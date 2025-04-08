<template>
  <OrganizerLayout :title="$t('email.management')" :breadcrumb="breadcrumb">
    <div class="flex-auto pb-3 text-right">
      <a-button type="primary" class="!rounded" @click="createRecord()">
        {{ $t("email.create") }}
      </a-button>
    </div>
    <div class="bg-white relative shadow rounded-lg overflow-x-auto">
      <a-table 
        ref="dataTable"
        :dataSource="emails.data" 
        :columns="columns"
        :pagination="pagination"
        @change="onPaginationChange"
      >
        <template #headerCell="{ column }">
          {{ column.i18n ? $t(column.i18n) : column.title }}
        </template>
        <template #bodyCell="{ column, text, record, index }">
          <template v-if="column.dataIndex == 'operation'">
            <a-button @click="showRecord(record)">{{ $t("edit") }}</a-button>
          </template>
          <template v-else>
            {{ record[column.dataIndex] }}
          </template>
        </template>
      </a-table>
    </div>

    <!-- Modal Start-->
    <a-modal v-model:open="modal.isOpen" :title="$t(modal.title)" width="60%">
      <a-form
        ref="modalRef"
        :model="modal.data"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 20 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-form-item :label="$t('email.sender')">
          <a-input v-model:value="modal.data.sender" />
        </a-form-item>
        <a-form-item :label="$t('email.recipient')">
          <a-input v-model:value="modal.data.recipient" />
        </a-form-item>
        <a-form-item :label="$t('email.cc')">
          <a-input v-model:value="modal.data.cc" />
        </a-form-item>
        <a-form-item :label="$t('email.bcc')">
          <a-input v-model:value="modal.data.bcc" />
        </a-form-item>
        <a-form-item :label="$t('email.subject')">
          <a-input v-model:value="modal.data.subject" />
        </a-form-item>
        <a-form-item :label="$t('email.message')">
          <a-textarea v-model:value="modal.data.message" :rows="5"/>
        </a-form-item>
      </a-form>
      <template #footer>
        <a-button key="back" @click="modal.isOpen = false">返回</a-button>
      </template>
    </a-modal>
    <!-- Modal End-->
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { defineComponent, reactive } from "vue";

export default {
  components: {
    OrganizerLayout,
  },
  props: ["emails"],
  data() {
    return {
      breadcrumb: [{ label: "電郵列表", url: null }],
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      pagination: {
        total: this.emails.total,
        current: this.emails.current_page,
        pageSize: this.emails.per_page,
      },
      columns: [
        {
          title: "Sender",
          i18n: "email.sender",
          dataIndex: "sender",
        },
        {
          title: "Recipient",
          i18n: "email.recipient",
          dataIndex: "recipient",
        },
        {
          title: "Subject",
          i18n: "email.subject",
          dataIndex: "subject",
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        sender: { required: true, type:'email' },
        recipient: { required: true, type:'email' },
        cc: { required: true, type:'email' },
        bcc: { required: true, type:'email' },
        subject: { required: true },
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
  mounted() {},
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
    showRecord(record) {
      this.modal.data = { ...record };
      this.modal.mode = "VIEW";
      this.modal.title = "view";
      this.modal.isOpen = true;
    },
    storeRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post(route("organizer.emails.store"), this.modal.data, {
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
            route("organizer.emails.update", this.modal.data.id),
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
      this.$inertia.delete(route("organizer.emails.destroy", recordId), {
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
        route("organizer.emails.index"),
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
