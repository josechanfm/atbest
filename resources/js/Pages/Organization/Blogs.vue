<template>
  <OrganizationLayout :title="$t('blogs')">
    <div class="flex-auto pb-3 text-right">
      <a-button type="primary" class="!rounded" @click="createRecord()">
        {{ $t("create_blog") }}
      </a-button>
    </div>
    <div class="bg-white relative shadow rounded-lg overflow-x-auto">
      <a-table
        :dataSource="blogs"
        :columns="columns"
        :pagination="pagination"
        @change="onPaginationChange"
        ref="dataTable"
      >
        <template #headerCell="{ column }">
          {{ column.i18n ? $t(column.i18n) : column.title }}
        </template>
        <template #bodyCell="{ column, text, record, index }">
          <template v-if="column.key == 'user'">{{ record.user.name }}</template>
          <template v-if="column.dataIndex == 'operation'">
            <div class="space-x-2">
              <a-button @click="editRecord(record)">{{ $t("edit") }}</a-button>
              <a :href="route('manage.blogs.show', record.id)">
                <a-button>
                  {{ $t("contents_management") }}
                </a-button>
              </a>
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
          <template v-else-if="column.dataIndex == 'category'">
            {{ bulletinCategories.find((x) => x.value == text)["label"] }}
          </template>
        </template>
      </a-table>
    </div>

    <!-- Modal Start-->
    <a-modal
      v-model:open="modal.isOpen"
      :title="modal.mode == 'CREATE' ? 'Create' : 'Edit'"
      width="60%"
    >
      <a-form
        ref="modalRef"
        :model="modal.data"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 20 }"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-form-item :label="$t('title')" name="title">
          <a-input v-model:value="modal.data.title" />
        </a-form-item>
        <a-form-item :label="$t('description')" name="description">
          <a-input v-model:value="modal.data.description" />
        </a-form-item>
        <!-- <a-form-item :label="$t('image')" name="images">
          <a-upload
            v-model:file-list="modal.data.images"
            list-type="picture-card"
            :beforeUpload="() => false"
          >
            <div>
              <upload-outlined></upload-outlined>
              <div style="margin-top: 8px">Upload</div>
            </div>
          </a-upload>
        </a-form-item> -->
      </a-form>
      <template #footer>
        <a-button
          v-if="modal.mode == 'EDIT'"
          :loading="loading"
          key="Update"
          type="primary"
          @click="updateRecord()"
          >修改</a-button
        >
        <a-button
          v-if="modal.mode == 'CREATE'"
          :loading="loading"
          key="Store"
          type="primary"
          @click="storeRecord()"
          >新增</a-button
        >
      </template>
    </a-modal>

    <!-- Modal End-->
  </OrganizationLayout>
</template>

<script>
import OrganizationLayout from "@/Layouts/OrganizationLayout.vue";
import { UploadOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import Icon, { RestFilled } from "@ant-design/icons-vue";
import { quillEditor } from "vue3-quill";

export default {
  components: {
    OrganizationLayout,
    UploadOutlined,
    RestFilled,
    quillEditor,
  },
  props: ["bulletinCategories", "blogs"],
  data() {
    return {
      dateFormat: "YYYY-MM-DD",
      yearCurrent: 2023,
      yearStart: new Date().getFullYear() + 1,
      yearLength: new Date().getFullYear() - 2009 + 2,
      loading: false,
      modal: {
        isOpen: false,
        data: {
          published: 1,
        },
        title: "公告",
        mode: "",
      },
      pagination: {
        total: this.blogs.total,
        current: this.blogs.current_page,
        pageSize: this.blogs.per_page,
      },
      filter: {},
      columns: [
        {
          title: "User",
          i18n: "blog.user",
          dataIndex: "user_id",
          key: "user",
        },
        {
          title: "Title",
          i18n: "blog.title",
          dataIndex: "title",
        },
        {
          title: "Description",
          i18n: "blog.description",
          dataIndex: "description",
        },
        {
          title: "Create at",
          i18n: "create_at",
          dataIndex: "create_at",
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        title: { required: true },
        description: { required: true },
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
  compunted: {
    pagination() {
      return {
        total: this.blogs.total,
        current: this.blogs.current_page,
        pageSize: this.blogs.per_page,
      };
    },
  },
  methods: {
    createRecord(record) {
      this.modal.data = {
        published: 1,
      };
      this.modal.mode = "CREATE";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.mode = "EDIT";
      console.log(record);
      this.modal.isOpen = true;
    },
    storeRecord() {
      this.loading = true;
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post(route("manage.blogs.store"), this.modal.data, {
            preserveState: false,
            onSuccess: (page) => {
              this.modal.isOpen = false;
              console.log(page);
              message.success("新增成功");
            },
            onError: (err) => {
              console.log(err);
            },
            onFinish: (visit) => {
              this.loading = false;
            },
          });
        })
        .catch((err) => {
          this.loading = false;
          console.log(err);
        });
    },
    updateRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.modal.data._method = "PATCH";
          this.$inertia.post(
            route("manage.blogs.update", this.modal.data.id),
            this.modal.data,
            {
              preserveState: false,
              onSuccess: (page) => {
                this.modal.isOpen = false;
                message.success("修改成功");
              },
              onError: (error) => {
                console.log(error);
                message.error(error.error);
              },
            }
          );
        })
        .catch((err) => {
          console.log("error", err);
        });
    },
    deleteRecord(recordId) {
      this.$inertia.delete(route("manage.blogs.destroy", recordId), {
        preserveState: false,
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          alert(error.message);
        },
      });
    },
    onPaginationChange(page, filters, sorter) {
      this.$inertia.get(route("admin.blogs.index"), {
        page: page.current,
        per_page: page.pageSize,
      });
    },
  },
};
</script>
