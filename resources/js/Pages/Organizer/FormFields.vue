<template>
  <OrganizerLayout title="表格欄位" :breadcrumb="breadcrumb">
    <div class="flex justify-end pb-3 gap-3">
      <template v-if="isDraggable">
        <a-button type="primary" @click="finishDragging">
          {{ $t("finish") }}
        </a-button>
      </template>
      <template v-else>
        <a-button type="primary" @click="startDragging">
          <HolderOutlined /> {{ $t("dragger_sort") }}
        </a-button>
        <a-button @click="createRecord" type="primary">
          {{ $t("create_field") }}
        </a-button>
      </template>
    </div>

    <a-table
      id="dragTable"
      :dataSource="dataModel"
      :columns="columns"
      :rowKey="(record) => record.id"
      :loading="loading"
      :row-class-name="() => (isDraggable ? 'draggable-row' : '')"
      bordered
    >
      <template #headerCell="{ column }">
        {{ column.i18n ? $t(column.i18n) : column.title }}
      </template>

      <template #bodyCell="{ column, record, index }">
        <template v-if="column.dataIndex === 'drag'">
          <HolderOutlined class="drag-icon" :class="{ 'active-drag': isDraggable }" />
        </template>

        <template v-else-if="column.dataIndex === 'operation'">
          <div class="flex gap-2">
            <a-button size="small" @click="editRecord(record)" :disabled="isDraggable">
              {{ $t("edit") }}
            </a-button>
            <a-popconfirm
              :title="$t('confirm_delete_field')"
              :ok-text="$t('yes')"
              :cancel-text="$t('no')"
              @confirm="deleteRecord(record)"
              :disabled="form.published == 1 || isDraggable"
            >
              <a-button size="small" :disabled="form.published == 1 || isDraggable" danger>
                {{ $t("delete") }}
              </a-button>
            </a-popconfirm>
          </div>
        </template>

        <template v-else-if="column.dataIndex === 'required'">
          <a-tag :color="record.required ? 'green' : 'default'">
            {{ record.required ? $t('yes') : $t('no') }}
          </a-tag>
        </template>

        <template v-else-if="column.dataIndex === 'in_column'">
          <a-tag :color="record.in_column ? 'blue' : 'default'">
            {{ record.in_column ? $t('yes') : $t('no') }}
          </a-tag>
        </template>

        <template v-else>
          <span v-if="column.dataIndex === 'type'">
            {{ getFieldTypeLabel(record.type) }}
          </span>
          <span v-else>
            {{ record[column.dataIndex] }}
          </span>
        </template>
      </template>
    </a-table>

    <a-modal
      v-model:open="modal.isOpen"
      :title="modal.mode === 'CREATE' ? $t('create_field') : $t('edit_field')"
      width="700px"
      :confirm-loading="modalLoading"
      @ok="handleModalOk"
      @cancel="handleModalCancel"
    >
      <a-form
        ref="modalFormRef"
        :model="modal.data"
        name="formField"
        :label-col="{ span: 5 }"
        :wrapper-col="{ span: 19 }"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
      >
        <a-form-item :label="$t('field_label')" name="field_label">
          <a-input 
            type="input" 
            v-model:value="modal.data.field_label" 
            @blur="onFieldLabelChanged"
            :placeholder="$t('please_input_field_label')"
          />
        </a-form-item>

        <a-form-item :label="$t('field_type')" name="type">
          <a-select
            v-model:value="modal.data.type"
            :placeholder="$t('please_select_field_type')"
            :options="fieldTypeOptions"
            @change="onChangeType"
          />
        </a-form-item>

        <a-form-item
          :label="$t('row')"
          name="rows"
          v-if="textAreaTypes.includes(modal.data.type)"
        >
          <a-input-number 
            v-model:value="modal.data.options" 
            :min="3" 
            :max="20"
            :placeholder="$t('please_input_rows')"
          />
        </a-form-item>

        <template v-if="optionTypes.includes(modal.data.type)">
          <a-form-item :label="$t('option')" name="options" required>
            <div class="options-list">
              <div 
                v-for="(option, idx) in modal.data.options" 
                :key="idx" 
                class="option-item"
              >
                <a-input
                  type="input"
                  v-model:value="option.label" 
                  :placeholder="$t('option_label')"
                  style="width: 200px"
                />
                <a-button 
                  type="text" 
                  danger 
                  @click="removeOptionItem(idx)"
                  :disabled="modal.data.options.length <= 1"
                >
                  <DeleteOutlined />
                </a-button>
              </div>
              <a-button type="dashed" @click="addOptionItem" block>
                <PlusOutlined /> {{ $t("add_option") }}
              </a-button>
            </div>
          </a-form-item>

          <a-form-item :label="$t('template')" name="optionTemplate">
            <a-select
              v-model:value="selectedTemplate"
              :options="templateOptions"
              :fieldNames="{ label: 'label_zh', value: 'value' }"
              :placeholder="$t('please_select_template')"
              allow-clear
              @change="onChangeOptionTemplate"
            />
          </a-form-item>

          <a-form-item
            :label="$t('layout_direction')"
            name="direction"
            v-if="radioCheckboxTypes.includes(modal.data.type)"
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
            :checked-value="1"
            :unchecked-value="0"
            @change="onRequiredChange"
          />
        </a-form-item>

        <a-form-item
          :label="$t('column_data')"
          name="in_column"
          v-if="modal.data.required"
        >
          <a-switch
            v-model:checked="modal.data.in_column"
            :checked-value="1"
            :unchecked-value="0"
          />
          <span class="ml-2 text-gray-500 text-sm">{{ $t('column_data_note') }}</span>
        </a-form-item>

        <a-form-item :label="$t('remark')" name="remark">
          <a-textarea 
            v-model:value="modal.data.remark" 
            :rows="3"
            :placeholder="$t('please_input_remark')"
          />
        </a-form-item>
      </a-form>
    </a-modal>
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { message } from "ant-design-vue";
import { HolderOutlined, DeleteOutlined, PlusOutlined } from "@ant-design/icons-vue";
import Sortable from "sortablejs";

export default {
  name: "FormFields",
  components: {
    OrganizerLayout,
    HolderOutlined,
    DeleteOutlined,
    PlusOutlined,
  },
  props: {
    form: { type: Object, required: true },
    fields: { type: Array, required: true },
    fieldTypes: { type: Array, required: true },
    templateOptions: { type: Array, default: () => [] },
  },
  data() {
    return {
      breadcrumb: [
        { label: "表格列表", url: route("organizer.forms.index") },
        { label: "表格欄位", url: null },
      ],
      loading: false,
      modalLoading: false,
      isDraggable: false,
      dataModel: [],
      selectedTemplate: null,
      sortableInstance: null,
      
      // 確保 modal 結構完整初始化，避免未定義錯誤
      modal: {
        isOpen: false,
        mode: "CREATE",
        data: {
          form_id: this.form?.id || null,
          field_label: "",
          field_name: "",
          type: "",
          options: [],
          direction: "V",
          required: 0,
          in_column: 0,
          remark: "",
        },
      },
      
      columns: [
        { title: "", dataIndex: "drag", width: 50, align: "center" },
        { title: "Field Label", i18n: "field_label", dataIndex: "field_label" },
        { title: "Field Type", i18n: "field_type", dataIndex: "type" },
        { title: "Compulsory", i18n: "compulsory", dataIndex: "required" },
        { title: "Column Data", i18n: "column_data", dataIndex: "in_column" },
        { title: "Operation", i18n: "operation", dataIndex: "operation", width: 150 },
      ],
      rules: {
        field_label: { required: true, message: "請輸入欄位標籤" },
        type: { required: true, message: "請選擇欄位類型" },
      },
      validateMessages: { required: "${label} is required!" },
      textAreaTypes: ["textarea", "longtext", "richtext"],
      optionTypes: ["radio", "checkbox", "dropdown"],
      radioCheckboxTypes: ["radio", "checkbox"],
    };
  },
  computed: {
    fieldTypeOptions() {
      return this.fieldTypes.map(type => ({
        label: type.label_zh,
        value: type.value,
      }));
    },
  },
  watch: {
    fields: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.dataModel = JSON.parse(JSON.stringify(newVal));
        }
      },
    },
  },
  beforeUnmount() {
    if (this.sortableInstance) {
      this.sortableInstance.destroy();
    }
  },
  methods: {
    getDefaultModalData() {
      return {
        form_id: this.form?.id || null,
        field_label: "",
        field_name: "",
        type: "",
        options: [],
        direction: "V",
        required: 0,
        in_column: 0,
        remark: "",
      };
    },
    getFieldTypeLabel(typeValue) {
      const found = this.fieldTypes.find(t => t.value === typeValue);
      return found ? found.label_zh : typeValue;
    },
    
    initSortable() {
      this.$nextTick(() => {
        const table = document.querySelector("#dragTable .ant-table-tbody");
        if (!table) return;

        if (this.sortableInstance) {
          this.sortableInstance.destroy();
        }

        this.sortableInstance = new Sortable(table, {
          handle: ".drag-icon",
          animation: 200,
          ghostClass: "sortable-ghost",
          chosenClass: "sortable-chosen",
          onEnd: (evt) => {
            const { oldIndex, newIndex } = evt;
            if (oldIndex === newIndex) return;

            const currRow = this.dataModel.splice(oldIndex, 1)[0];
            this.dataModel.splice(newIndex, 0, currRow);

            this.dataModel.forEach((item, idx) => {
              item.sequence = idx;
            });

            this.saveSequence();
          },
        });
      });
    },

    startDragging() {
      this.isDraggable = true;
      message.info(this.$t("drag_to_sort"));
      this.initSortable();
    },
    finishDragging() {
      this.isDraggable = false;
      if (this.sortableInstance) {
        this.sortableInstance.destroy();
        this.sortableInstance = null;
      }
      this.reloadFormFields();
    },

    saveSequence() {
      this.$inertia.post(
        route("organizer.form.fieldsSequence", this.form.id),
        { fields: this.dataModel },
        {
          preserveState: true,
          preserveScroll: true,
          onSuccess: () => {
            message.success(this.$t("sequence_updated"));
          },
          onError: (error) => {
            console.error("Sequence error:", error);
            message.error(this.$t("sequence_update_failed"));
            this.reloadFormFields();
          },
        }
      );
    },

    createRecord() {
      this.modal.mode = "CREATE";
      this.modal.data = this.getDefaultModalData();
      this.selectedTemplate = null;
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.mode = "EDIT";
      const clonedRecord = JSON.parse(JSON.stringify(record));
      
      if (clonedRecord.options) {
        if (typeof clonedRecord.options === "string") {
          try { clonedRecord.options = JSON.parse(clonedRecord.options); } catch (e) { clonedRecord.options = []; }
        }
      } else {
        clonedRecord.options = [];
      }
      if (!Array.isArray(clonedRecord.options)) clonedRecord.options = [];
      
      clonedRecord.required = clonedRecord.required ? 1 : 0;
      clonedRecord.in_column = clonedRecord.in_column ? 1 : 0;
      
      this.modal.data = clonedRecord;
      this.selectedTemplate = null;
      this.modal.isOpen = true;
    },
    handleModalOk() {
      if (!this.$refs.modalFormRef) return;
      this.$refs.modalFormRef.validate().then(() => {
        this.modalLoading = true;
        if (this.modal.mode === "CREATE") { this.storeRecord(); } else { this.updateRecord(); }
      }).catch((err) => console.log("Validation error:", err));
    },
    handleModalCancel() {
      this.modal.isOpen = false;
      this.modal.data = this.getDefaultModalData();
    },
    storeRecord() {
      const data = { ...this.modal.data };
      if (this.optionTypes.includes(data.type) && data.options) data.options = JSON.stringify(data.options);
      this.$inertia.post(route("organizer.form.fields.store", { form: data.form_id }), data, {
        preserveState: true,
        onSuccess: () => {
          this.modalLoading = false;
          this.modal.isOpen = false;
          message.success(this.$t("field_created_success"));
          this.reloadFormFields();
        },
        onError: () => this.modalLoading = false
      });
    },
    updateRecord() {
      const data = { ...this.modal.data };
      if (this.optionTypes.includes(data.type) && data.options) data.options = JSON.stringify(data.options);
      this.$inertia.patch(route("organizer.form.fields.update", { form: data.form_id, field: data.id }), data, {
        preserveState: true,
        onSuccess: () => {
          this.modalLoading = false;
          this.modal.isOpen = false;
          message.success(this.$t("field_updated_success"));
          this.reloadFormFields();
        },
        onError: () => this.modalLoading = false
      });
    },
    deleteRecord(record) {
      this.$inertia.delete(route("organizer.form.fields.destroy", { form: this.form.id, field: record.id }), {
        preserveState: true,
        onSuccess: () => {
          message.success(this.$t("field_deleted_success"));
          this.reloadFormFields();
        }
      });
    },
    addOptionItem() {
      const newIndex = this.modal.data.options.length + 1;
      this.modal.data.options.push({ value: `option_${newIndex}`, label: `${this.$t("option")}_${newIndex}` });
    },
    removeOptionItem(index) { this.modal.data.options.splice(index, 1); },
    onChangeOptionTemplate(value) {
      if (!value) return;
      const template = this.templateOptions.find(t => t.value === value);
      if (template?.template) this.modal.data.options = JSON.parse(JSON.stringify(template.template));
    },
    onChangeType(value) {
      if (this.textAreaTypes.includes(value)) {
        if (typeof this.modal.data.options !== "number") this.modal.data.options = 5;
      } else if (this.optionTypes.includes(value)) {
        if (!this.modal.data.options?.length) {
          this.modal.data.options = [
            { value: "option_1", label: `${this.$t("option")}_1` },
            { value: "option_2", label: `${this.$t("option")}_2` },
          ];
        }
      } else {
        this.modal.data.options = null;
      }
    },
    onFieldLabelChanged() {
      if (!this.modal.data.field_name && this.modal.data.field_label) {
        this.modal.data.field_name = this.modal.data.field_label.toLowerCase().replace(/\s+/g, "_").replace(/[^a-z0-9_]/g, "");
      }
    },
    onRequiredChange(checked) { if (!checked) this.modal.data.in_column = 0; },
    
    reloadFormFields() {
      this.$inertia.reload({
        only: ["fields"],
        preserveState: true, // 核心：保留當前頁面的其餘響應式狀態，不洗掉 modal 狀態
        onSuccess: () => {
          if (this.isDraggable) this.initSortable();
        },
      });
    },
  },
};
</script>

<style scoped>
.drag-icon {
  font-size: 16px;
  color: #ccc;
  padding: 4px 8px;
}
.drag-icon.active-drag {
  cursor: move;
  color: #1890ff;
}
.options-list { display: flex; flex-direction: column; gap: 8px; width: 100%; }
.option-item { display: flex; align-items: center; gap: 8px; }

/* SortableJS 專用樣式 */
:deep(.draggable-row) {
  background-color: #fafafa;
}
:deep(.sortable-ghost) {
  opacity: 0.4;
  background-color: #e6f7ff !important;
  border: 1px dashed #40a9ff;
}
:deep(.sortable-chosen) {
  background-color: #f5f5f5 !important;
}
</style>