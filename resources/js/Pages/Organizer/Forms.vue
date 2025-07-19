<template>
  <OrganizerLayout title="表格" :breadcrumb="breadcrumb">
    <div class="flex-auto pb-3 text-right">
      <a-button
          :href="route('organizer.forms.create')"
          as="link"
          type="primary"
      >
        {{ $t("create_form") }}
      </a-button>
    </div>
    <div class="container mx-auto">
      <div class="flex flex-auto gap-2">
        <a-input type="input"
          v-model:value="search.name"
          :placeholder="$t('please_input_name')"
          class="w-64"
        ></a-input>
        <a-input type="input"
          v-model:value="search.title"
          :placeholder="$t('please_input_title')"
          class="w-64"
        ></a-input>
        <a-button type="primary" @click="searchData">{{ $t("search") }}</a-button>
      </div>
    </div>
    <div class="container mx-auto py-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="forms.data" :columns="columns" :pagination="false">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <a-button :href="route('organizer.form.entries.index', { form: record.id })" as="link" >
                {{ $t("applications") }}
              </a-button>
              <a-button :href="route('organizer.entry.export', { form: record.id })" as="link">
                {{ $t("export") }}
              </a-button>
              <a-button :href="route('organizer.form.fields.index', { form: record.id }) "as="link">
                {{ $t("data_fields") }}
              </a-button>
              <a-button :href="route('organizer.forms.edit', record.id)" as="link">
                {{ $t("edit") }}
              </a-button>
              <a-popconfirm
                :title="$t('confirm_delete_record')"
                :ok-text="$t('yes')"
                :cancel-text="$t('no')"
                @confirm="deleteConfirmed(record)"
                :disabled="record.entries_count > 0"
              >
                <a-button :disabled="record.entries_count > 0">{{
                  $t("delete")
                }}</a-button>
              </a-popconfirm>
              <a-button @click="backupRecords(record)" v-if="record.entries_count > 0">{{
                $t("backup")
              }}</a-button>
            </template>
            <template v-else-if="column.type == 'yesno'">
              <span v-if="record[column.dataIndex] == 1">{{ $t("yes") }}</span>
              <span v-else>{{ $t("no") }}</span>
            </template>
            <template v-else-if="column.dataIndex == 'entries'">
              {{ record.entries_count }}
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>
        <Pagination :data="forms" :search="search" />
      </div>
    </div>
    <p>From CAN NOT be delete, if Response is not empty.</p>
    {{$t('From CAN NOT be delete, if Response is not empty.')}}
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import {
  UploadOutlined,
  LoadingOutlined,
  PlusOutlined,
  InfoCircleFilled,
} from "@ant-design/icons-vue";
import Icon, { RestFilled } from "@ant-design/icons-vue";
// import { quillEditor, Quill } from "vue3-quill";
import { message } from "ant-design-vue";

export default {
  components: {
    OrganizerLayout,
    UploadOutlined,
    Pagination,
    LoadingOutlined,
    PlusOutlined,
    RestFilled,
    // quillEditor,
    message,
  },
  props: ["forms"],
  data() {
    return {
      breadcrumb: [{ label: "表格列表", url: null }],
      loading: false,
      imageUrl: null,
      search: {},
      pagination: {
        total: this.forms.total,
        current: this.forms.current_page,
        pageSize: this.forms.per_page,
      },
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      columns: [
        {
          title: "Name",
          i18n: "name",
          dataIndex: "name",
        },
        {
          title: "Title",
          i18n: "title",
          dataIndex: "title",
        },
        {
          title: "Require_login",
          i18n: "require_login",
          dataIndex: "require_login",
          type: "yesno",
        },
        {
          title: "Published",
          i18n: "published",
          dataIndex: "published",
          type: "yesno",
        },
        {
          title: "Entries",
          i18n: "entries",
          dataIndex: "entries",
          key: "entries",
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
    };
  },
  created() {},
  mounted() {
    this.pagination = {
      currentPage: this.route().params.currentPage ?? 1,
      pageSize: this.route().params.pageSize ?? 10,
    };
    this.search = this.route().params.search ?? {};
  },
  methods: {
    deleteConfirmed(record) {
      console.log("delete");
      console.log(record);
      this.$inertia.delete(route("organizer.forms.destroy", { form: record.id }), {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          alert(error.message);
        },
      });
    },
    backupRecords(record) {
      if (!confirm("Do you sure want to backup?")) return;
      this.$inertia.post(route("organizer.form.backup", record.id), {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          alert(error.message);
        },
      });
    },
    searchData() {
      this.$inertia.get(
        route("organizer.forms.index"),
        { search: this.search, pagination: this.pagination },
        {
          onSuccess: (page) => {
            console.log(page);
          },
          preserveState: true,
        }
      );
    },
  },
};
</script>
