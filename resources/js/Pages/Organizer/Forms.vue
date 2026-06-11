<template>
  <OrganizerLayout title="表格" :breadcrumb="breadcrumb">
    <!-- 顶部操作栏 -->
    <div class="flex-auto pb-3 text-right">
      <a-button
        :href="route('organizer.forms.create')"
        as="link"
        type="primary"
      >
        {{ $t("create_form") }}
      </a-button>
    </div>

    <!-- 搜索栏 -->
    <div class="mx-auto">
      <div class="flex flex-auto gap-2">
        <a-input
          type="input"
          v-model:value="search.name"
          :placeholder="$t('please_input_name')"
          class="w-64"
        />
        <a-input
          type="input"
          v-model:value="search.title"
          :placeholder="$t('please_input_title')"
          class="w-64"
        />
        <a-button type="primary" @click="searchData">
          {{ $t("search") }}
        </a-button>
      </div>
    </div>

    <!-- 表格主体 -->
    <div class="mx-auto py-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table
          :dataSource="forms.data"
          :columns="columns"
          :pagination="false"
        >
          <!-- 表头国际化 -->
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>

          <!-- 单元格自定义渲染 -->
          <template #bodyCell="{ column, record }">
            <!-- 操作列 -->
            <template v-if="column.dataIndex === 'operation'">
              <div class="flex flex-wrap gap-2">
                <a-button :href="route('organizer.form.entries.index', { form: record.id })" as="link">
                  {{ $t("applications") }}
                </a-button>
                <a-button :href="route('organizer.entry.export', { form: record.id })" as="link">
                  {{ $t("export") }}
                </a-button>
                <a-button :href="route('organizer.form.fields.index', { form: record.id })" as="link">
                  {{ $t("data_fields") }}
                </a-button>
                <a-button :href="route('organizer.forms.edit', record.id)" as="link">
                  {{ $t("edit") }}
                </a-button>

                <!-- 删除按钮（带确认） -->
                <a-popconfirm
                  :title="$t('confirm_delete_record')"
                  :ok-text="$t('yes')"
                  :cancel-text="$t('no')"
                  @confirm="deleteConfirmed(record)"
                  :disabled="hasEntries(record)"
                >
                  <a-button :disabled="hasEntries(record)">
                    {{ $t("delete") }}
                  </a-button>
                </a-popconfirm>

                <!-- 备份按钮（仅当有数据时显示） -->
                <a-button v-if="hasEntries(record)" @click="backupRecords(record)">
                  {{ $t("backup") }}
                </a-button>
              </div>
            </template>

            <!-- 是/否 类型列 -->
            <template v-else-if="column.type === 'yesno'">
              <span>{{ record[column.dataIndex] === 1 ? $t("yes") : $t("no") }}</span>
            </template>

            <!-- 条目数列 -->
            <template v-else-if="column.dataIndex === 'entries'">
              {{ record.entries_count }}
            </template>

            <!-- 普通列 -->
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>

        <!-- 分页组件 -->
        <Pagination :data="forms" :search="search" />
      </div>
    </div>

    <!-- 提示信息 -->
    <div class="text-gray-500 text-sm mt-2">
      {{ $t('from_can_not_be_delete_if_response_is_not_empty') }}
    </div>
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import {
  UploadOutlined,
  LoadingOutlined,
  PlusOutlined,
} from "@ant-design/icons-vue";
import { RestFilled } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";

export default {
  name: "FormIndex",
  components: {
    OrganizerLayout,
    Pagination,
    UploadOutlined,
    LoadingOutlined,
    PlusOutlined,
    RestFilled,
    message,
  },
  props: {
    forms: {
      type: Object,
      required: true,
    },
  },
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
      columns: [
        { title: "Name", i18n: "name", dataIndex: "name" },
        { title: "Title", i18n: "title", dataIndex: "title" },
        { title: "Require_login", i18n: "require_login", dataIndex: "require_login", type: "yesno" },
        { title: "Published", i18n: "published", dataIndex: "published", type: "yesno" },
        { title: "Entries", i18n: "entries", dataIndex: "entries", key: "entries" },
        { title: "Operation", i18n: "operation", dataIndex: "operation", key: "operation" },
      ],
    };
  },
  mounted() {
    // 初始化分页和搜索参数（从路由获取）
    this.pagination = {
      currentPage: this.route().params.currentPage ?? 1,
      pageSize: this.route().params.pageSize ?? 10,
    };
    this.search = this.route().params.search ?? {};
  },
  methods: {
    /**
     * 判断表单是否有提交记录
     */
    hasEntries(record) {
      return record.entries_count > 0;
    },

    /**
     * 删除确认后的回调
     */
    deleteConfirmed(record) {
      this.$inertia.delete(route("organizer.forms.destroy", { form: record.id }), {
        onSuccess: (page) => {
          // 可在此添加成功提示
          console.log("删除成功", page);
        },
        onError: (error) => {
          message.error(error.message || "删除失败");
        },
      });
    },

    /**
     * 备份表单数据
     */
    backupRecords(record) {
      if (!confirm(this.$t("confirm_backup") || "确定要备份吗？")) return;
      this.$inertia.post(route("organizer.form.backup", record.id), {
        onSuccess: (page) => {
          console.log("备份成功", page);
          message.success(this.$t("backup_success"));
        },
        onError: (error) => {
          message.error(error.message || "备份失败");
        },
      });
    },

    /**
     * 搜索数据
     */
    searchData() {
      this.$inertia.get(
        route("organizer.forms.index"),
        { search: this.search, pagination: this.pagination },
        {
          preserveState: true,
          onSuccess: (page) => {
            console.log("搜索完成", page);
          },
        }
      );
    },
  },
};
</script>