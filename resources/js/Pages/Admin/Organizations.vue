<template>
  <AdminLayout :title="$t('organization')">
    <template #header>
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
          <a-input-search
            v-model:value="search.abbr"
            :placeholder="$t('search_by_abbr')"
            style="width: 200px"
            @search="searchData"
          />
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
    </template>

    <!-- Filters Section -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow-sm">
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
                    :alt="record.abbr"
                    class="h-full w-full object-cover"
                  />
                </div>
                <span class="font-medium text-gray-900">
                  {{ record.abbr }}
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

    <!-- Modal -->
    <a-modal
      v-model:open="modal.isOpen"
      :title="modal.title"
      class="md:!w-[70%] !w-full"
      :footer="null"
      centered
      :styles="{ body: { padding: '24px' } }"
      wrap-class-name="organization-modal"
    >
      <a-form
        ref="modalRef"
        :model="modal.data"
        layout="horizontal"
        @finish="onFormFinish"
        class="organization-form"
      >
        <!-- Basic Information Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">
            {{ $t('basic_information') }}
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a-form-item
              :label="$t('abbreviation')"
              name="abbr"
              :rules="[{ required: true, message: $t('abbreviation_required') }]"
            >
              <a-input
                v-model:value="modal.data.abbr"
                :placeholder="$t('enter_abbreviation')"
                allow-clear
              />
            </a-form-item>

            <a-form-item
              :label="$t('registration_code')"
              name="registration_code"
            >
              <a-input
                v-model:value="modal.data.registration_code"
                :placeholder="$t('enter_registration_code')"
                allow-clear
              />
            </a-form-item>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a-form-item
              :label="$t('organization_name_zh')"
              name="name_zh"
              :rules="[{ required: true, message: $t('chinese_name_required') }]"
            >
              <a-input
                v-model:value="modal.data.name_zh"
                :placeholder="$t('enter_chinese_name')"
                allow-clear
              />
            </a-form-item>

            <a-form-item
              :label="$t('organization_name_en')"
              name="name_en"
            >
              <a-input
                v-model:value="modal.data.name_en"
                :placeholder="$t('enter_english_name')"
                allow-clear
              />
            </a-form-item>

            <a-form-item
              :label="$t('organization_name_pt')"
              name="name_pt"
            >
              <a-input
                v-model:value="modal.data.name_pt"
                :placeholder="$t('enter_portuguese_name')"
                allow-clear
              />
            </a-form-item>
          </div>
        </div>

        <!-- Contact Information Section -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">
            {{ $t('contact_information') }}
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a-form-item
              :label="$t('email')"
              name="email"
              :rules="[
                { required: true, message: $t('email_required') },
                { type: 'email', message: $t('invalid_email') }
              ]"
            >
              <a-input
                v-model:value="modal.data.email"
                type="email"
                :placeholder="$t('enter_email')"
                allow-clear
              >
                <template #prefix>
                  <MailOutlined class="text-gray-400" />
                </template>
              </a-input>
            </a-form-item>

            <a-form-item
              :label="$t('phone')"
              name="phone"
            >
              <a-input
                v-model:value="modal.data.phone"
                :placeholder="$t('enter_phone')"
                allow-clear
              >
                <template #prefix>
                  <PhoneOutlined class="text-gray-400" />
                </template>
              </a-input>
            </a-form-item>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <a-form-item
              :label="$t('address')"
              name="address"
            >
              <a-input
                v-model:value="modal.data.address"
                :placeholder="$t('enter_address')"
                allow-clear
              />
            </a-form-item>

            <a-form-item
              :label="$t('website')"
              name="href"
            >
              <a-input
                v-model:value="modal.data.href"
                :placeholder="$t('enter_website')"
                allow-clear
              >
                <template #prefix>
                  <GlobalOutlined class="text-gray-400" />
                </template>
              </a-input>
            </a-form-item>
          </div>
        </div>

        <!-- Organization Settings -->
        <div class="mb-6">
          <h3 class="mb-4 text-lg font-semibold text-gray-900">
            {{ $t('organization_settings') }}
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a-form-item
              :label="$t('avatar_url')"
              name="avatar"
            >
              <a-input
                v-model:value="modal.data.avatar"
                :placeholder="$t('enter_avatar_url')"
                allow-clear
              />
            </a-form-item>

            <a-form-item
              :label="$t('card_style')"
              name="card_style"
            >
              <a-select
                v-model:value="modal.data.card_style"
                :placeholder="$t('select_card_style')"
                allow-clear
              >
                <a-select-option
                  v-for="style in cardStyles"
                  :key="style.value"
                  :value="style.value"
                >
                  {{ style.label }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <a-form-item
              :label="$t('president')"
              name="president"
            >
              <a-input
                v-model:value="modal.data.president"
                :placeholder="$t('enter_president_name')"
                allow-clear
              />
            </a-form-item>

            <a-form-item
              :label="$t('manager')"
              name="manager"
            >
              <a-select
                v-model:value="modal.data.user_ids"
                mode="multiple"
                :placeholder="$t('select_managers')"
                allow-clear
                :options="modal.data.members?.filter((m) => m.user_id != null)"
                :fieldNames="{ value: 'user_id', label: 'given_name' }"
                class="w-full"
              />
            </a-form-item>
          </div>

          <div class="mt-4">
            <a-form-item
              :label="$t('content')"
              name="content"
              :wrapper-col="{ span: 24 }"
            >
              <a-textarea
                v-model:value="modal.data.content"
                :placeholder="$t('enter_content')"
                :rows="4"
                show-count
                :maxlength="500"
              />
            </a-form-item>
          </div>

          <div class="mt-4">
            <a-form-item
              :label="$t('status')"
              name="status"
            >
              <div class="flex items-center space-x-4">
                <a-switch
                  v-model:checked="modal.data.status"
                  :checked-value="1"
                  :unchecked-value="0"
                  :checked-children="$t('active')"
                  :un-checked-children="$t('inactive')"
                />
              </div>
            </a-form-item>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6">
          <a-button @click="modal.isOpen = false">
            {{ $t('cancel') }}
          </a-button>
          <a-button
            type="primary"
            :loading="loading"
            @click="$refs.modalRef.submit()"
            class="!bg-indigo-600 hover:!bg-indigo-700"
          >
            <template #icon>
              <SaveOutlined />
            </template>
            {{
              modal.mode === 'CREATE'
                ? $t('create_organization')
                : $t('update_organization')
            }}
          </a-button>
        </div>
      </a-form>
    </a-modal>
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/Components/Pagination.vue";
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
        data: {},
        title: "",
        mode: "",
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
      this.modal.data = {
        members: [],
        status: 1,
        user_ids: []
      };
      this.modal.mode = "CREATE";
      this.modal.title = this.$t('create_organization');
      this.modal.isOpen = true;
    },
    
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.mode = "EDIT";
      this.modal.title = this.$t('edit_organization');
      this.modal.isOpen = true;
    },
    
    async onFormFinish() {
      try {
        this.loading = true;
        await this.$refs.modalRef.validate();
        
        if (this.modal.mode === "CREATE") {
          await this.storeRecord();
        } else {
          await this.updateRecord();
        }
        
        this.modal.isOpen = false;
        this.$message.success(
          this.modal.mode === "CREATE"
            ? this.$t('organization_created_successfully')
            : this.$t('organization_updated_successfully')
        );
      } catch (error) {
        console.error("Form validation failed:", error);
        if (error.errorFields) {
          this.$message.error(this.$t('please_fill_required_fields'));
        }
      } finally {
        this.loading = false;
      }
    },
    
    storeRecord() {
      return new Promise((resolve, reject) => {
        this.$inertia.post(route("admin.organizations.store"), this.modal.data, {
          onSuccess: () => {
            this.modal.isOpen = false;
            resolve();
          },
          onError: (error) => {
            console.error("Store failed:", error);
            reject(error);
          },
        });
      });
    },
    
    updateRecord() {
      return new Promise((resolve, reject) => {
        this.$inertia.patch(
          route("admin.organizations.update", this.modal.data.id),
          this.modal.data,
          {
            onSuccess: () => {
              this.modal.isOpen = false;
              resolve();
            },
            onError: (error) => {
              console.error("Update failed:", error);
              reject(error);
            },
          }
        );
      });
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