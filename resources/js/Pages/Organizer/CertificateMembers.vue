<template>
  <OrganizerLayout title="Dashboard" :organization="organization">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("certificates") }}
      </h2>
    </template>
    <div class="flex-auto pb-3 text-right">
      <a-button type="primary" class="!rounded" @click="createRecord()">
        {{ $t("add_member")}}
      </a-button>
    </div>
    <a-table :dataSource="certificate.members" :columns="columns">
      <!-- <template #headerCell="{ column }">
        {{ column.i18n ? $t(column.i18n) : column.title }}
      </template> -->
      <template #bodyCell="{ column, text, record, index }">
        <template v-if="column.dataIndex == 'operation'">
          <a-button @click="editRecord(record)">{{ $t("edit") }}</a-button>
        </template>
        <template v-else-if="column.dataIndex == 'cert_number'">
          {{ record.pivot.number_display }}
        </template>
        <template v-else-if="column.dataIndex == 'display_name'">
          {{ record.pivot.display_name }}
        </template>
        <template v-else-if="column.dataIndex == 'issue_date'">
          {{ record.pivot.issue_date }}
        </template>
        <template v-else-if="column.dataIndex == 'valid_from'">
          {{ record.pivot.valid_from }}
        </template>
        <template v-else-if="column.dataIndex == 'valid_until'">
          {{ record.pivot.valid_until }}
        </template>
        <template v-else>
          {{ record[column.dataIndex] }}
        </template>
      </template>
      <template #expandedRowRender="{ record }">
        <p>{{$t('given_name')}}: {{ record.given_name }}</p>
        <p>{{$t('middle_name')}}: {{ record.middle_name }}</p>
        <p>{{$t('family_name')}}: {{ record.family_name }}</p>
        <p>{{$t('display_name')}}: {{ record.display_name }}</p>
        <p>{{$t('gender')}}: {{ record.gender }}</p>
        <p>{{$t('dob')}}: {{ record.dob }}</p>
        <p>{{$t('email')}}: {{ record.email }}</p>
        <p>{{$t('number')}}: {{ record.pivot.number }}</p>
        <p>Number Display: {{ record.pivot.number_display }}</p>
        <p>{{$t('issue_date')}}: {{ record.pivot.issue_date }}</p>
        <p>{{$t('valid_until')}}: {{ record.pivot.valid_until }}</p>
        <p>{{$t('authorized_by')}}: {{ record.pivot.authorized_by }}</p>
        <p>{{$t('remark')}}: {{ record.pivot.remark }}</p>
        <p>Avata: {{ record.pivot.avata }}</p>
      </template>
      <template #expandColumnTitle> Details </template>
    </a-table>

    <!-- Modal Start-->
    <a-modal v-model:open="modal.isOpen" :title="modal.title" width="60%">
      <a-form
        ref="modalRef"
        :model="modal.data"
        name="certificate"
        :label-col="{ span: 8 }"
        :wrapper-col="{ span: 16 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-form-item label="Family Name" name="family_name" v-if="modal.data.id">
            {{ modal.data.family_name}}, {{ modal.data.given_name}}
        </a-form-item>
        <a-form-item label="Member" name="member" v-else>
            <a-select v-model:value="modal.data.pivot.member_id" :options="memberOptions()"/>
        </a-form-item>
        <a-form-item label="Cert display name" name="display_nmae">
            <a-input v-model:value="modal.data.pivot.display_name" />
        </a-form-item>
        <a-form-item label="Cert number" name="number_display">
            <a-input v-model:value="modal.data.pivot.number_display" />
        </a-form-item>
        <a-form-item :label="$t('issue_date')" name="issue_date">
          <a-date-picker
            v-model:value="modal.data.pivot.issue_date"
            :format="dateFormat"
            :valueFormat="dateFormat"
          />
        </a-form-item>
        <a-form-item :label="$t('valid_from')" name="valid_from">
          <a-date-picker
            v-model:value="modal.data.pivot.valid_from"
            :format="dateFormat"
            :valueFormat="dateFormat"
          />
        </a-form-item>
        <a-form-item :label="$t('valid_until')" name="valid_until">
          <a-date-picker
            v-model:value="modal.data.pivot.valid_until"
            :format="dateFormat"
            :valueFormat="dateFormat"
          />
        </a-form-item>
        <a-form-item :label="$t('authorized_by')" name="authorized_by">
          <a-input v-model:value="modal.data.pivot.authorized_by" />
        </a-form-item>
        <a-form-item :label="$t('remark')" name="remark">
          <a-textarea v-model:value="modal.data.pivot.remark" />
        </a-form-item>
        <!-- <a-form-item label="Avata" name="avata">
                    <a-input v-model:value="modal.data.avata" />
                </a-form-item> -->
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
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";

export default {
  components: {
    OrganizerLayout,
  },
  props: ["organization", "certificate", "members"],
  data() {
    return {
      breadcrumb:[
          {label:"證書列表" ,url:route('organizer.certificates.index')},
          {label:"會員證書" ,url:null},
      ],
      dateFormat: "YYYY-MM-DD",
      modal: {
        isOpen: false,
        data:{}
,       title: "Modal",
        mode: "",
      },
      columns: [
        {
          title: "Display Name",
          dataIndex: "display_name",
        },{
          title: "Cert Number",
          dataIndex: "cert_number",
        },{
          title: "Issue Date",
          dataIndex: "issue_date",
        },{
          title: "Valid From",
          dataIndex: "valid_from",
        },{
          title: "Valid Until",
          dataIndex: "valid_until",
        },{
          title: "Operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        display_name: { required: true },
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
  methods: {
    memberOptions(){
      console.log(this.members)
      return this.members.map(m=>({value:m.id, label:m.family_name+', '+m.given_name}));
    },
    createRecord() {
      this.modal.data = {
        pivot:{
          certificate_id: this.certificate.id,
          member_id:null,
          remark:null
        }
      };
      this.modal.member = {};
      this.modal.mode = "CREATE";
      this.modal.title = "Add New Member";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      // this.modal.member = record;
      this.modal.mode = "EDIT";
      this.modal.title = "Edit";
      this.modal.isOpen = true;
    },
    storeRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          console.log(this.modal.data);
          this.$inertia.post(
            route("organizer.certificate.members.store", {
              certificate: this.certificate.id,
            }),
            this.modal.data.pivot,
            {
              onSuccess: (page) => {
                this.modal.isOpen = false;
              },
              onError: (err) => {
                console.log(err);
              },
            }
          );
        })
        .catch((err) => {
          console.log(err);
        });
    },
    updateRecord() {
      console.log(this.modal.data);
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.put(
            route("organizer.certificate.members.update", {
              certificate: this.certificate.id,
              member: this.modal.data.pivot.member_id,
            }),
            this.modal.data.pivot,
            {
              onSuccess: (page) => {
                //this.modal.data = {};
                this.modal.isOpen = false;
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
    onChangeMember(memberId) {
      const member = this.members.find((m) => m.id == memberId);
      this.modal.member = member;
      this.modal.data.display_name = member.given_name + " " + member.family_name;
    },
  },
};
</script>
