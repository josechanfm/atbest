<template>
  <a-modal
    v-model:open="visible"
    :title="$t('send_sms')"
    :confirm-loading="submitting"
    @ok="handleOk"
    @cancel="handleCancel"
    :ok-text="$t('send')"
    :cancel-text="$t('cancel')"
    width="600px"
  >
    <a-form
      ref="formRef"
      :model="form"
      :rules="rules"
      layout="vertical"
    >
      <a-form-item
        :label="$t('organization')"
        name="organization_id"
      >
        <a-select
          v-model:value="form.organization_id"
          :placeholder="$t('select_organization')"
          allow-clear
        >
          <a-select-option
            v-for="org in organizations"
            :key="org.id"
            :value="org.id"
          >
            {{ org.abbr }} - {{ org.name_zh }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item
        :label="$t('mobile')"
        name="mobile"
      >
        <a-input
          v-model:value="form.mobile"
          :placeholder="$t('enter_mobile')"
          allow-clear
        />
      </a-form-item>

      <a-form-item
        :label="$t('content')"
        name="content"
      >
        <a-textarea
          v-model:value="form.content"
          :placeholder="$t('enter_message_content')"
          :rows="4"
          show-count
          :maxlength="500"
        />
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script>
export default {
  name: 'SmsMessageModal',
  props: {
    organizations: {
      type: Array,
      required: true,
    },
  },
  emits: ['update:visible', 'saved'],
  data() {
    return {
      visible: false,
      submitting: false,
      form: {
        organization_id: null,
        mobile: '',
        content: '',
      },
      rules: {
        organization_id: [
          { required: true, message: this.$t('organization_required'), trigger: 'change' },
        ],
        mobile: [
          { required: true, message: this.$t('mobile_required'), trigger: 'blur' },
          { pattern: /^[0-9+\-() ]+$/, message: this.$t('invalid_mobile'), trigger: 'blur' },
        ],
        content: [
          { required: true, message: this.$t('content_required'), trigger: 'blur' },
          { max: 500, message: this.$t('content_too_long'), trigger: 'blur' },
        ],
      },
    };
  },
  methods: {
    open() {
      this.submitting = false,
      this.visible = true;
      this.resetForm();
    },
    close() {
      this.visible = false;
      this.submitting = false,
      this.$emit('update:visible', false);
    },
    resetForm() {
      this.form = {
        organization_id: null,
        mobile: '',
        content: '',
      };
      this.$refs.formRef?.resetFields();
    },
    async handleOk() {
      try {
        await this.$refs.formRef.validate();
        this.submitting = true;

        this.$inertia.post(route('admin.smsMessages.store'), this.form, {
          preserveState: false,
          onSuccess: () => {
            this.submitting = false;
            this.$message.success(this.$t('sms_sent_successfully'));
            this.close();
            this.$emit('saved');
          },
          onError: (errors) => {
            this.submitting = false;
            // Optionally display errors on fields
            if (errors) {
              this.$message.error(this.$t('sms_send_failed'));
            }
          },
        });
      } catch (error) {
        // Validation error
        console.warn('Validation failed', error);
      }
    },
    handleCancel() {
      this.close();
    },
  },
};
</script>