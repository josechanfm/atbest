<template>
  <a-modal :open="open" :title="$t(title)" width="60%" @cancel="$emit('cancel')">
    <a-form id="organizer-member-form" ref="formRef" :model="formData" :label-col="{ span: 4 }" autocomplete="off"
      :rules="rules" :validate-messages="validateMessages" @finish="$emit('save', formData)">
      <a-form-item :label="$t('given_name')" name="given_name">
        <a-input type="input" v-model:value="formData.given_name" />
      </a-form-item>
      <a-row :span="24">
        <a-col :span="12">
          <a-form-item :label="$t('middle_name')" name="middle_name" :label-col="{ span: 8 }">
            <a-input type="input" v-model:value="formData.middle_name" />
          </a-form-item>
        </a-col>
        <a-col :span="12">
          <a-form-item :label="$t('family_name')" name="family_name" :label-col="{ span: 6 }">
            <a-input type="input" v-model:value="formData.family_name" />
          </a-form-item>
        </a-col>
      </a-row>
      <a-form-item :label="$t('display_name')" name="display_name">
        <a-input type="input" v-model:value="formData.display_name" />
      </a-form-item>
      <a-row :span="24">
        <a-col :span="12">
          <a-form-item :label="$t('gender')" name="gender" :label-col="{ span: 8 }">
            <a-radio-group v-model:value="formData.gender" button-style="solid">
              <a-radio-button value="M">{{ $t("male") }}</a-radio-button>
              <a-radio-button value="F">{{ $t("female") }}</a-radio-button>
            </a-radio-group>
          </a-form-item>
        </a-col>
        <a-col :span="12">
          <a-form-item :label="$t('dob')" name="dob" :label-col="{ span: 8 }">
            <a-date-picker v-model:value="formData.dob" :format="dateFormat" :valueFormat="dateFormat" />
          </a-form-item>
        </a-col>
      </a-row>
      <a-row :span="24">
        <a-col :span="12">
          <a-form-item :label="$t('email')" name="email" :label-col="{ span: 8 }"
            :wrapper-col="{ span: 14, offset: 0 }">
            <a-input type="input" v-model:value="formData.email" />
          </a-form-item>
        </a-col>
        <a-col :span="12">
          <a-form-item :label="$t('mobile_number')" name="mobile" :label-col="{ span: 8 }">
            <a-input type="input" v-model:value="formData.mobile" />
          </a-form-item>
        </a-col>
      </a-row>
      <a-row :span="24">
        <a-col :span="12">
          <a-form-item :label="$t('is_organizer')" name="is_organizer">
            <a-switch v-model:checked="formData.is_organizer" />
          </a-form-item>
          <a-form-item :label="$t('is_dismissed')" name="is_dismissed">
            <a-switch v-model:checked="formData.is_dismissed" />
          </a-form-item>
        </a-col>
          <a-col :span="12">
            <a-form-item :label="$t('avatar')" name="avatar" :label-col="{ span: 8 }">
              <img :src="formData.avatar" width="150" />
            </a-form-item>
          </a-col>
      </a-row>
    </a-form>
    <template #footer>
      <a-button key="back" @click="$emit('cancel')">返回</a-button>
      <a-button v-if="mode === 'EDIT'" key="Update" type="primary" html-type="submit" form="organizer-member-form"
        :loading="saving">
        {{ $t("update") }}
      </a-button>
      <a-button v-if="mode === 'CREATE'" key="Store" type="primary" html-type="submit" form="organizer-member-form"
        :loading="saving">
        {{ $t("add") }}
      </a-button>
    </template>
  </a-modal>
</template>

<script>
export default {
  props: {
    open: { type: Boolean, default: false },
    member: { type: Object, default: () => ({}) },
    title: { type: String, default: "create" },
    mode: { type: String, default: "CREATE" },
    saving: { type: Boolean, default: false },
  },
  emits: ["cancel", "save"],
  data() {
    return {
      formData: {},
      dateFormat: "YYYY-MM-DD",
      rules: {
        given_name: { required: true },
        family_name: { required: true },
        display_name: { required: true, whitespace: true },
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
    };
  },
  watch: {
    open(isOpen) {
      if (isOpen) {
        this.formData = this.mode === "CREATE"
          ? { gender: "M", is_organizer: false, is_dismissed: false, ...this.member }
          : { ...this.member };
        this.$refs.formRef?.clearValidate();
      }
    },
  },
};
</script>
