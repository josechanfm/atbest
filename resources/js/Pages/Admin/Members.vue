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
          type="input"
          v-model:value="search.given_name"
          :placeholder="$t('please_input_given_name')"
          class="w-32"
        ></a-input>
        <a-input
          type="input"
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
    <MemberModal
      v-model:open="modal.isOpen"
      :member="modal.data"
      :mode="modal.mode"
      :organizations="organizations"
      :users="users"
      :saving="saving"
      @save="onFormFinish"
    />
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import MemberModal from "./MemberModal.vue";
import { message } from "ant-design-vue";

export default {
  components: {
    AdminLayout,
    Pagination,
    MemberModal,
  },
  props: ["organizations", "members", "users"],
  data() {
    return {
      saving: false,
      modal: {
        isOpen: false,
        data: {},
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
      this.modal.isOpen = true;
      console.log(this.modal);
    },
    editRecord(record) {
      console.log(record.organizations);
      this.modal.data = { ...record };
      //this.modal.data.organization_ids = record.organizations.map((org) => org.id);
      this.modal.mode = "EDIT";
      this.modal.isOpen = true;
    },
    onFormFinish(formData) {
      if (this.saving) return;
      this.saving = true;
      const options = {
        onSuccess: () => {
          this.modal.data = {};
          this.modal.isOpen = false;
        },
        onError: (errors) => {
          console.error('Failed to save member:', errors);
          message.error(Object.values(errors)[0] || this.$t('submit_failed'));
        },
        onFinish: () => { this.saving = false; },
      };
      if (this.modal.mode === "CREATE") {
        this.$inertia.post(route("admin.members.store"), formData, options);
      } else {
        this.$inertia.patch(route("admin.members.update", formData.id), formData, options);
      }
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
