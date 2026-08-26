<template>
  <a-modal
    v-model:open="localVisible"
    :title="modalTitle"
    class="md:!w-[70%] !w-full"
    :footer="null"
    centered
    :styles="{ body: { padding: '24px' } }"
    wrap-class-name="organization-modal"
    @cancel="handleCancel"
  >
    <a-form
      ref="formRef"
      :model="formData"
      layout="horizontal"
      @finish="handleFinish"
      class="organization-form"
    >
      <!-- Basic Information Section -->
      <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">
          {{ $t('basic_information') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <a-form-item
            :label="$t('abbreviation')"
            name="abbr_en"
            :rules="[{ required: true, message: $t('abbreviation_required') }]"
          >
            <a-input
              v-model:value="formData.abbr_en"
              :placeholder="$t('enter_abbreviation')"
              allow-clear
            />
          </a-form-item>

          <a-form-item
            :label="$t('registration_code')"
            name="registration_code"
          >
            <a-input
              v-model:value="formData.registration_code"
              :placeholder="$t('enter_registration_code')"
              allow-clear
            />
          </a-form-item>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <a-form-item
            :label="$t('organization_name_zh')"
            name="name_zh"
            :rules="[{ required: true, message: $t('chinese_name_required') }]"
          >
            <a-input
              v-model:value="formData.name_zh"
              :placeholder="$t('enter_chinese_name')"
              allow-clear
            />
          </a-form-item>

          <a-form-item
            :label="$t('organization_name_en')"
            name="name_en"
          >
            <a-input
              v-model:value="formData.name_en"
              :placeholder="$t('enter_english_name')"
              allow-clear
            />
          </a-form-item>

          <a-form-item
            :label="$t('organization_name_pt')"
            name="name_pt"
          >
            <a-input
              v-model:value="formData.name_pt"
              :placeholder="$t('enter_portuguese_name')"
              allow-clear
            />
          </a-form-item>
        </div>
      </div>

      <!-- Contact Information Section -->
      <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">
          {{ $t('contact_information') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <a-form-item
            :label="$t('email')"
            name="email"
            :rules="[
              { required: true, message: $t('email_required') },
              { type: 'email', message: $t('invalid_email') }
            ]"
          >
            <a-input
              v-model:value="formData.email"
              type="email"
              :placeholder="$t('enter_email')"
              allow-clear
            >
              <template #prefix>
                <MailOutlined class="text-gray-400" />
              </template>
            </a-input>
          </a-form-item>

          <a-form-item
            :label="$t('phone')"
            name="phone"
          >
            <a-input
              v-model:value="formData.phone"
              :placeholder="$t('enter_phone')"
              allow-clear
            >
              <template #prefix>
                <PhoneOutlined class="text-gray-400" />
              </template>
            </a-input>
          </a-form-item>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <a-form-item
            :label="$t('address')"
            name="address"
          >
            <a-input
              v-model:value="formData.address"
              :placeholder="$t('enter_address')"
              allow-clear
            />
          </a-form-item>

          <a-form-item
            :label="$t('website')"
            name="href"
          >
            <a-input
              v-model:value="formData.href"
              :placeholder="$t('enter_website')"
              allow-clear
            >
              <template #prefix>
                <GlobalOutlined class="text-gray-400" />
              </template>
            </a-input>
          </a-form-item>
        </div>
      </div>

      <!-- Organization Settings -->
      <div class="mb-6">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">
          {{ $t('organization_settings') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <a-form-item
            :label="$t('avatar_url')"
            name="avatar"
          >
            <a-input
              v-model:value="formData.avatar"
              :placeholder="$t('enter_avatar_url')"
              allow-clear
            />
          </a-form-item>

          <a-form-item
            :label="$t('card_style')"
            name="card_style"
          >
            <a-select
              v-model:value="formData.card_style"
              :placeholder="$t('select_card_style')"
              allow-clear
            >
              <a-select-option
                v-for="style in cardStyles"
                :key="style.value"
                :value="style.value"
              >
                {{ style.label }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <a-form-item
            :label="$t('president')"
            name="president"
          >
            <a-input
              v-model:value="formData.president"
              :placeholder="$t('enter_president_name')"
              allow-clear
            />
          </a-form-item>
          <a-form-item
            :label="$t('manager')"
            name="manager"
          >
            <a-select
              v-model:value="formData.organizer_member_ids"
              mode="multiple"
              :placeholder="$t('select_managers')"
              allow-clear
              :options="availableManagerOptions"
              :fieldNames="{ value: 'id', label: 'given_name' }"
              class="w-full"
            />
          </a-form-item>
        </div>

        <div class="mt-4">
          <a-form-item
            :label="$t('content')"
            name="content"
            :wrapper-col="{ span: 24 }"
          >
            <a-textarea
              v-model:value="formData.content"
              :placeholder="$t('enter_content')"
              :rows="4"
              show-count
              :maxlength="500"
            />
          </a-form-item>
        </div>

        <div class="mt-4">
          <a-form-item
            :label="$t('status')"
            name="status"
          >
            <div class="flex items-center space-x-4">
              <a-switch
                v-model:checked="formData.status"
                :checked-value="1"
                :unchecked-value="0"
                :checked-children="$t('active')"
                :un-checked-children="$t('inactive')"
              />
            </div>
          </a-form-item>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6">
        <a-button @click="handleCancel">
          {{ $t('cancel') }}
        </a-button>
        <a-button
          type="primary"
          :loading="submitting"
          @click="submitForm"
          class="!bg-indigo-600 hover:!bg-indigo-700"
        >
          <template #icon>
            <SaveOutlined />
          </template>
          {{ isEdit ? $t('update_organization') : $t('create_organization') }}
        </a-button>
      </div>
    </a-form>
  </a-modal>
</template>

<script>
import { ref, watch, computed, toRaw, getCurrentInstance } from 'vue';
import { message } from 'ant-design-vue';
import {
  MailOutlined,
  PhoneOutlined,
  GlobalOutlined,
  SaveOutlined
} from '@ant-design/icons-vue';

export default {
  name: 'OrganizationModal',
  components: {
    MailOutlined,
    PhoneOutlined,
    GlobalOutlined,
    SaveOutlined
  },
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    organization: {
      type: Object,
      default: null
    },
    users: {
      type: Array,
      default: () => []
    },
    cardStyles: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:open', 'save'],
  setup(props, { emit }) {
    // Access the global $t function from the Vue instance
    const instance = getCurrentInstance();
    const t = instance.proxy.$t; // This is the laravel-vue-i18n $t

    const formRef = ref(null);
    const submitting = ref(false);
    const localVisible = ref(props.visible);

    const getDefaultFormData = () => ({
      id: null,
      abbr_en: '',
      registration_code: '',
      name_zh: '',
      name_en: '',
      name_pt: '',
      email: '',
      phone: '',
      address: '',
      href: '',
      avatar: '',
      card_style: null,
      president: '',
      organizer_member_ids: [],
      content: '',
      status: 1,
    });

    const formData = ref({ ...getDefaultFormData() });

    const isEdit = computed(() => !!formData.value.id);
    const modalTitle = computed(() =>
      isEdit.value ? t('edit_organization') : t('create_organization')
    );

    const availableManagerOptions = computed(() => {
      if (!props.users) return [];
      return props.users.filter(u => u.user_id != null);
    });

    watch(() => props.visible, (newVal) => {
      localVisible.value = newVal;
      if (newVal) {
        if (props.organization) {
          const orgCopy = { ...props.organization };
          if (orgCopy.organizer_member_ids === undefined) {
            orgCopy.organizer_member_ids = [];
          }
          formData.value = orgCopy;
        } else {
          formData.value = { ...getDefaultFormData() };
        }
      }
    });

    watch(() => localVisible.value, (newVal) => {
      if (!newVal) {
        emit('update:open', false);
      }
    });

    const handleCancel = () => {
      localVisible.value = false;
      emit('update:open', false);
    };

    const submitForm = () => {
      formRef.value?.$emit('finish');
    };

    const handleFinish = async () => {
      try {
        await formRef.value?.validate();
        submitting.value = true;
        const payload = toRaw(formData.value);
        emit('save', payload);
      } catch (error) {
        console.error('Validation failed:', error);
        if (error.errorFields) {
          message.error(t('please_fill_required_fields'));
        }
      }
    };

    watch(() => localVisible.value, (newVal) => {
      if (!newVal) {
        submitting.value = false;
      }
    });

    return {
      formRef,
      localVisible,
      formData,
      isEdit,
      modalTitle,
      availableManagerOptions,
      submitting,
      handleCancel,
      submitForm,
      handleFinish
    };
  }
};
</script>

<style scoped>
/* Optional custom styles */
</style>