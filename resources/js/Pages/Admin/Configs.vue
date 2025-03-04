<template>
  <AdminLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("configs") }}
      </h2>
    </template>
    <div class="flex justify-end pb-3 gap-3">
      <a-button @click="createRecord()" type="primary">
        {{ $t("create_config_item") }}
      </a-button>
    </div>
    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="configs" :columns="columns">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <a-button @click="editRecord(record)">{{ $t("edit") }}</a-button>
            </template>
            <template v-else-if="column.dataIndex == 'organization_id'">
              {{ record[column.dataIndex] }}
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
        name="config"
        :label-col="{ span: 8 }"
        :wrapper-col="{ span: 16 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
        @finish="onFormFinish"
      >
        <a-input type="hidden" v-model:value="modal.data.id" />
        <a-form-item :label="$t('organization')" name="organization_id">
          <a-select v-model:value="modal.data.organization_id">
            <a-select-option :value="0">General Config Item</a-select-option>
              <a-select-option v-for="org in organizations" :value="org.value" :key="org.value">{{ org.name_zh }}</a-select-option>
          </a-select>
        </a-form-item>
        <a-form-item :label="$t('key')" name="key">
          <a-input v-model:value="modal.data.key" />
        </a-form-item>
        <a-form-item :label="$t('value')" name="value">
          <a-textarea v-model:value="modal.data.value" :rows="15" />
        </a-form-item>
        <a-form-item :label="$t('remark')" name="remark">
          <a-textarea v-model:value="modal.data.remark" />
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
  props: ["organizations", "users", "configs"],
  data() {
    return {
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      teacherStateLabels: {},
      columns: [
        {
          title: "Organization",
          i18n: "organization",
          dataIndex: "organization_id",
        },
        {
          title: "Key",
          i18n: "key",
          dataIndex: "key",
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
    };
  },
  created() {
    //this.organizations.unshift({ id: 0, name_zh: "General Config Item" });
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
        data.json_value=JSON.parse(data.value);
        this.$inertia.post(route("admin.configs.store"), data, {
          onSuccess: (page) => {
            data = {};
                // this.organizations.unshift({ id: 0, name_zh: "General Config Item" });
          },
          onError: (err) => {
            console.log(err);
          },
        });
    },
    updateRecord(data) {
      data.json_value=JSON.parse(data.value);
      this.$inertia.patch(
        route("admin.configs.update", data.id),data,
        {
          onSuccess: (page) => {
            data = {}
            // this.organizations.unshift({ id: 0, name_zh: "General Config Item" });
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
