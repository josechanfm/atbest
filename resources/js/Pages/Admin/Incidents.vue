<template>
  <AdminLayout title="Issue">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("incident.title") }}
      </h2>
    </template>
    <div class="flex justify-end pb-3 gap-3">
      <a-button @click="createRecord()" type="primary">
        {{ $t("incident.create") }}
      </a-button>
    </div>
    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="incidents.data" :columns="columns">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <a-button @click="editRecord(record)">{{ $t("incident.edit") }}</a-button>
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
        name="incident"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 20 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
        @finish="onFormFinish"
      >
        <a-form-item :label="$t('incident.topic')" name="topic">
          <a-select
            v-model:value="modal.data.topic"
            :options="topicOptions"
          />
        </a-form-item>
        <a-form-item :label="$t('incident.title_field')" name="title">
          <a-input type="input" v-model:value="modal.data.title" />
        </a-form-item>
        <a-form-item :label="$t('incident.content')" name="content">
          <a-textarea v-model:value="modal.data.content" :rows="10" />
        </a-form-item>
        <a-form-item :label="$t('incident.solution')" name="solution" >
          <a-textarea v-model:value="modal.data.solution" :rows="5"/>
        </a-form-item>
        <a-form-item :label="$t('incident.raised_at')" name="raised_at" >
          <a-date-picker v-model:value="modal.data.raised_at" :format="dateFormat" :valueFormat="dateFormat"/>
        </a-form-item>
        <a-form-item :label="$t('incident.solved_at')" name="solved_at" >
          <a-date-picker v-model:value="modal.data.solved_at" :format="dateFormat" :valueFormat="dateFormat"/>
        </a-form-item>
      </a-form>
      <template #footer>
        <a-button @click="$refs.modalRef.$emit('finish')" type="primary">
          <span v-if="modal.mode=='EDIT'">{{ $t('incident.update') }}</span>
          <span v-if="modal.mode=='CREATE'">{{ $t('incident.create') }}</span>
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
  props: ["incidents"],
  data() {
    return {
      dateFormat:'YYYY-MM-DD',
      topicOptions:[
        {value:"SUG",label:this.$t("incident.topic_suggestion")},
        {value:"DEG",label:this.$t("incident.topic_debug")},
        {value:"CHN",label:this.$t("incident.topic_change")},
        {value:"REQ",label:this.$t("incident.topic_request")},
        {value:"SEC",label:this.$t("incident.topic_security")},
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
          i18n: "incident.topic",
          dataIndex: "topic",
        },{
          title: "Title",
          i18n: "incident.title_field",
          dataIndex: "title",
        },{
          title: "Raised at",
          i18n: "incident.raised_at",
          dataIndex: "raised_at",
          key: "raised_at",
        },{
          title: "Solved at",
          i18n: "incident.solved_at",
          dataIndex: "solved_at",
          key: "solved_at",
        },{
          title: "User",
          i18n: "incident.user",
          dataIndex: "user_id",
          key: "user_id",
        },{
          title: "Operation",
          i18n: "incident.operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        topic: { required: true },
        title: { required: true},
      },
      validateMessages: {
        required: this.$t("incident.required"),
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
      this.modal.title = "incident.create";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.data.value=JSON.stringify(this.modal.data.value)
      this.modal.mode = "EDIT";
      this.modal.title = "incident.edit";
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
        this.$inertia.post(route("admin.incidents.store"), data, {
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
        route("admin.incidents.update", data.id),data,
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
