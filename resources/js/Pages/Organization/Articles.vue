<template>
  <OrganizationLayout :title="$t('articles')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("articles") }}
      </h2>
    </template>
    <div class="container mx-auto">
      <div class="flex-auto pb-3">
        <inertia-link
          :href="route('manage.articles.create')"
          class="ant-btn ant-btn-primary"
          >{{ $t("create_article") }}</inertia-link
        >
      </div>
    </div>
    <div class="container mx-auto">
      <div class="flex flex-col md:flex-row justify-between gap-6">
        <a-select
          class="w-full"
          :placeholder="$t('please_select_category')"
          v-model:value="search.category"
          allowClear
          :options="articleCategories"
        ></a-select>
        <a-input
          v-model:value="search.title"
          :placeholder="$t('please_input_title')"
        ></a-input>
        <a-button type="primary" @click="searchData">{{ $t("search") }}</a-button>
      </div>
    </div>
    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table
          :dataSource="articles.data"
          :columns="columns"
          :pagination="false"
          @change="onPaginationChange"
        >
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <inertia-link
                :href="route('manage.articles.edit', record.id)"
                class="ant-btn"
                >{{ $t("edit") }}</inertia-link
              >
              <a-popconfirm
                :title="$t('confirm_delete_record')"
                :ok-text="$t('yes')"
                :cancel-text="$t('no')"
                @confirm="deleteConfirmed(record)"
                :disabled="record.published == 1"
              >
                <a-button :disabled="record.published == 1">{{ $t("delete") }}</a-button>
              </a-popconfirm>
            </template>
            <template v-else-if="column.dataIndex == 'published'">
              {{ record.published ? $t("yes") : $t("no") }}
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>
        <Pagination :data="articles" :search="search" />
      </div>
    </div>
    <p>Article CAN NOT be delete if published.</p>

    <!-- Modal Start-->
    <a-modal v-model:visible="modal.isOpen" :title="modal.title" width="100%">
      <a-form
        ref="modalRef"
        :model="modal.data"
        name="Teacher"
        layout="vertical"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-form-item :label="$t('article_category')" name="category_code">
          <a-select
            v-model:value="modal.data.category_code"
            :options="articleCategories"
          />
        </a-form-item>
        <a-form-item :label="$t('title')" name="title">
          <a-input v-model:value="modal.data.title" />
        </a-form-item>
        <a-form-item :label="$t('title')" name="title_fn">
          <a-input v-model:value="modal.data.title" />
        </a-form-item>
        <a-form-item :label="$t('content')" name="content_en">
          <ckeditor
            :editor="editor"
            v-model="modal.data.content_en"
            :config="editorConfig"
          />
        </a-form-item>
        <a-form-item :label="$t('valid_at')" name="valid_at">
          <a-date-picker
            v-model:value="modal.data.valid_at"
            :format="dateFormat"
            :valueFormat="dateFormat"
          />
        </a-form-item>
        <a-form-item :label="$t('expired_at')" name="expired_at">
          <a-date-picker v-model:value="modal.data.expire_at" :valueFormat="dateFormat" />
        </a-form-item>
        <a-form-item :label="$t('url')" name="url">
          <a-input v-model:value="modal.data.url" />
        </a-form-item>
        <a-row>
          <a-col>
            <a-form-item :label="$t('published')" name="published">
              <a-switch
                v-model:checked="modal.data.published"
                :checkedValue="1"
                :unCheckedValue="0"
                @change="modal.data.public = 0"
              />
            </a-form-item>
          </a-col>
          <a-col class="pl-10" v-if="modal.data.published">
            <a-form-item :label="$t('public')" name="public">
              <a-switch
                v-model:checked="modal.data.public"
                :checkedValue="1"
                :unCheckedValue="0"
              />
            </a-form-item>
          </a-col>
        </a-row>
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
  </OrganizationLayout>
</template>

<script>
import OrganizationLayout from "@/Layouts/OrganizationLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { defineComponent, reactive } from "vue";
//import Editor from 'ckeditor5-custom-build/build/ckeditor';
import CKEditor from "@ckeditor/ckeditor5-vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import UploadAdapter from "@/Components/ImageUploadAdapter.vue";
import { VueDraggableNext } from "vue-draggable-next";
import { HolderOutlined } from "@ant-design/icons-vue";

export default {
  components: {
    OrganizationLayout,
    Pagination,
    ckeditor: CKEditor.component,
    UploadAdapter,
    draggable: VueDraggableNext,
    HolderOutlined,
    //UploadAdapter
  },
  props: ["classifies", "articleCategories", "articles"],
  data() {
    return {
      dateFormat: "YYYY-MM-DD",
      modal: {
        isOpen: false,
        data: { content_en: "" },
        title: "Modal",
        mode: "",
      },
      originSequences: [],
      search: {},
      pagination: {
        total: this.articles.total,
        current: this.articles.current_page,
        pageSize: this.articles.per_page,
      },
      editor: ClassicEditor,
      editorData: "<p>Content of the editor.</p>",
      editorConfig: {
        extraPlugins: [
          function (editor) {
            editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
              return new UploadAdapter(loader);
            };
          },
        ],
        // The configuration of the editor.
      },
      columns: [
        {
          title: "Category",
          i18n: "category",
          dataIndex: "category_code",
        },
        {
          title: "Title",
          i18n: "title",
          dataIndex: "title",
        },
        {
          title: "Validated at",
          i18n: "valid_at",
          dataIndex: "valid_at",
        },
        {
          title: "Expired at",
          i18n: "expired_at",
          dataIndex: "expired_at",
        },
        {
          title: "Published",
          i18n: "published",
          dataIndex: "published",
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        category_code: { required: true },
        classify_id: { required: true },
        title_en: { required: true },
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
  created() {

  },
  mounted() {
    this.pagination = {
      currentPage: this.route().params.currentPage ?? 1,
      pageSize: this.route().params.pageSize ?? 10,
    };
    this.search = this.route().params.search ?? {};
  },
  methods: {
    onPaginationChange(page, filters, sorter) {
      console.log(page);
      this.$inertia.get(route("manage.articles.index"), {
        page: page,
        per_page: this.pagination.pageSize,
      });
    },
    onShowSizeChange(current, pageSize) {
      this.pagination.pageSize = pageSize;
    },
    createRecord() {
      this.modal.data = {};
      this.modal.data.public = 0;
      this.modal.mode = "CREATE";
      this.modal.title = "Create";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.mode = "EDIT";
      //this.modal.title = "Edit";
      this.modal.isOpen = true;
    },
    storeRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post(route("manage.articles.store"), this.modal.data, {
            onSuccess: (page) => {
              this.modal.data = {};
              this.modal.isOpen = false;
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
      console.log(this.modal.data);
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.put(
            route("manage.articles.update", this.modal.data.id),
            this.modal.data,
            {
              onSuccess: (page) => {
                this.modal.data = {};
                this.modal.isOpen = false;
                console.log(page);
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
    deleteConfirmed(record) {
      this.$inertia.delete(route("manage.articles.destroy", record.id), {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          console.log(error);
        },
      });
    },
    //deleteRecord(recordId) {
    //if (!confirm('Are you sure want to remove?')) return;
    // this.$inertia.delete(route('manage.articles.destroy', recordId), {
    //     onSuccess: (page) => {
    //         console.log(page);
    //     },
    //     onError: (error) => {
    //         console.log(error);
    //     }
    // });
    //},
    createLogin(recordId) {
      console.log("create login" + recordId);
    },
    uploader(editor) {
      editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
        return new UploadAdapter(loader);
      };
    },
    searchData() {
      this.$inertia.get(
        route("manage.articles.index"),
        { search: this.search, pagination: this.pagination },
        {
          onSuccess: (page) => {
            console.log(page);
          },
        }
      );
    },
  },
};
</script>
