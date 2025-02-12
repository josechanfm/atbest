<template>
  <OrganizationLayout title="表格欄位" :breadcrumb="breadcrumb">

    <div class="flex justify-end pb-3 gap-3">
      <template v-if="isDraggable">
        <a-button type="primary" @click="reloadFormFields">
          {{ $t("finish") }}
        </a-button>
      </template>
      <template v-else>
        <a-button type="primary" @click="isDraggable = !isDraggable">
          {{ $t("dragger_sort") }}
        </a-button>
        <a-button @click="createRecord()" type="primary">
          {{ $t("create_field") }}
        </a-button>
      </template>
    </div>
    
    <a-table
      :dataSource="dataModel"
      :columns="columns"
      :customRow="customRow"
    >
      <template #bodyCell="{ column, record, index }">
          <template v-if="column.dataIndex === 'operation'">
            <a-button @click="editRecord(record)">{{ $t("edit") }}</a-button>
            <a-button @click="deleteRecord(record)" :disabled="form.published == 1">{{ $t("delete") }}</a-button>
          </template>
      </template>
    </a-table>

    <!-- Modal Start-->
    <a-modal
      v-model:open="modal.isOpen"
      :title="modal.mode == 'CREATE' ? $t('create_field') : $t('edit_field')"
      width="60%"
    >
      <a-form
        ref="modalRef"
        :model="modal.data"
        name="formField"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 20 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
        @finish="onFormFinish"
      >
        <!-- <a-form-item label="Field name" name="field_name">
                <a-input v-model:value="modal.data.field_name" />
            </a-form-item> -->
        <a-form-item :label="$t('field_label')" name="field_label">
          <a-input v-model:value="modal.data.field_label" @blur="onFieldLabelChanged" />
        </a-form-item>
        <a-form-item :label="$t('field_type')" name="type">
          <a-select
            v-model:value="modal.data.type"
            placeholder="Field Type"
            :options="fieldTypes"
            :fieldNames="{ value: 'value', label: 'label_zh' }"
            @change="onChangeType"
          />
        </a-form-item>
        <a-form-item
          :label="$t('row')"
          name="rows"
          v-if="['textarea', 'longtext', 'richtext'].includes(modal.data.type)"
        >
          <a-input-number v-model:value="modal.data.options" />
        </a-form-item>

        <template v-if="['radio', 'checkbox', 'dropdown'].includes(modal.data.type)">
          <a-form-item :label="$t('option')" name="options">
            <a-radio-group>
              <template v-for="option in modal.data.options">
                <a-radio :style="verticalStyle" :value="option.value">
                  <a-input v-model:value="option.label" />
                </a-radio>
              </template>
              <a-radio @click="addOptionItem">{{ $t("add_option") }}</a-radio>
            </a-radio-group>
          </a-form-item>
          ss
          <a-form-item :label="$t('template')" name="optionTemplate">
            <a-select :options="templateOptions" @change="onChangeOptionTemplate" />
          </a-form-item>
          <a-form-item
            :label="$t('layout_direction')"
            name="optionTemplate"
            v-if="['radio', 'checkbox'].includes(modal.data.type)"
          >
            <a-radio-group v-model:value="modal.data.direction">
              <a-radio value="H">{{ $t("horizontal") }}</a-radio>
              <a-radio value="V">{{ $t("vertical") }}</a-radio>
            </a-radio-group>
          </a-form-item>
        </template>
        <a-form-item :label="$t('compulsory')" name="required">
          <a-switch
            v-model:checked="modal.data.required"
            :unCheckedValue="0"
            :checkedValue="1"
            @change="modal.data.in_column=0"
          />
        </a-form-item>
        <!-- <a-form-item label="規則" name="rule">
                <a-input v-model:value="modal.data.rule" />
            </a-form-item> -->
        <a-form-item
          :label="$t('column_data')"
          name="in_column"
          v-if="modal.data.required"
        >
          <a-switch
            v-model:checked="modal.data.in_column"
            :unCheckedValue="0"
            :checkedValue="1"
          />
        </a-form-item>
        <a-form-item :label="$t('remark')" name="remark">
          <a-textarea v-model:value="modal.data.remark" />
        </a-form-item>
      </a-form>
      <template #footer>
        <a-button @click="$refs.modalRef.$emit('finish')" type="primary">
          <span v-if="modal.mode == 'EDIT'">{{ $t("update") }}</span>
          <span v-if="modal.mode == 'CREATE'">{{ $t("create") }}</span>
        </a-button>
      </template>
    </a-modal>
    <!-- Modal End-->
  </OrganizationLayout>
</template>

<script>
import OrganizationLayout from "@/Layouts/OrganizationLayout.vue";
import { defineComponent, reactive } from "vue";
import { VueDraggableNext } from "vue-draggable-next";
import { HolderOutlined } from "@ant-design/icons-vue";
import Sortable from "sortablejs";

export default {
  components: {
    OrganizationLayout,
    draggable: VueDraggableNext,
    HolderOutlined,
    Sortable
  },
  props: ["templateOptions", "form", "fields", "fieldTypes"],
  data() {
    return {
      breadcrumb: [
        { label: "表格列表", url: route("manage.forms.index") },
        { label: "表格欄位", url: null },
      ],
      //templateOptions:[],
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      dataModel:null,
      sourceObj:null,
      targetObj:null,
      isDraggable: false,
      columns: [
        {
          title: "Field Label",
          i18n: "field_label",
          dataIndex: "field_label",
        },
        {
          title: "Field Type",
          i18n: "field_type",
          dataIndex: "type",
        },
        {
          title: "Compulsory",
          i18n: "compulsory",
          dataIndex: "required",
        },
        {
          title: "Column Data",
          i18n: "column_data",
          dataIndex: "in_column",
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
        },
      ],
      rules: {
        field_label: { required: true },
        type: { required: true },
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
      },
    };
  },
  created() {
    this.dataModel=this.fields
  },
  methods: {
    createRecord() {
      this.modal.data = {};
      this.modal.data.form_id = this.form.id;
      this.modal.data.direction = "V";
      this.modal.mode = "CREATE";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      console.log(this.modal.data.options);
      // this.modal.data.options=JSON.parse(this.modal.data.options)
      if (this.modal.data.options == null) {
        this.modal.data.options = [{ value: "option_1", label: "option_1" }];
      }
      this.modal.mode = "EDIT";
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
    storeRecord(data) {
      this.$inertia.post(
        route("manage.form.fields.store", {
          form: data.form_id,
        }),
        data,
        {
          onSuccess: (page) => {
            data = {};
          },
          onError: (err) => {
            console.log(err);
          },
        }
      );
    },
    updateRecord(data) {
      this.$inertia.patch(
        route("manage.form.fields.update", {
          form: data.form_id,
          field: data,
        }),
        data,
        {
          onSuccess: (page) => {
            data = {};
            console.log(page);
          },
          onError: (error) => {
            console.log(error);
          },
        }
      );
    },
    deleteRecord(record) {
      if (!confirm("Are you sure want to remove?")) return;
      this.$inertia.delete(
        route("form.fields.destroy", {
          form: this.form.id,
          field: record.id,
        }),
        {
          onSuccess: (page) => {
            console.log("the field has been deleted!");
          },
          onError: (error) => {
            alert(error.message);
          },
        }
      );
    },
    addOptionItem() {
      const newOption = "option_" + (this.modal.data.options.length + 1);
      this.modal.data.options.push({ value: newOption, label: newOption });
    },
    onChangeOptionTemplate(value) {
      this.modal.data.options = this.templateOptions.find(
        (t) => t.value == value
      ).template;
    },
    onChangeType(value) {
      if (["textarea", "longtext", "richtext"].includes(this.modal.data.type)) {
        if (!Number.isInteger(this.modal.data.type)) {
          this.modal.data.options = 5;
        }
      } else if (["radio", "checkbox"].includes(this.modal.data.type)) {
        if (typeof this.modal.data.options !== "object") {
          this.modal.data.options = [{ value: "option_1", label: "option_1" }];
        }
      }
    },
    onFieldLabelChanged(value) {
      if (!this.modal.data.field_name) {
        this.modal.data.field_name = this.modal.data.field_label;
      }
    },

    reloadFormFields() {
      this.$inertia.reload({
        only: ["fields"],
        onSuccess: (page) => {
          this.isDraggable = false;
        },
      });
    },

    customRow(record, index){
      return {
        domProps:{
          draggable:this.isDraggable
        },
        style:{
          cursor: this.isDraggable?'move':'default'
        },
        // mouse move
        onMouseenter: event =>{
          if(this.isDraggable){
            var ev = event || window.event // for competible with IE
            ev.target.draggable = true
          }
        },
        // start drag
        onDragstart: event => {
          if(this.isDraggable){
            var ev = event || window.event
            ev.stopPropagation() // block popup
            this.sourceObj = record // get sourceObject with record id
          }
        },
        // drag crossing elements
        onDragover: event => {
          if(this.isDraggable){
            var ev = event || window.event
            ev.preventDefault()
          }
        },
        // mouse up
        onDrop: event => {
          if(this.isDraggable){
            var ev = event || window.event
            ev.stopPropagation()
            this.targetObj=record // get target Object
            // swap record position
            let sourceIndex = ''
            let targetIndex = ''
            this.dataModel.map((item,idx) => {
              if(this.sourceObj == item){
                console.log(idx)
                sourceIndex = idx
              }
              if(this.targetObj == item){
                targetIndex = idx
              }
            })
            // show swap data
            let arr=[]
            this.dataModel.map((item,idx) => {
              if(sourceIndex == idx){
                arr.push(this.targetObj)
              }else if(targetIndex == idx){
                arr.push(this.sourceObj)
              }else{
                arr.push(item)
              }
            })
            arr.map((item,idx) => {
              arr[idx].sequence=idx
              console.log(item);
            })
            this.dataModel=arr
            this.$inertia.post(route("manage.form.fieldsSequence", this.form.id), this.dataModel, {
              onSuccess: (page) => {
                console.log(page);
              },
              onError: (error) => {
                console.log(error);
              },
            });
          }
        },
      }
    }
  },
};
</script>
