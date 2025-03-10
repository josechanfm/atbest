<template>
  <AdminLayout :title="$t('users')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t("users") }}</h2>
    </template>
    <div class="flex justify-end pb-3 gap-3">
      <a-button @click="createRecord()" type="primary">
        {{ $t("create_user") }}
      </a-button>
    </div>
    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="users.data" :columns="columns" :pagination="pagination" @change="onPaginationChange">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <a-button @click="editRecord(record)">{{ $t("edit") }}</a-button>
              <a-popconfirm
                :title="$t('confirm_delete_record')"
                :ok-text="$t('yes')"
                :cancel-text="$t('no')"
                @confirm="deleteRecord(record)"
              >
                <a-button>{{ $t("delete") }}</a-button>
              </a-popconfirm>
            </template>
            <template v-else-if="column.dataIndex == 'organizations'">
              <ol class="list-decimal">
                <li v-for="organization in record['organizations']">
                  {{ organization.abbr }}
                </li>
              </ol>
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>
      </div>
    </div>
    <!-- Modal Start-->
    <a-modal v-model:open="modal.isOpen" :title="$t(modal.title)" width="60%">
      <a-form
        ref="modalRef"
        :model="modal.data"
        name="Teacher"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 20 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-form-item :label="$t('username')" name="name">
          <a-input v-model:value="modal.data.name" />
        </a-form-item>
        <a-form-item :label="$t('email')" name="email">
          <a-input v-model:value="modal.data.email" />
        </a-form-item>
        <a-form-item
          :label="$t('password')"
          name="password"
          v-if="modal.mode == 'CREATE'"
        >
          <a-input v-model:value="modal.data.password" />
        </a-form-item>
        <a-form-item :label="$t('organization_manager')" name="organization_ids">
          <a-select
            v-model:value="modal.data.organization_ids"
            mode="multiple"
            show-search
            :filter-option="filterOption"
            :options="organizations"
            :fieldNames="{ value: 'id', label: 'name_zh' }"
          />
        </a-form-item>
        <a-row>
          <a-col :span="4"></a-col>
          <a-col :span="10">
            <a-form-item :label="$t('roles')" name="roles">
              <a-checkbox-group v-model:value="modal.data.role_ids">
                <template v-for="role in roles">
                  <a-checkbox :value="role.id" :style="verticalStyle">
                    {{ role.name }} ({{ role.guard_name }})
                  </a-checkbox>
                </template>
              </a-checkbox-group>
            </a-form-item>
          </a-col>
          <a-col :span="10">
            <a-form-item :label="$t('permissions')" name="permissions">
              <a-checkbox-group v-model:value="modal.data.permission_ids">
                <template v-for="permission in permissions">
                  <a-checkbox :value="permission.id" :style="verticalStyle">{{
                    permission.name
                  }}</a-checkbox>
                </template>
              </a-checkbox-group>
            </a-form-item>

          </a-col>
        </a-row>
        <a-form-item :label="$t('password')" name="password" v-if="modal.mode == 'EDIT'">
          <a-input v-model:value="modal.data.password" />
        </a-form-item>

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
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { defineComponent, reactive } from "vue";

export default {
  components: {
    AdminLayout,
  },
  props: ["organizations", "users", "roles","permissions"],
  data() {
    return {
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      pagination: {
        total: this.users.total,
        current: this.users.current_page,
        pageSize: this.users.per_page,
      },
      teacherStateLabels: {},
      columns: [
        {
          title: "Username",
          i18n: "username",
          dataIndex: "name",
        },
        {
          title: "Email",
          i18n: "email",
          dataIndex: "email",
        },
        {
          title: "Organizations",
          i18n: "organizations",
          dataIndex: "organizations",
        },
        {
          title: "State",
          i18n: "state",
          dataIndex: "state",
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        name: { required: true },
        email: { required: true, type: "email" },
        password: { required: true },
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
      verticalStyle: {
        display: "flex",
        height: "30px",
        lineHeight: "30px",
        width: "100%",
        marginLeft: "20px",
      },
    };
  },
  created() {},
  methods: {
    filterOption(input, option) {
      return option.full_name.toLowerCase().indexOf(input.toLowerCase()) >= 0;
    },
    onPaginationChange(page, filters, sorter) {
      this.$inertia.get(route("admin.users.index"),{
          page: page.current,
          per_page: page.pageSize,
        },{
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (error) => {
            console.log(error);
          },
      });
    },
    createRecord() {
      this.modal.data = {};
      this.modal.mode = "CREATE";
      this.modal.title = "create";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.data.organization_ids = record.organizations.map((item) => item.id);
      this.modal.data.role_ids = record.roles.map((item) => item.id);
      this.modal.data.permission_ids = record.permissions.map((item) => item.id);
      this.modal.mode = "EDIT";
      this.modal.title = "edit";
      this.modal.isOpen = true;
    },
    storeRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post(route("admin.users.store"), this.modal.data, {
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
      this.$inertia.patch(
        route("admin.users.update", this.modal.data.id),
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
    },
    deleteRecord(record) {
      this.$inertia.delete(route("admin.users.destroy", record.id), {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          console.log(error);
        },
      });
    },
  },
};
</script>
