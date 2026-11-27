<template>
  <OrganizerLayout title="表格" :breadcrumb="breadcrumb">
    <!-- 顶部操作栏 -->
    <div class="flex-auto pb-3 text-right">
      <a-button :href="route('organizer.forms.create')" as="link" type="primary">
        {{ $t("create_form") }}
      </a-button>
    </div>

    <!-- 搜索栏 -->
    <div class="mx-auto">
      <div class="flex flex-auto gap-2 flex-wrap">
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

    <!-- 表格主体 – horizontal scroll always available -->
    <div class="mx-auto py-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table
          :dataSource="forms.data"
          :columns="columns"
          :pagination="false"
          :row-class-name="tableRowClassName"
          :scroll="{ x: 'max-content' }"
        >
          <!-- 表头国际化 -->
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>

          <!-- 单元格自定义渲染 -->
          <template #bodyCell="{ column, record }">
            <!-- 操作列 -->
            <template v-if="column.dataIndex === 'operation'">
              <!-- 桌面端：显示所有按钮 -->
              <div class="desktop-actions hidden md:flex flex-wrap gap-2">
                <template v-for="action in getFormActions(record)" :key="action.key">
                  <!-- 删除 -->
                  <a-popconfirm
                    v-if="action.confirm"
                    :title="$t('confirm_delete_record')"
                    :ok-text="$t('yes')"
                    :cancel-text="$t('no')"
                    @confirm="action.click"
                    :disabled="action.disabled"
                  >
                    <a-button
                      danger
                      :disabled="action.disabled"
                      size="small"
                      class="action-btn"
                      :style="actionBtnStyle(action)"
                    >
                      {{ action.label }}
                    </a-button>
                  </a-popconfirm>
                  <a-button
                    v-else
                    :href="action.href"
                    :target="action.target"
                    size="small"
                    class="action-btn"
                    :class="{ 'ghost-btn': action.ghost }"
                    :style="actionBtnStyle(action)"
                    @click="action.click"
                  >
                    {{ action.label }}
                  </a-button>
                </template>
              </div>

              <!-- 移动端：下拉菜单（节省空间，并允许水平滚动其他列） -->
              <div class="mobile-actions md:hidden">
                <a-dropdown>
                  <a-button size="small">
                    {{ $t('actions') }} <DownOutlined />
                  </a-button>
                  <template #overlay>
                    <a-menu>
                      <a-menu-item
                        v-for="action in getFormActions(record)"
                        :key="action.key"
                        :disabled="action.disabled"
                        @click="action.click"
                      >
                        {{ action.label }}
                      </a-menu-item>
                    </a-menu>
                  </template>
                </a-dropdown>
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

    <!-- QR Code Modal -->
    <qr-code-modal
      :fullUrl="qrCodeModal.url"
      :title="qrCodeModal.title"
      :isModalOpen="qrCodeModal.isOpen"
      @update:isModalOpen="updateQrCodeModalOpen"
    />
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import {
  UploadOutlined,
  LoadingOutlined,
  PlusOutlined,
  DownOutlined,
} from "@ant-design/icons-vue";
import { RestFilled } from "@ant-design/icons-vue";
import { message, Modal } from "ant-design-vue";
import QrCodeModal from '@/Components/QrCodeModal.vue';

export default {
  name: "FormIndex",
  components: {
    OrganizerLayout,
    Pagination,
    UploadOutlined,
    LoadingOutlined,
    PlusOutlined,
    RestFilled,
    DownOutlined,
    QrCodeModal,
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
      qrCodeModal: {
        isOpen: false,
        title: 'Qr Code',
        url: 'https://venus.mpu.edu.mo'
      },
    };
  },
  mounted() {
    this.pagination = {
      currentPage: this.route().params.currentPage ?? 1,
      pageSize: this.route().params.pageSize ?? 10,
    };
    this.search = this.route().params.search ?? {};
  },
  methods: {
    tableRowClassName(record, index) {
      return index % 2 === 0 ? 'bg-gray-50' : 'bg-white';
    },

    hasEntries(record) {
      return record.entries_count > 0;
    },

    // 表單操作清單，桌面按鈕與行動選單共用
    getFormActions(record) {
      // 按功能分組配色：表單管理(藍)、資料記錄(綠)、分享預覽(紫)、刪除(紅)
      const C = { manage: "#1677ff", data: "#5294aa", share: "#722ed1", danger: "#ff4d4f" };
      const actions = [
        { key: "edit", label: this.$t("edit"), color: C.manage, href: route("organizer.forms.edit", record.id) },
        { key: "data_fields", label: this.$t("data_fields"), color: C.data, href: route("organizer.form.fields.index", { form: record.id }) },
        { key: "applications", label: this.$t("applications"), color: C.data, href: route("organizer.form.entries.index", { form: record.id }) },
        { key: "export", label: this.$t("export"), color: C.data, href: route("organizer.entry.export", { form: record.id }) },
        { key: "qrcode", label: this.$t("qrcode_show"), color: C.share, ghost: true, click: () => this.openQrCodeModal(record) },
        { key: "preview", label: this.$t("preview"), color: C.share, ghost: true, target: "_blank", href: route("form.item", { t: record.uuid, preview: true }) },
        { key: "clone", label: this.$t("clone"), color: C.manage, click: () => this.cloneForm(record) },
      ];
      if (this.hasEntries(record)) {
        actions.push({ key: "backup", label: this.$t("backup"), color: C.data, click: () => this.backupRecords(record) });
      }
      actions.push({
        key: "delete",
        label: this.$t("delete"),
        color: "#ff4d4f",
        confirm: true,
        disabled: this.hasEntries(record),
        click: () => this.deleteConfirmed(record),
      });
      return actions;
    },

    // 桌面端按鈕配色：ghost 型用透明背景＋彩色邊框文字，其餘用彩色背景實體按鈕；停用時不套用顏色
    actionBtnStyle(action) {
      if (action.disabled) {
        return {};
      }
      if (action.ghost) {
        return {
          backgroundColor: "transparent",
          borderColor: action.color,
          color: action.color,
          "--action-color": action.color,
        };
      }
      return {
        backgroundColor: action.color,
        borderColor: action.color,
        color: "#fff",
      };
    },

    deleteConfirmed(record) {
      this.$inertia.delete(route("organizer.forms.destroy", { form: record.id }), {
        onSuccess: (page) => {
          console.log("删除成功", page);
        },
        onError: (error) => {
          Modal.error({
            title: this.$t("delete_failed") || "删除失败",
            content: this.$t("delete_requires_empty") || "只有沒有資料的表單才能刪除。此表單可能包含資料欄位或回應，請確認表單為空後再刪除。",
            okText: this.$t("acknowledged") || "知道了",
          });
        },
      });
    },

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

    searchData() {
      this.$inertia.get(
        route("organizer.forms.index"),
        { search: this.search, pagination: this.pagination },
        { preserveState: true }
      );
    },

    cloneForm(form) {
      Modal.confirm({
        title: this.$t("confirm_clone") || "確定要克隆此表格嗎？",
        content: form.title,
        okText: this.$t("yes") || "是",
        cancelText: this.$t("no") || "否",
        onOk: () => {
          this.$inertia.post(route("manage.form.clone", form), {
            onSuccess: (page) => {
            },
            onError: (error) => {
              message.error(error.message || "克隆失败");
            },
          });
        },
      });
    },

    openQrCodeModal(record) {
      this.qrCodeModal.title = record.title;
      this.qrCodeModal.url = `${window.location.origin}/form/?t=${record.uuid}`;
      this.qrCodeModal.isOpen = true;
    },

    updateQrCodeModalOpen(value) {
      this.qrCodeModal.isOpen = value;
    },

  },
};
</script>

<style scoped>
/* Keep row hover effect */
:deep(.ant-table-tbody > tr:hover > td) {
  background-color: #f9fafb;
}

/* 實體操作按鈕 hover：內聯樣式會覆蓋 antd 預設，故用 !important 提亮 */
.action-btn:not(.ant-btn-disabled):hover {
  filter: brightness(1.1);
}

/* ghost 操作按鈕 hover：以自身顏色輕微填充 */
.ghost-btn:not(.ant-btn-disabled):hover {
  background-color: color-mix(in srgb, var(--action-color) 12%, transparent);
}

/* Ensure the table scrolls horizontally on small screens */
:deep(.ant-table-content) {
  overflow-x: auto;
}

/* (Optional) small screen adjustments */
@media (max-width: 640px) {
  /* The table itself already scrolls because of the wrapper overflow-x-auto */
  /* You can keep the action dropdown, which is already hidden/shown with md:hidden / hidden md:flex */
}
</style>