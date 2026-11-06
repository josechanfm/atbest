<template>
  <AdminLayout :title="formTitle">
    <div class="mx-auto rounded-lg bg-white p-6 shadow-sm">
    <h2 class="mb-6 text-2xl font-bold text-gray-900">{{ formTitle }}</h2>
    <a-form
      ref="formRef"
      :model="formData"
      layout="vertical"
      @finish="handleFinish"
      class="organization-form"
    >
      <!-- Basic Information Section -->
      <section class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:p-5" aria-labelledby="basic-information-heading">
        <h3 id="basic-information-heading" class="mb-4 text-base font-semibold text-gray-900">
          {{ $t('basic_information') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
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

        <div class="mt-2 grid grid-cols-1 gap-y-2">
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
      </section>

      <!-- Contact Information Section -->
      <section class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:p-5" aria-labelledby="contact-information-heading">
        <h3 id="contact-information-heading" class="mb-4 text-base font-semibold text-gray-900">
          {{ $t('contact_information') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
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

        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
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
      </section>

      <section class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:p-5" aria-labelledby="organization-logo-heading">
        <h3 id="organization-logo-heading" class="mb-4 text-base font-semibold text-gray-900">
          {{ $t('organization_main_logo') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
          <a-form-item
            :label="$t('organization_main_logo')"
            name="avatar"
            :validate-status="formData.errors.avatar_upload ? 'error' : undefined"
            :help="formData.errors.avatar_upload"
          >
            <a-input
              v-model:value="formData.avatar"
              :placeholder="$t('enter_avatar_url')"
              allow-clear
              @change="clearUpload('avatar')"
            />
            <img v-if="imagePreview('avatar')" :src="imagePreview('avatar')" class="mt-2 h-20 max-w-full object-contain" :alt="$t('organization_main_logo')" />
            <input type="file" accept=".jpg,.jpeg,.png,image/png,image/jpeg" class="mt-2 block w-full" :aria-label="$t('organization_main_logo')" @change="selectImage('avatar', $event)" />
            <div class="text-xs text-gray-500">{{ $t('card_style_image_restrictions') }}</div>
            <a-button v-if="imagePreview('avatar')" class="mt-2" @click="removeImage('avatar')">{{ $t('delete') }}</a-button>
          </a-form-item>
        </div>
      </section>

      <section class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:p-5" aria-labelledby="card-style-heading">
        <h3 id="card-style-heading" class="mb-4 text-base font-semibold text-gray-900">
          {{ $t('card_style') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
          <a-form-item
            :label="$t('card_style')"
          >
            <a-select
              :value="selectedCardStyle"
              :placeholder="$t('select_card_style')"
              allow-clear
              @change="selectCardStyle"
            >
              <a-select-option
                v-for="style in cardStyleOptions"
                :key="style.value"
                :value="style.value"
              >
                {{ style.label }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <div v-if="formData.card_style && typeof formData.card_style === 'object'" class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
          <a-form-item :label="$t('card_style_logo')" :validate-status="formData.errors.card_style_logo_upload ? 'error' : undefined" :help="formData.errors.card_style_logo_upload">
            <a-input v-model:value="formData.card_style.logo" allow-clear @change="clearUpload('logo')" />
            <img v-if="imagePreview('logo')" :src="imagePreview('logo')" class="mt-2 h-20 max-w-full object-contain" :alt="$t('card_style_logo')" />
            <input type="file" accept=".jpg,.jpeg,.png,image/png,image/jpeg" class="mt-2 block w-full" :aria-label="$t('card_style_logo')" @change="selectImage('logo', $event)" />
            <div class="text-xs text-gray-500">{{ $t('card_style_image_restrictions') }}</div>
            <a-button v-if="imagePreview('logo')" class="mt-2" @click="removeImage('logo')">{{ $t('delete') }}</a-button>
          </a-form-item>
          <a-form-item :label="$t('card_style_background_image')" :validate-status="formData.errors.card_style_bg_image_upload ? 'error' : undefined" :help="formData.errors.card_style_bg_image_upload">
            <a-input v-model:value="formData.card_style.bg_image" allow-clear @change="clearUpload('bg_image')" />
            <img v-if="imagePreview('bg_image')" :src="imagePreview('bg_image')" class="mt-2 h-20 max-w-full object-contain" :alt="$t('card_style_background_image')" />
            <input type="file" accept=".jpg,.jpeg,.png,image/png,image/jpeg" class="mt-2 block w-full" :aria-label="$t('card_style_background_image')" @change="selectImage('bg_image', $event)" />
            <div class="text-xs text-gray-500">{{ $t('card_style_image_restrictions') }}</div>
            <a-button v-if="imagePreview('bg_image')" class="mt-2" @click="removeImage('bg_image')">{{ $t('delete') }}</a-button>
          </a-form-item>
          <a-form-item :label="$t('card_style_background_color')">
            <a-input v-model:value="formData.card_style.bg_color" allow-clear />
          </a-form-item>
          <a-form-item :label="$t('card_style_font_color')">
            <a-input v-model:value="formData.card_style.font_color" allow-clear />
          </a-form-item>
          <a-form-item :label="$t('card_style_font_style')">
            <a-input v-model:value="formData.card_style.font_style" allow-clear />
          </a-form-item>
        </div>
      </section>

      <section class="mb-5 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:p-5" aria-labelledby="organization-settings-heading">
        <h3 id="organization-settings-heading" class="mb-4 text-base font-semibold text-gray-900">
          {{ $t('organization_settings') }}
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
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
            name="organizer_member_ids"
          >
            <a-select
              v-model:value="formData.organizer_member_ids"
              mode="multiple"
              :placeholder="$t('select_managers')"
              allow-clear
              :options="availableManagerOptions"
              :disabled="!isEdit"
              class="w-full"
            />
          </a-form-item>
        </div>

        <div class="mt-2">
          <a-form-item
            :label="$t('content')"
            name="content"
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

        <div class="mt-2">
          <a-form-item
            :label="$t('status')"
            name="status"
          >
            <div class="flex items-center space-x-4">
              <a-switch
                v-model:checked="formData.status"
                :checked-value="true"
                :unchecked-value="false"
                :checked-children="$t('active')"
                :un-checked-children="$t('inactive')"
              />
            </div>
          </a-form-item>
        </div>
      </section>

      <!-- Form Actions -->
      <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4">
        <a-button @click="handleCancel">
          {{ $t('cancel') }}
        </a-button>
        <a-button
          type="primary"
          :loading="formData.processing"
          html-type="submit"
          class="!bg-indigo-600 hover:!bg-indigo-700"
        >
          <template #icon>
            <SaveOutlined />
          </template>
          {{ isEdit ? $t('update_organization') : $t('create_organization') }}
        </a-button>
      </div>
    </a-form>
    </div>
  </AdminLayout>
</template>

<script>
import { ref, reactive, computed, getCurrentInstance, onBeforeUnmount } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { message } from 'ant-design-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  MailOutlined,
  PhoneOutlined,
  GlobalOutlined,
  SaveOutlined
} from '@ant-design/icons-vue';

export default {
  name: 'OrganizationForm',
  components: {
    AdminLayout,
    MailOutlined,
    PhoneOutlined,
    GlobalOutlined,
    SaveOutlined
  },
  props: {
    organization: {
      type: Object,
      default: null
    },
    cardStyles: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const instance = getCurrentInstance();
    const t = instance.proxy.$t;

    const formRef = ref(null);
    const organization = props.organization;
    const formData = useForm({
      abbr_en: organization?.abbr_en ?? '',
      registration_code: organization?.registration_code ?? '',
      name_zh: organization?.name_zh ?? '',
      name_en: organization?.name_en ?? '',
      name_pt: organization?.name_pt ?? '',
      email: organization?.email ?? '',
      phone: organization?.phone ?? '',
      address: organization?.address ?? '',
      href: organization?.href ?? '',
      avatar: organization?.avatar ?? '',
      avatar_upload: null,
      card_style: organization?.card_style && typeof organization.card_style === 'object'
        ? { ...organization.card_style }
        : organization?.card_style ?? null,
      card_style_logo_upload: null,
      card_style_bg_image_upload: null,
      president: organization?.president ?? '',
      organizer_member_ids: [...(organization?.organizer_member_ids || [])],
      content: organization?.content ?? '',
      status: organization ? !!organization.status : true,
    });

    const previews = reactive({ avatar: '', logo: '', bg_image: '' });
    const uploadFields = { avatar: 'avatar_upload', logo: 'card_style_logo_upload', bg_image: 'card_style_bg_image_upload' };
    const clearUpload = (field) => {
      if (previews[field]) URL.revokeObjectURL(previews[field]);
      previews[field] = '';
      formData[uploadFields[field]] = null;
      formData.clearErrors(uploadFields[field]);
    };
    const selectImage = (field, event) => {
      const file = event.target.files?.[0];
      event.target.value = '';
      if (!file) return;
      if (!/\.(jpe?g|png)$/i.test(file.name) || !['image/jpeg', 'image/png'].includes(file.type) || file.size > 3 * 1024 * 1024) {
        message.error(t('card_style_image_restrictions'));
        return;
      }
      clearUpload(field);
      formData[uploadFields[field]] = file;
      previews[field] = URL.createObjectURL(file);
    };
    const removeImage = (field) => {
      clearUpload(field);
      if (field === 'avatar') formData.avatar = '';
      else formData.card_style[field] = '';
    };
    const imagePreview = (field) => {
      if (previews[field]) return previews[field];
      const value = field === 'avatar' ? formData.avatar : formData.card_style?.[field];
      if (!value) return '';
      return field === 'bg_image' && !value.includes('/') && !value.includes(':')
        ? `/storage/images/${value}` : value;
    };
    onBeforeUnmount(() => {
      clearUpload('avatar');
      clearUpload('logo');
      clearUpload('bg_image');
    });

    const cardStyleOptions = computed(() => Object.entries(props.cardStyles).map(([value, style]) => ({
      value, label: style.name, style,
    })));
    const selectedCardStyle = computed(() => {
      const style = formData.card_style;
      return typeof style === 'string' ? style : style?.preset;
    });

    const selectCardStyle = (value) => {
      if (!value) {
        clearUpload('logo');
        clearUpload('bg_image');
        formData.card_style = null;
        return;
      }
      const preset = cardStyleOptions.value.find(style => style.value === value);
      if (!preset) {
        console.error('Unknown card style preset:', value);
        message.error(t('submit_failed'));
        return;
      }
      clearUpload('logo');
      clearUpload('bg_image');
      formData.card_style = { ...preset.style, preset: value };
    };

    if (typeof formData.card_style === 'string') {
      const preset = cardStyleOptions.value.find(item => item.value === formData.card_style);
      if (preset) formData.card_style = { ...preset.style, preset: preset.value };
    }

    const isEdit = computed(() => !!props.organization?.id);
    const formTitle = computed(() =>
      isEdit.value ? t('edit_organization') : t('create_organization')
    );

    const availableManagerOptions = computed(() => {
      return (props.organization?.members || [])
        .map(member => ({
          value: member.id,
          label: member.display_name || member.given_name || member.user?.name || String(member.id)
        }));
    });

    const handleCancel = () => {
      router.get(route('admin.organizations.index'));
    };

    const handleFinish = () => {
      const options = {
        onSuccess: () => message.success(t('submit_success')),
        onError: (errors) => {
          console.error('Organization save failed:', errors);
          message.error(Object.values(errors)[0] || t('submit_failed'));
        },
      };
      if (isEdit.value) {
        formData.transform(data => ({ ...data, _method: 'PATCH', organizer_member_ids_present: true }))
          .post(route('admin.organizations.update', props.organization.id), { ...options, forceFormData: true });
      } else {
        formData.post(route('admin.organizations.store'), { ...options, forceFormData: true });
      }
    };

    return {
      formRef,
      formData,
      isEdit,
      formTitle,
      cardStyleOptions,
      selectedCardStyle,
      selectCardStyle,
      imagePreview,
      selectImage,
      removeImage,
      clearUpload,
      availableManagerOptions,
      handleCancel,
      handleFinish
    };
  }
};
</script>

<style scoped>
.organization-form :deep(.ant-form-item) {
  margin-bottom: 0;
}

.organization-form :deep(.ant-form-item-label) {
  padding-bottom: 2px;
}
</style>