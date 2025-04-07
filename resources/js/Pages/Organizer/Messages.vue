<template>
  <OrganizerLayout :title="$t('messages')" :breadcrumb="breadcrumb">
    <div class="flex-auto pb-3 text-right">
      <a-button type="primary" class="!rounded" @click="createRecord()">
        {{ $t("create_message") }}
      </a-button>
    </div>
    <div class="bg-white relative shadow rounded-lg overflow-x-auto">
      <a-table
        :dataSource="messages.data"
        :columns="columns"
        :pagination="pagination"
        @change="onPaginationChange"
        ref="dataTable"
      >
        <template #headerCell="{ column }">
          {{ column.i18n ? $t(column.i18n) : column.title }}
        </template>
        <template #bodyCell="{ column, text, record, index }">
          <template v-if="column.dataIndex == 'operation'">
            <div class="space-x-2">
              <a-button @click="editRecord(record)">{{ $t("edit") }}</a-button>
              <a-popconfirm
                :title="$t('confirm_delete_record')"
                :ok-text="$t('yes')"
                :cancel-text="$t('no')"
                @confirm="deleteRecord(record.id)"
              >
                <a-button>{{ $t("delete") }}</a-button>
              </a-popconfirm>
            </div>
          </template>
          <template v-else-if="column.dataIndex == 'category_code'">
            {{ messageCategories.find((x) => x.value == record.category_code)["label"] }}
          </template>
          <template v-else-if="column.dataIndex == 'receiver'">
            <ol>
              <li v-for="member in record.received_members" :key="member.id">
                {{ member.given_name }}
              </li>
            </ol>
          </template>
        </template>
      </a-table>
    </div>
    <!-- Modal Start-->
    <a-modal v-model:open="modal.isOpen" :title="modal.title" width="60%">
      <a-form
        ref="modalRef"
        :model="modal.data"
        name="Teacher"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 20 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-form-item :label="$t('classification')" name="category_code">
          <a-select
            v-model:value="modal.data.category_code"
            :options="messageCategories"
          />
        </a-form-item>
        <a-form-item
          label="Receiver"
          name="receiver"
          v-if="modal.data.category_code == 'IND'"
        >
          <a-select
            mode="multiple"
            v-model:value="modal.data.receiver"
            :options="members"
            :field-names="{ label: 'display_name', value: 'id' }"
          />
        </a-form-item>
        <a-form-item :label="$t('sender')" name="sender">
          <a-input v-model:value="modal.data.sender" />
        </a-form-item>
        <a-form-item :label="$t('title')" name="title">
          <a-input v-model:value="modal.data.title" />
        </a-form-item>
        <a-form-item :label="$t('content')" name="content">
          <!-- <quill-editor v-model:value="modal.data.content" style="min-height: 200px" /> -->
        </a-form-item>
      </a-form>
      <template #footer>
        <a-button
          v-if="modal.mode == 'EDIT'"
          key="Update"
          type="primary"
          @click="updateRecord()"
          >{{ $t("update") }}</a-button
        >
        <a-button
          v-if="modal.mode == 'CREATE'"
          key="Store"
          type="primary"
          @click="storeRecord()"
          >{{ $t("add") }}</a-button
        >
      </template>
    </a-modal>
    <!-- Modal End-->
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { UploadOutlined } from "@ant-design/icons-vue";
import Icon, { RestFilled } from "@ant-design/icons-vue";
// import { quillEditor } from "vue3-quill";
import { message } from "ant-design-vue";

export default {
  components: {
    OrganizerLayout,
    UploadOutlined,
    RestFilled,
    // quillEditor,
  },
  props: ["messageCategories", "messages", "members"],
  data() {
    return {
      breadcrumb: [{ label: "通告列表", url: null }],
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      pagination: {
        total: this.messages.total,
        current: this.messages.current_page,
        pageSize: this.messages.per_page,
      },
      filter: {},
      columns: [
        {
          title: "Category",
          i18n: "type",
          dataIndex: "category_code",
          width: 80,
        },
        {
          title: "Title",
          i18n: "title",
          dataIndex: "title",
        },
        {
          title: "Sender",
          i18n: "sender",
          dataIndex: "sender",
          width: 120,
        },
        {
          title: "Receiver",
          i18n: "receiver",
          dataIndex: "receiver",
          width: 120,
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
          width: 200,
        },
      ],
      rules: {
        category_code: { required: true },
        receiver: { required: true },
        sender: { required: true },
        title: { required: true },
        content: { required: true },
      },
      validateMessages: {
        required: "${label} is required!",
        types: {
          email: "${label} is not a valid email!",
          number: "${label} is not a valid number!",
        },
        number: {
          range: "${label} must be between ${min} and ${max}",
        },
      },
      labelCol: {
        style: {
          width: "150px",
        },
      },
    };
  },
  created() {},
  methods: {
    createRecord(record) {
      this.modal.data = {};
      this.modal.mode = "CREATE";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.mode = "EDIT";
      this.modal.isOpen = true;
    },
    storeRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post(route("organizer.messages.store"), this.modal.data, {
            preserveState: false,
            onSuccess: (page) => {
              this.modal.isOpen = false;
              message.success("Create Successful.");
            },
            onError: (err) => {
              console.log(err);
            },
          });
        })
        .catch((err) => {
          console.log(err);
        });
    },
    updateRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.modal.data._method = "PATCH";
          this.$inertia.post(
            route("organizer.messages.update", this.modal.data.id),
            this.modal.data,
            {
              preserveState: false,
              onSuccess: (page) => {
                this.modal.isOpen = false;
                message.success("Update Successful.");
              },
              onError: (error) => {
                console.log(error);
              },
            }
          );
        })
        .catch((err) => {
          console.log("error", err);
        });
    },

    deleteRecord(recordId) {
      this.$inertia.delete(route("organizer.messages.destroy", recordId), {
        preserveState: false,
        onSuccess: (page) => {
          message.success("Delete Successful.");
          console.log(page);
        },
        onError: (error) => {
          alert(error.message);
        },
      });
    },
    onPaginationChange(page, filters, sorter) {
      this.$inertia.get(
        route("organizer.messages.index"),
        {
          page: page.current,
          per_page: page.pageSize,
        },
        {
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (error) => {
            console.log(error);
          },
        }
      );
    },
  },
};
</script>
