<template>
  <AdminLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("members") }}
      </h2>
    </template>
    <div class="flex justify-end pb-3 gap-3">
      <a-button @click="createRecord()" type="primary">
        {{ $t("create_member") }}
      </a-button>
    </div>
    <div class="container mx-auto">
      <div class="flex flex-auto gap-2">
        <a-select
          class="w-32"
          :placeholder="$t('please_select_organization')"
          v-model:value="search.organization"
          allowClear
          :options="organizations"
          :fieldNames="{ value: 'id', label: 'abbr' }"
        ></a-select>
        <a-input
          v-model:value="search.given_name"
          :placeholder="$t('please_input_given_name')"
          class="w-32"
        ></a-input>
        <a-input
          v-model:value="search.family_name"
          :placeholder="$t('please_input_family_name')"
          class="w-32"
        ></a-input>
        <a-button type="primary" @click="searchData">{{ $t("search") }}</a-button>
        <a-button type="primary" as="link" :href="route('admin.members.index')">{{ $t("clear") }}</a-button>
      </div>
    </div>
    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="members.data" :columns="columns" :pagination="pagination" @change="onPaginationChange">
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
              <a-button @click="createLogin(record)">{{ $t("create_login") }}</a-button>
            </template>
            <template v-else-if="column.dataIndex == 'login'">
              <span v-if="record['user']">
                {{ record["user"].email }}
              </span>
            </template>
            <template v-else-if="column.dataIndex == 'avatar'">
              <img :src="record.avatar_url" width="60" />
            </template>
            <template v-else-if="column.dataIndex == 'organization'">
              <span v-if="record.organization">
                {{ record.organization.abbr }}
              </span>
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>
        <!-- <Pagination :data="members" :search="search" /> -->
      </div>
    </div>
    <!-- Modal Start-->
    <a-modal v-model:open="modal.isOpen" :title="$t(modal.title)" width="60%">
      <a-form
        ref="modalRef"
        :model="modal.data"
        name="member"
        :label-col="{ span: 3 }"
        :wrapper-col="{ span: 16 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
        @finish="onFormFinish"
      >
        <a-form-item :label="$t('organizations')" name="organization_ids">
          <a-select
            v-model:value="modal.data.organization_id"
            show-search
            :filter-option="filterOption"
            :options="organizations"
            :fieldNames="{ value: 'id', label: 'name_zh' }"
          />
        </a-form-item>

        <a-form-item :label="$t('given_name')" name="given_name">
          <a-input type="input" v-model:value="modal.data.given_name" />
        </a-form-item>
        <a-form-item :label="$t('middle_name')" name="middle_name">
          <a-input type="input" v-model:value="modal.data.middle_name" />
        </a-form-item>
        <a-form-item :label="$t('family_name')" name="family_name">
          <a-input type="input" v-model:value="modal.data.family_name" />
        </a-form-item>
        <a-form-item :label="$t('display_name')" name="display_name">
          <a-input type="input" v-model:value="modal.data.display_name" />
        </a-form-item>

        <a-row :span="24">
          <a-col :span="18">
            <a-form-item :label="$t('email')" :label-col="{ span: 4 }" name="email">
              <a-input type="input" v-model:value="modal.data.email" />
            </a-form-item>
            <a-form-item :label="$t('gender')" :label-col="{ span: 4 }" name="gender">
              <a-switch
                v-model:checked="modal.data.gender"
                :checkedValue="1"
                :unCheckedValue="0"
              />
            </a-form-item>
            <a-form-item :label="$t('dob')" :label-col="{ span: 4 }" name="dob">
              <a-date-picker
                v-model:value="modal.data.dob"
                :format="dateFormat"
                :valueFormat="dateFormat"
              />
            </a-form-item>
            <template v-if="modal.data.user">
              <a-form-item :label="$t('users')" :label-col="{ span: 4 }" name="user_id">
                <p>{{ modal.data.user.email }}</p>
              </a-form-item>
            </template>
            <template v-else>
              <a-form-item :label="$t('users')" :label-col="{ span: 4 }" name="user_id">
                <a-select
                  v-model:value="modal.data.user_id"
                  show-search
                  :options="users"
                  :fieldNames="{ value: 'id', label: 'email' }"
                />
              </a-form-item>
            </template>
          </a-col>
          <a-col :span="6">
            <img :src="modal.data.avatar_url" width="200" />
          </a-col>
        </a-row>
      </a-form>
      <template #footer>
        <a-button @click="$refs.modalRef.$emit('finish')" type="primary">
          <span v-if="modal.mode == 'EDIT'">{{ $t("update") }}</span>
          <span v-if="modal.mode == 'CREATE'">{{ $t("create") }}</span>
        </a-button>
      </template>
    </a-modal>
    <!-- Modal End-->
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/Components/Pagination.vue";

export default {
  components: {
    AdminLayout,
    Pagination,
  },
  props: ["organizations", "members", "users"],
  data() {
    return {
      dateFormat: "YYYY-MM-DD",
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      search: {
        organization:null,
        given_name:null,
        family_name:null
      },
      pagination: {
        total: this.members.total,
        current: this.members.current_page,
        pageSize: this.members.per_page,
      },
      columns: [
        {
          title: "Organization",
          i18n: "organization",
          dataIndex: "organization",
        },
        {
          title: "Given Name",
          i18n: "given_name",
          dataIndex: "given_name",
        },
        {
          title: "Family Name",
          i18n: "family_name",
          dataIndex: "family_name",
        },
        {
          title: "Display Name",
          i18n: "display_name",
          dataIndex: "display_name",
        },
        {
          title: "Login Email",
          i18n: "login_email",
          dataIndex: "login",
        },
        {
          title: "Avatar",
          dataIndex: "avatar",
          i18n: "avatar",
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        name_zh: { required: true },
        mobile: { required: true },
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
  mounted() {
    console.log(this.route().params)
    if(this.route().params.search){
      this.search = {
          organization: parseInt(this.route().params.search.organization) || null,
          given_name: this.route().params.search.given_name || null,
          family_name: this.route().params.search.family_name || null,
      };

    }
    //this.search = this.route().params.search ?? {};
  },
  methods: {
    filterOption(input, option) {
      return option.full_name.toLowerCase().indexOf(input.toLowerCase()) >= 0;
    },
    onPaginationChange(page, filters, sorter) {
      this.$inertia.get(route("admin.members.index"),{
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
      console.log(this.modal);
      this.modal.data = {};
      //this.modal.data.organization_id = [];
      this.modal.mode = "CREATE";
      this.modal.title = "create";
      this.modal.isOpen = true;
      console.log(this.modal);
    },
    editRecord(record) {
      console.log(record.organizations);
      this.modal.data = { ...record };
      //this.modal.data.organization_ids = record.organizations.map((org) => org.id);
      this.modal.mode = "EDIT";
      this.modal.title = "edit";
      this.modal.isOpen = true;
    },
    onFormFinish() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          if (this.modal.mode == "CREATE") {
            this.storeRecord(this.modal.data);
            this.modal.isOpen = false;
          }
          if (this.modal.mode == "EDIT") {
            this.updateRecord(this.modal.data);
            this.modal.isOpen = false;
          }
        })
        .catch((err) => {
          console.log("error", err);
        });
    },
    storeRecord() {
      this.$inertia.post(route("admin.members.store"), this.modal.data, {
        onSuccess: (page) => {
          this.modal.data = {};
          this.modal.isOpen = false;
        },
        onError: (err) => {
          console.log(err);
        },
      });
    },
    updateRecord() {
      this.$inertia.patch(
        route("admin.members.update", this.modal.data.id),
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
      this.$inertia.delete(route("admin.members.destroy", record.id), {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          console.log(error);
        },
      });
    },
    createLogin(record) {
      console.log("create login" + record.id);
    },
    searchData() {
      this.$inertia.get(route("admin.members.index"),{ search: this.search },{
          onSuccess: (page) => {
            console.log(page);
          },
          // preserveState: true,
        }
      );
    },
  },
};
</script>
