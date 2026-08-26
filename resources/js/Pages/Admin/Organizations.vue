<template>
  <AdminLayout :title="$t('organization')">

    <!-- Filters Section -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow-sm">
      
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">
            {{ $t("organizations") }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            {{ $t("manage_your_organizations") }}
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <a-button
            type="primary"
            @click="createRecord()"
            class="!bg-indigo-600 hover:!bg-indigo-700"
          >
            <template #icon>
              <PlusOutlined />
            </template>
            {{ $t("create_organization") }}
          </a-button>
        </div>
      </div>
      
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">
            {{ $t('abbreviation') }}
          </label>
          <a-input
            v-model:value="search.abbr"
            :placeholder="$t('enter_abbreviation')"
            class="w-48"
            allow-clear
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">
            {{ $t('organization_name_zh') }}
          </label>
          <a-input
            v-model:value="search.name_zh"
            :placeholder="$t('enter_chinese_name')"
            class="w-56"
            allow-clear
          />
        </div>
        <a-button
          type="primary"
          @click="searchData"
          class="!bg-blue-600 hover:!bg-blue-700"
        >
          <template #icon>
            <SearchOutlined />
          </template>
          {{ $t("search") }}
        </a-button>
        <a-button
          @click="clearSearch"
          class="border-gray-300 text-gray-700 hover:!border-gray-400 hover:!text-gray-800"
        >
          <template #icon>
            <RedoOutlined />
          </template>
          {{ $t("clear") }}
        </a-button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
      <div class="overflow-x-auto">
        <a-table
          :dataSource="organizations.data"
          :columns="columns"
          :pagination="false"
          :row-class-name="(_record, index) => (index % 2 === 0 ? 'bg-gray-50' : 'bg-white')"
        >
          <template #headerCell="{ column }">
            <div class="font-semibold text-gray-900">
              {{ column.i18n ? $t(column.i18n) : column.title }}
            </div>
          </template>
          
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'abbr'">
              <div class="flex items-center">
                <div
                  v-if="record.avatar"
                  class="mr-3 h-8 w-8 overflow-hidden rounded-full"
                >
                  <img
                    :src="record.avatar"
                    :alt="record.abbr_en"
                    class="h-full w-full object-cover"
                  />
                </div>
                <span class="font-medium text-gray-900">
                  {{ record.abbr_en }}
                </span>
              </div>
            </template>

            <template v-else-if="column.dataIndex === 'name_zh'">
              <div class="text-gray-900">{{ record.name_zh }}</div>
              <div class="text-xs text-gray-500">{{ record.name_en }}</div>
            </template>

            <template v-else-if="column.dataIndex === 'email'">
              <div class="flex items-center text-gray-600">
                <MailOutlined class="mr-2" />
                {{ record.email }}
              </div>
            </template>

            <template v-else-if="column.dataIndex === 'manager'">
              <div v-if="record.users && record.users.length > 0">
                <a-tag v-for="user in record.users.slice(0, 2)" :key="user.id" class="mb-1 mr-1">
                  {{ user.name }}
                </a-tag>
                <a-tag v-if="record.users.length > 2" color="blue">
                  +{{ record.users.length - 2 }}
                </a-tag>
              </div>
              <span v-else class="text-gray-400">—</span>
            </template>

            <template v-else-if="column.dataIndex === 'status'">
              <a-tag :color="record.status ? 'green' : 'red'" class="rounded-full px-3 py-1">
                {{ record.status ? $t('active') : $t('inactive') }}
              </a-tag>
            </template>

            <template v-else-if="column.dataIndex === 'operation'">
              <div class="flex items-center space-x-2">
                <a-tooltip :title="$t('members')">
                  <a-button
                    :href="route('admin.organization.members', record.id)"
                    size="small"
                    class="!border-blue-200 !text-blue-600 hover:!border-blue-300 hover:!bg-blue-50"
                  >
                    <TeamOutlined />
                  </a-button>
                </a-tooltip>
                
                <a-tooltip :title="$t('edit')">
                  <a-button
                    @click="editRecord(record)"
                    size="small"
                    class="!border-green-200 !text-green-600 hover:!border-green-300 hover:!bg-green-50"
                  >
                    <EditOutlined />
                  </a-button>
                </a-tooltip>

                <a-tooltip :title="$t('delete')">
                  <a-popconfirm
                    :title="$t('confirm_delete_record')"
                    :ok-text="$t('yes')"
                    :cancel-text="$t('no')"
                    :ok-button-props="{ danger: true }"
                    @confirm="deleteRecord(record)"
                  >
                    <a-button
                      size="small"
                      danger
                      class="!border-red-200 hover:!border-red-300"
                    >
                      <DeleteOutlined />
                    </a-button>
                  </a-popconfirm>
                </a-tooltip>

                <a-tooltip :title="$t('masquerade')">
                  <a-button
                    @click="masqueradeOrganization(record)"
                    size="small"
                    class="!border-purple-200 !text-purple-600 hover:!border-purple-300 hover:!bg-purple-50"
                  >
                    <UserSwitchOutlined />
                  </a-button>
                </a-tooltip>
              </div>
            </template>
          </template>
        </a-table>
      </div>

      <!-- Pagination -->
      <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
        <Pagination :data="organizations" :search="search" />
      </div>
    </div>

    <!-- Modal Component -->
    <OrganizationModal
      :open="modal.isOpen"
      :organization="modal.organization"
      :users="users"
      :card-styles="cardStyles"
      @update:visible="modal.isOpen = $event"
      @save="onModalSave"
    />
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import OrganizationModal from "./OrganizationModal.vue"; // adjust path as needed
import { ref, reactive } from "vue";
import axios from 'axios';
import {
  PlusOutlined,
  SearchOutlined,
  RedoOutlined,
  EditOutlined,
  DeleteOutlined,
  UserSwitchOutlined,
  TeamOutlined,
  MailOutlined,
  PhoneOutlined,
  GlobalOutlined,
  SaveOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined
} from '@ant-design/icons-vue';

export default {
  components: {
    AdminLayout,
    Pagination,
    OrganizationModal,
    PlusOutlined,
    SearchOutlined,
    RedoOutlined,
    EditOutlined,
    DeleteOutlined,
    UserSwitchOutlined,
    TeamOutlined,
    MailOutlined,
    PhoneOutlined,
    GlobalOutlined,
    SaveOutlined,
    CheckCircleOutlined,
    CloseCircleOutlined
  },
  
  props: ["organizations", "users"],
  
  data() {
    return {
      modal: {
        isOpen: false,
        organization: null, // null means create mode
      },
      loading: false,
      search: {
        abbr: "",
        name_zh: ""
      },
      cardStyles: [],
      columns: [
        {
          title: "Abbreviation",
          i18n: "abbreviation",
          dataIndex: "abbr",
          width: 150,
        },
        {
          title: "Name",
          i18n: "organization_name_zh",
          dataIndex: "name_zh",
          width: 250,
        },
        {
          title: "Email",
          i18n: "email",
          dataIndex: "email",
          width: 200,
        },
        {
          title: "Manager",
          i18n: "manager",
          dataIndex: "manager",
          width: 200,
        },
        {
          title: "Status",
          i18n: "status",
          dataIndex: "status",
          width: 100,
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          width: 250,
          fixed: 'right',
        },
      ],
    };
  },
  
  created() {
    this.fetchCardStyles();
  },
  
  mounted() {
    // 從路由參數初始化搜索條件
    const params = this.route().params;
    this.search.abbr = params.search?.abbr || "";
    this.search.name_zh = params.search?.name_zh || "";
  },
  
  methods: {
    async fetchCardStyles() {
      try {
        const response = await axios.get(route("api.config.item", { key: "card_styles" }));
        this.cardStyles = Object.entries(response.data).map(([key, card]) => ({
          value: key,
          label: card.name
        }));
      } catch (error) {
        console.error("Failed to fetch card styles:", error);
      }
    },
    
    createRecord() {
      this.modal.organization = null;
      this.modal.isOpen = true;
    },
    
    editRecord(record) {
      this.modal.organization = { ...record };
      this.modal.isOpen = true;
    },
    
    // Called when modal emits 'save'
    onModalSave(formData) {
      // Determine mode by presence of id
      const isEdit = !!formData.id;
      this.loading = true;
      if (isEdit) {
        this.$inertia.patch(
          route("admin.organizations.update", formData.id),
          formData,
          {
            onSuccess: () => {
              this.loading = false;
              this.modal.isOpen = false;
              this.$message.success(this.$t('organization_updated_successfully'));
            },
            onError: (error) => {
              this.loading = false;
              console.error("Update failed:", error);
              this.$message.error(this.$t('update_failed'));
            },
          }
        );
      } else {
        this.$inertia.post(
          route("admin.organizations.store"),
          formData,
          {
            onSuccess: () => {
              this.loading = false;
              this.modal.isOpen = false;
              this.$message.success(this.$t('organization_created_successfully'));
            },
            onError: (error) => {
              this.loading = false;
              console.error("Store failed:", error);
              this.$message.error(this.$t('create_failed'));
            },
          }
        );
      }
    },
    
    deleteRecord(record) {
      this.$inertia.delete(route("admin.organizations.destroy", record.id), {
        onSuccess: () => {
          this.$message.success(this.$t('organization_deleted_successfully'));
        },
        onError: (error) => {
          console.error("Delete failed:", error);
          this.$message.error(this.$t('delete_failed'));
        },
      });
    },
    
    masqueradeOrganization(organization) {
      this.$inertia.post(
        route("admin.organization.masquerade", { organization: organization.id }),
        null,
        {
          onStart: () => {
            this.$message.loading(this.$t('switching_organization'), 0);
          },
          onSuccess: () => {
            this.$message.destroy();
            this.$message.success(this.$t('switched_successfully'));
          },
          onError: () => {
            this.$message.destroy();
            this.$message.error(this.$t('switch_failed'));
          },
        }
      );
    },
    
    searchData() {
      this.$inertia.get(route("admin.organizations.index"), {
        search: this.search,
      }, {
        preserveState: true,
        preserveScroll: true,
      });
    },
    
    clearSearch() {
      this.search = {
        abbr: "",
        name_zh: ""
      };
      this.$inertia.get(route("admin.organizations.index"), {}, {
        preserveState: true,
        preserveScroll: true,
      });
    },
  },
};
</script>

<style scoped>
/* 表格行懸停效果 */
:deep(.ant-table-tbody > tr:hover > td) {
  background-color: #f9fafb !important;
}

/* 標籤樣式 */
:deep(.ant-tag) {
  border-radius: 9999px;
  padding: 0 8px;
  font-size: 12px;
  height: 24px;
  line-height: 22px;
}

/* 按鈕懸停效果 */
:deep(.ant-btn:hover) {
  transform: translateY(-1px);
  transition: all 0.2s;
}

:deep(.ant-btn:active) {
  transform: translateY(0);
}
</style>