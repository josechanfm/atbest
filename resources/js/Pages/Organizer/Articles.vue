<template>
  <OrganizerLayout :title="$t('articles')" :breadcrumb="breadcrumb">
    <div class="flex-auto pb-3 text-right">
      <a-button
          :href="route('organizer.articles.create')"
          as="link"
          type="primary"
      >
        {{ $t("create_article") }}
      </a-button>
    </div>
    <div class="container mx-auto">
      <div class="flex flex-auto gap-2">
        <a-select
          class="w-32"
          :placeholder="$t('please_select_category')"
          v-model:value="search.category"
          allowClear
          :options="articleCategories"
        ></a-select>
        <a-input type="input"
          v-model:value="search.title"
          :placeholder="$t('please_input_title')"
          class="w-64"
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
              <a-button :href="route('organizer.articles.edit', record.id)" as="link">
                {{ $t("edit") }}
              </a-button>
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

  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { defineComponent, reactive } from "vue";
import UploadAdapter from "@/Components/ImageUploadAdapter.vue";
import { VueDraggableNext } from "vue-draggable-next";
import { HolderOutlined } from "@ant-design/icons-vue";

export default {
  components: {
    OrganizerLayout,
    Pagination,
    UploadAdapter,
    draggable: VueDraggableNext,
    HolderOutlined,
    //UploadAdapter
  },
  props: ["classifies", "articleCategories", "articles"],
  data() {
    return {
      breadcrumb: [{ label: "文章列表", url: null }],
      dateFormat: "YYYY-MM-DD",
      originSequences: [],
      search: {},
      pagination: {
        total: this.articles.total,
        current: this.articles.current_page,
        pageSize: this.articles.per_page,
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
      this.$inertia.get(route("organizer.articles.index"), {
        page: page,
        per_page: this.pagination.pageSize,
      });
    },
    onShowSizeChange(current, pageSize) {
      this.pagination.pageSize = pageSize;
    },
    createRecord() {
      this.modal.data = {};
      this.modal.data.for_member = 0;
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
          this.$inertia.post(route("organizer.articles.store"), this.modal.data, {
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
            route("organizer.articles.update", this.modal.data.id),
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
      this.$inertia.delete(route("organizer.articles.destroy", record.id), {
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
    // this.$inertia.delete(route('organizer.articles.destroy', recordId), {
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
        route("organizer.articles.index"),
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
