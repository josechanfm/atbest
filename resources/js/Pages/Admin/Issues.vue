<template>
  <AdminLayout title="Issue">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("issues") }}
      </h2>
    </template>
    <div class="flex justify-end pb-3 gap-3">
      <a-button @click="createRecord()" type="primary">
        {{ $t("create") }}
      </a-button>
    </div>
    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="issues.data" :columns="columns">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <a-button @click="editRecord(record)">{{ $t("edit") }}</a-button>
            </template>
            <template v-else-if="column.dataIndex == 'user_id'">
              {{ record.user.name }}
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
        name="issue"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 20 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
        @finish="onFormFinish"
      >
        <a-input type="input" type="hidden" v-model:value="modal.data.id" />
        <a-form-item :label="$t('topic')" name="topic">
          <a-select
            v-model:value="modal.data.topic"
            :options="topicOptions"
          />
        </a-form-item>
        <a-form-item :label="$t('title')" name="title">
          <a-input type="input" v-model:value="modal.data.title" />
        </a-form-item>
        <a-form-item :label="$t('content')" name="content">
          <a-textarea v-model:value="modal.data.content" :rows="10" />
        </a-form-item>
        <a-form-item :label="$t('solution')" name="solution" >
          <a-textarea v-model:value="modal.data.solution" :rows="5"/>
        </a-form-item>
        <a-form-item :label="$t('raised_at')" name="raised_at" >
          <a-date-picker v-model:value="modal.data.raised_at" :format="dateFormat" :valueFormat="dateFormat"/>
        </a-form-item>
        <a-form-item :label="$t('solved_at')" name="solved_at" >
          <a-date-picker v-model:value="modal.data.solved_at" :format="dateFormat" :valueFormat="dateFormat"/>
        </a-form-item>
      </a-form>
      <template #footer>
        <a-button @click="$refs.modalRef.$emit('finish')" type="primary">
          <span v-if="modal.mode=='EDIT'">{{ $t('update') }}</span>
          <span v-if="modal.mode=='CREATE'">{{ $t('create') }}</span>
        </a-button>
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
  props: ["issues"],
  data() {
    return {
      dateFormat:'YYYY-MM-DD',
      topicOptions:[
        {value:"SUG",label:"Suggestion  "},
        {value:"DEG",label:"Debug"},
        {value:"CHN",label:"Change"},
        {value:"REQ",label:"Customer Request"},
        {value:"SEC",label:"Security Issue"},
      ],
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      columns: [
        {
          title: "Topic",
          i18n: "topic",
          dataIndex: "topic",
        },{
          title: "Title",
          i18n: "title",
          dataIndex: "title",
        },{
          title: "Raised at",
          i18n: "raised_at",
          dataIndex: "raised_at",
          key: "raised_at",
        },{
          title: "Solved at",
          i18n: "solved_at",
          dataIndex: "solved_at",
          key: "solved_at",
        },{
          title: "User",
          i18n: "user",
          dataIndex: "user_id",
          key: "user_id",
        },{
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        topic: { required: true },
        title: { required: true},
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
  created() {
  },
  methods: {
    createRecord() {
      this.modal.data = {};
      this.modal.mode = "CREATE";
      this.modal.title = "create";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.data.value=JSON.stringify(this.modal.data.value)
      this.modal.mode = "EDIT";
      this.modal.title = "edit";
      this.modal.isOpen = true;
    },
    onFormFinish(){
      this.$refs.modalRef
        .validateFields()
        .then(() => {
            if(this.modal.mode=='CREATE'){
              this.storeRecord(this.modal.data)
              this.modal.isOpen=false
            }
            if(this.modal.mode=='EDIT'){
              this.updateRecord(this.modal.data)
              this.modal.isOpen=false
            }
        })
        .catch((err) => {
          console.log("error", err);
        });
    },
    storeRecord(data) {
        this.$inertia.post(route("admin.issues.store"), data, {
          onSuccess: (page) => {
            data = {};
          },
          onError: (err) => {
            console.log(err);
          },
        });
    },
    updateRecord(data) {
      //data.value=JSON.parse(data.value);
      this.$inertia.patch(
        route("admin.issues.update", data.id),data,
        {
          onSuccess: (page) => {
            data = {}
          },
          onError: (error) => {
            console.log(error);
          },
        }
      );
    },
  },
};
</script>
