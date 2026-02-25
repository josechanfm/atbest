
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
    <div class="container mx-auto">
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
    <div class="container mx-auto py-5">
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
              <img :src="record.avatar_url" width="60" />
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>
        <Pagination :data="members" :search="search" />
      </div>
    </div>

    <!-- Modal Start-->
    <a-modal v-model:open="modal.isOpen" :title="$t(modal.title)" width="60%">
      <a-form
        ref="modalRef"
        :model="modal.data"
        :label-col="{ span: 4 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-row :span="24">
          <a-col :span="8"> </a-col>
          <a-col :span="8"> </a-col>
          <a-col :span="8">
            <img :src="modal.data.avatar_url" width="200" />
          </a-col>
        </a-row>
        <a-form-item :label="$t('given_name')" name="given_name">
          <a-input type="input" v-model:value="modal.data.given_name" />
        </a-form-item>
        <a-row :span="24">
          <a-col :span="12">
            <a-form-item
              :label="$t('middle_name')"
              name="middle_name"
              :label-col="{ span: 8 }"
            >
              <a-input type="input" v-model:value="modal.data.middle_name" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item
              :label="$t('family_name')"
              name="family_name"
              :label-col="{ span: 6 }"
            >
              <a-input type="input" v-model:value="modal.data.family_name" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item :label="$t('display_name')" name="display_name">
          <a-input type="input" v-model:value="modal.data.display_name" />
        </a-form-item>
        <a-row :span="24">
          <a-col :span="12">
            <a-form-item :label="$t('gender')" name="gender" :label-col="{ span: 8 }">
              <a-radio-group v-model:value="modal.data.gender" button-style="solid">
                <a-radio-button value="M">{{ $t("male") }}</a-radio-button>
                <a-radio-button value="F">{{ $t("female") }}</a-radio-button>
              </a-radio-group>
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item :label="$t('dob')" name="dob" :label-col="{ span: 8 }">
              <a-date-picker
                v-model:value="modal.data.dob"
                :format="dateFormat"
                :valueFormat="dateFormat"
              />
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :span="24">
          <a-col :span="12">
            <a-form-item
              :label="$t('email')"
              name="email"
              :label-col="{ span: 8 }"
              :wrapper-col="{ span: 14, offset: 0 }"
            >
              <a-input type="input" v-model:value="modal.data.email" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item
              :label="$t('mobile_number')"
              name="mobile"
              :label-col="{ span: 8 }"
            >
              <a-input type="input" v-model:value="modal.data.mobile" />
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
      <template #footer>
        <a-button key="back" @click="onCancelModal">返回</a-button>

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
    <a-modal :title="$t('import_athletes_list')" v-model:open="visible">
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
        <p class="ant-upload-text">{{$t('import_athletes_information')}}</p>
        <p class="ant-upload-hint">
          {{$t('import_athletes_description')}}
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
import { Modal as PopupModal } from "ant-design-vue";
import Pagination from "@/Components/Pagination.vue";
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
    DownloadOutlined,
    FileExcelOutlined,
    WarningOutlined,
    ExclamationCircleOutlined,
  },
  props: ["members"],
  data() {
    return {
      breadcrumb: [{ label: "會員列表", url: null }],
      dateFormat: "YYYY-MM-DD",
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
      rules: {
        given_name: { required: true },
        family_name: { required: true },
        gender: { required: true },
        dob: { required: true },
        email: { required: true, type: "email" },
        state: { required: true },
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
    storeRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post(route("organizer.members.store"), this.modal.data, {
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
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.patch(
            route("organizer.members.update", this.modal.data.id),
            this.modal.data,
            {
              onSuccess: (page) => {
                this.modal.isOpen = false;
                //this.modal.data = {};
                console.log(page);
              },
              onError: (error) => {
                console.log(error);
              },
            }
          );
        })
        .catch((err) => {
          console.log("error", err);
        });
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

