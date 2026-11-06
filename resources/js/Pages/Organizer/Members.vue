
<template>
  <OrganizerLayout :title="$t('members')" :breadcrumb="breadcrumb">
    <div class="flex gap-2 pb-3 text-right justify-end">
        <a-button type="primary" class="!rounded" @click="() => (visible = true)">
          {{ $t("import_members")}}
        </a-button>
        <a-button type="primary" class="!rounded" @click="createRecord()">
          {{ $t("create_member") }}
        </a-button>
    </div>
    <div class="mx-auto">
      <div class="flex flex-auto gap-2">
        <a-input
          type="input"
          v-model:value="search.given_name"
          :placeholder="$t('please_input_given_name')"
          class="w-64"
        ></a-input>
        <a-input
          type="input"
          v-model:value="search.family_name"
          :placeholder="$t('please_input_family_name')"
          class="w-64"
        ></a-input>
        <a-button type="primary" @click="searchData">{{ $t("search") }}</a-button>
      </div>
    </div>
    <div class="mx-auto py-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="members.data" :columns="columns" :pagination="false">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'gender'">
              {{ record.gender == 'M' ? $t('male') : $t('female') }}
            </template>
            <template v-else-if="column.dataIndex == 'operation'">
              <a-button :href="route('organizer.members.show', record.id)">
                {{ $t("view") }}
              </a-button>
              <a-button @click="editRecord(record)" :disabled="record.dismiss">{{ $t("edit") }}</a-button>
              <a-popconfirm
                :title="$t('confirm_delete_record')"
                :ok-text="$t('yes')"
                :cancel-text="$t('no')"
                @confirm="deleteConfirmed(record.id)"
                 :disabled="record.dismiss"
              >
                <a-button :disabled="record.dismiss">{{ $t("dismiss") }}</a-button>
              </a-popconfirm>
              <a-button @click="createLogin(record.id)" :disabled="record.user != null || record.dimiss">
                {{ $t("create_login") }}
              </a-button>
            </template>
            <template v-else-if="column.dataIndex == 'avatar'">
              <img :src="record.avatar" width="60" />
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>
        <Pagination :data="members" :search="search" />
      </div>
    </div>

    <MemberModal
      :open="modal.isOpen"
      :member="modal.data"
      :mode="modal.mode"
      :title="modal.title"
      :saving="saving"
      @cancel="onCancelModal"
      @save="saveRecord"
    />
    <a-modal :title="$t('import_members_list')" v-model:open="visible">
      <a-upload-dragger
        v-model:fileList="files"
        name="file"
        :beforeUpload="() => false"
        :multiple="false"
        accept=".xls,.xlsx"
      >
        <p class="ant-upload-drag-icon">
          <file-excel-outlined />
        </p>
        <p class="ant-upload-text">{{$t('import_members_information')}}</p>
        <p class="ant-upload-hint">
          {{$t('import_members_description')}}
        </p>
      </a-upload-dragger>

      <div class="mt-3" v-if="imported">
        <div class="font-bold my-3 text-green-500" v-if="errors.length === 0">
          {{$t('import_success')}}
        </div>
        <div class="font-bold my-3 text-yellow-500" v-else>
          {{$t('import_some_error')}}
        </div>
        <p v-for="(error, index) in errors" :key="index" class="font-mono m-0">
          <warning-outlined class="!text-yellow-500" />
          <template v-if="lang == 'zh'">
            {{$t('row')}} {{ error.row }}, {{ error.errors[0] }}
          </template>
          <template v-else>
            {{ error.row }} {{$t('row')}}, {{ error.errors[0] }}
          </template>
        </p>
      </div>
      <template #footer>
        <div class="flex w-full justify-between">
          <a href="/templates/member_list.xlsx">
            <a-button type="link">
              <template #icon>
                <DownloadOutlined />
              </template>
              {{$t('download_member_list_template')}}
            </a-button>
          </a>
          <a-button
            type="primary"
            class="bg-blue-500"
            @click="handleImport"
            :disabled="files.length === 0"
            >{{$t('import')}}</a-button
          >
        </div>
      </template>
    </a-modal>
    <!-- Modal End-->
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { Modal as PopupModal, message } from "ant-design-vue";
import Pagination from "@/Components/Pagination.vue";
import MemberModal from "./MemberModal.vue";
import axios from 'axios';
import { defineComponent, reactive } from "vue";
import {
  ExclamationCircleOutlined,
  DownloadOutlined,
  FileExcelOutlined,
  WarningOutlined,
} from "@ant-design/icons-vue";
import { getActiveLanguage } from 'laravel-vue-i18n';

export default {
  components: {
    OrganizerLayout,
    PopupModal,
    Pagination,
    MemberModal,
    DownloadOutlined,
    FileExcelOutlined,
    WarningOutlined,
    ExclamationCircleOutlined,
  },
  props: ["members"],
  data() {
    return {
      breadcrumb: [{ label: "會員列表", url: null }],
      saving: false,
      lang:getActiveLanguage(),
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      search: {},
      errors: [],
      files: [],
      pagination: {
        total: this.members.total,
        current: this.members.current_page,
        pageSize: this.members.per_page,
      },
      columns: [
        {
          title: "Given name",
          dataIndex: "given_name",
          i18n: "given_name",
          responsive: ["md"],
        },
        {
          title: "Family name",
          dataIndex: "family_name",
          i18n: "family_name",
        },
        {
          title: "Gender",
          dataIndex: "gender",
          i18n: "gender",
        },
        {
          title: "Date of birth",
          dataIndex: "dob",
          i18n: "dob",
        },
        {
          title: "State",
          dataIndex: "state",
          i18n: "state",
        },
        {
          title: "Avatar",
          dataIndex: "avatar",
          i18n: "avatar",
        },
        {
          title: "Operation",
          dataIndex: "operation",
          key: "operation",
          i18n: "operation",
        },
      ],
      labelCol: {
        style: {
          width: "150px",
        },
      },
      visible: false,
      imported: false,
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
    onCancelModal() {
      this.modal.data = {};
      this.modal.isOpen = false;
    },
    createRecord() {
      this.modal.data = {};
      this.modal.mode = "CREATE";
      this.modal.title = "create";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.mode = "EDIT";
      this.modal.title = "edit";
      this.modal.isOpen = true;
    },
    saveRecord(formData) {
      if (this.saving) return;
      this.saving = true;
      const options = {
        onSuccess: () => {
          this.modal.data = {};
          this.modal.isOpen = false;
        },
        onError: (errors) => {
          console.error("Failed to save member:", errors);
          message.error(Object.values(errors)[0] || this.$t("submit_failed"));
        },
        onFinish: () => { this.saving = false; },
      };
      if (this.modal.mode === "CREATE") {
        this.$inertia.post(route("organizer.members.store"), formData, options);
      } else {
        this.$inertia.patch(route("organizer.members.update", formData.id), formData, options);
      }
    },
    deleteConfirmed(recordId) {
      this.$inertia.delete(route("organizer.members.destroy", { member: recordId }));
    },

    createLogin(recordId) {
      axios.post(route("organizer.member.createLogin", recordId)).then((response) => {
        if (response.data.result == false) {
          PopupModal.warning({
            title: "Email Duplicated",
            content: response.data.message,
          });
        }
        this.modal.data = {};
        this.modal.isOpen = false;
      });
    },
    validateNotNull(rule) {
      if (rule.value) {
        return Promise.resolve();
      } else {
        return Promise.reject("必填欄位 Required input!");
      }
    },
    searchData() {
      this.$inertia.get(
        route("organizer.members.index"),
        { search: this.search, pagination: this.pagination },
        {
          onSuccess: (page) => {
            console.log(page);
          },
          preserveState: true,
        }
      );
    },
    handleImport() {
      const formData = new FormData();
      formData.append("file", this.files[0].originFileObj);

      // TODO: handle import athlete list
      axios
        .post(
          route("organizer.members.import"),
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        )
        .then(({ data }) => {
          this.$message.success("匯入成功");
          this.files = [];
          this.errors = data.errors;
          this.imported = true;

          this.$emit("imported");
        })
        .catch(() => {
          this.$message.error("匯入失敗");
        });
    },
  },
};
</script>
