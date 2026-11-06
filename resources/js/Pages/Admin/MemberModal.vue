<template>
  <a-modal
    :open="open"
    :title="$t(mode === 'EDIT' ? 'edit' : 'create')"
    width="60%"
    @cancel="$emit('update:open', false)"
  >
  
    <a-form
      id="admin-member-form"
      ref="formRef"
      :model="formData"
      name="member"
      :label-col="{ span: 3 }"
      :wrapper-col="{ span: 16 }"
      autocomplete="off"
      :rules="rules"
      :validate-messages="validateMessages"
      @finish="$emit('save', formData)"
    >
      <a-form-item :label="$t('organizations')" name="organization_id">
        <a-select
          v-model:value="formData.organization_id"
          show-search
          :filter-option="filterOption"
          :options="organizations"
          :fieldNames="{ value: 'id', label: 'name_zh' }"
        />
      </a-form-item>
      <a-form-item :label="$t('given_name')" name="given_name">
        <a-input type="input" v-model:value="formData.given_name" />
      </a-form-item>
      <a-form-item :label="$t('middle_name')" name="middle_name">
        <a-input type="input" v-model:value="formData.middle_name" />
      </a-form-item>
      <a-form-item :label="$t('family_name')" name="family_name">
        <a-input type="input" v-model:value="formData.family_name" />
      </a-form-item>
      <a-form-item :label="$t('display_name')" name="display_name">
        <a-input type="input" v-model:value="formData.display_name" />
      </a-form-item>
      <a-row :span="24">
        <a-col :span="18">
          <a-form-item :label="$t('email')" :label-col="{ span: 4 }" name="email">
            <a-input type="input" v-model:value="formData.email" />
          </a-form-item>
          <a-form-item :label="$t('gender')" :label-col="{ span: 4 }" name="gender">
            <a-radio-group v-model:value="formData.gender" button-style="solid">
              <a-radio-button value="M">{{ $t('male') }}</a-radio-button>
              <a-radio-button value="F">{{ $t('female') }}</a-radio-button>
            </a-radio-group>
          </a-form-item>
          <a-form-item :label="$t('dob')" :label-col="{ span: 4 }" name="dob">
            <a-date-picker v-model:value="formData.dob" :format="dateFormat" :valueFormat="dateFormat" />
          </a-form-item>
          <a-form-item :label="$t('valid_from')" :label-col="{ span: 4 }" name="valid_at">
            <a-date-picker v-model:value="formData.valid_at" :format="dateFormat" :valueFormat="dateFormat" />
          </a-form-item>
          <a-form-item :label="$t('valid_until')" :label-col="{ span: 4 }" name="expired_at">
            <a-date-picker v-model:value="formData.expired_at" :format="dateFormat" :valueFormat="dateFormat" />
          </a-form-item>
          <a-form-item :label="$t('is_organizer')" :label-col="{ span: 4 }" name="is_organizer">
            <a-switch v-model:checked="formData.is_organizer" />
          </a-form-item>
          <a-form-item :label="$t('is_dismissed')" :label-col="{ span: 4 }" name="is_dismissed">
            <a-switch v-model:checked="formData.is_dismissed" />
          </a-form-item>
          <a-form-item :label="$t('users')" :label-col="{ span: 4 }" name="user_id">
            <p v-if="formData.user">{{ formData.user.email }}</p>
            <a-select
              v-else
              v-model:value="formData.user_id"
              show-search
              option-filter-prop="email"
              style="width: 100%"
              :options="users"
              :fieldNames="{ value: 'id', label: 'email' }"
            />
          </a-form-item>
        </a-col>
        <a-col :span="6">
          <img :src="formData.avatar" width="200" />
        </a-col>
      </a-row>
    </a-form>
    <template #footer>
      <a-button type="primary" html-type="submit" form="admin-member-form" :loading="saving">
        {{ $t(mode === 'EDIT' ? 'update' : 'create') }}
      </a-button>
    </template>
  </a-modal>
</template>

<script>
export default {
  props: {
    open: { type: Boolean, default: false },
    member: { type: Object, default: () => ({}) },
    mode: { type: String, default: 'CREATE' },
    organizations: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    saving: { type: Boolean, default: false },
  },
  emits: ['update:open', 'save'],
  data() {
    return {
      formData: {},
      dateFormat: 'YYYY-MM-DD',
      validateMessages: {
        required: '${label} is required!',
        types: {
          email: '${label} is not a valid email!',
          number: '${label} is not a valid number!',
        },
        number: {
          range: '${label} must be between ${min} and ${max}',
        },
      },
    };
  },
  computed: {
    rules() {
      const message = this.$t('field_required');
      return {
        organization_id: { required: true, type: 'number', message },
        given_name: { required: true, whitespace: true, message },
        family_name: { required: true, whitespace: true, message },
        display_name: { required: true, whitespace: true, message },
        email: { required: true, whitespace: true, message },
      };
    },
  },
  watch: {
    open(isOpen) {
      if (isOpen) {
        this.formData = this.mode === 'CREATE'
          ? { gender: 'M', is_organizer: false, is_dismissed: false, ...this.member }
          : { ...this.member };
        this.$refs.formRef?.clearValidate();
      }
    },
  },
  methods: {
    filterOption(input, option) {
      return (option.name_zh || '').toLowerCase().includes(input.toLowerCase());
    },
  },
};
</script>
