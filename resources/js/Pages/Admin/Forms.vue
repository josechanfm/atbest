<template>
  <AdminLayout title="表格" :breadcrumb="breadcrumb">
    <div class="container mx-auto p-5">
      <div class="flex-auto pb-3 text-right">
        <inertia-link :href="route('admin.forms.create')"
          class="ant-btn ant-btn-primary !rounded">{{ $t("create_form") }}</inertia-link>
      </div>
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="forms" :columns="columns">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <inertia-link :href="route('admin.form.entries.index', { form: record.id })" class="ant-btn">
                <a-button>{{ $t("applications") }}</a-button>
              </inertia-link>
              <a-button :href="route('admin.entry.export', { form: record.id })" class="ant-btn">{{ $t("export") }}</a-button>
              <inertia-link :href="route('admin.form.fields.index', { form: record.id })" class="ant-btn">
                <a-button>{{ $t("data_fields") }}</a-button>
              </inertia-link>
              <inertia-link :href="route('admin.forms.edit', record.id)" class="ant-btn">
                <a-button>{{ $t("edit") }}</a-button>
              </inertia-link>

              <a-popconfirm :title="$t('confirm_delete_record')" :ok-text="$t('yes')" :cancel-text="$t('no')"
                @confirm="deleteConfirmed(record)" :disabled="record.entries_count > 0">
                <a-button :disabled="record.entries_count > 0">{{ $t("delete") }}</a-button>
              </a-popconfirm>
              <a-button @click="backupRecords(record)" v-if="record.entries_count > 0">{{ $t("backup") }}</a-button>
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
      </div>
      <p>From CAN NOT be delete, if Response is not empty.</p>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
  UploadOutlined,
  LoadingOutlined,
  PlusOutlined,
  InfoCircleFilled,
} from "@ant-design/icons-vue";
import Icon, { RestFilled } from "@ant-design/icons-vue";
import { quillEditor, Quill } from "vue3-quill";
import { message } from "ant-design-vue";

export default {
  components: {
    AdminLayout,
    UploadOutlined,
    LoadingOutlined,
    PlusOutlined,
    RestFilled,
    quillEditor,
    message,
  },
  props: ["forms"],
  data() {
    return {
        breadcrumb: [
          { label: "表格列表", url: null }
        ],
        loading: false,
        imageUrl: null,
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
        ]
      }
    },
      created() { },
    methods: {
      deleteConfirmed(record) {
        console.log("delete");
        console.log(record);
        this.$inertia.delete(route("admin.forms.destroy", { form: record.id }), {
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
        this.$inertia.post(route("admin.form.backup", record.id), {
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (error) => {
            alert(error.message);
          },
        });
      },
    },
  };
</script>
