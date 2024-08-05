<template>
  <OrganizationLayout :title="form.id ? '表格修改' : '表格新增'" :breadcrumb="breadcrumb">
    <div class="bg-white relative shadow  p-5 rounded-lg overflow-x-auto">
      <a-form ref="modalRef" :model="form" name="From" layout="vertical" autocomplete="off" :rules="rules"
        :validate-messages="validateMessages" @finish="onFinish" @finishFailed="onFinishFailed">
        <a-form-item :label="$t('form_name')" name="name">
          <a-input v-model:value="form.name" />
        </a-form-item>
        <a-form-item :label="$t('title')" name="title">
          <a-input v-model:value="form.title" />
        </a-form-item>
        <div class="text-right">
          <a @click="form.openWelcome = !form.openWelcome">{{ $t('form_welcome') }}</a>
        </div>
        <a-form-item :label="$t('form_welcome')" name="welcome" v-if="form.openWelcome">
          <quill-editor v-model:value="form.welcome" style="min-height: 200px" />
        </a-form-item>
        <a-form-item :label="$t('description')" name="description">
          <quill-editor v-model:value="form.description" style="min-height: 200px" />
        </a-form-item>
        <div class="text-right">
          <a @click="form.openThanks = !form.openThanks">{{ $t('form_thankyou') }}</a>
        </div>
        <a-form-item :label="$t('form_thankyou')" name="thanks" v-if="form.openThanks">
          <quill-editor v-model:value="form.thanks" style="min-height: 200px" />
        </a-form-item>
        <a-form-item :label="$t('valid_at')" name="valid_at">
          <a-date-picker
            v-model:value="form.valid_at"
            :format="dateFormat"
            :valueFormat="dateFormat"
          />
        </a-form-item>
        <a-form-item :label="$t('expired_at')" name="expired_at">
          <a-date-picker v-model:value="form.expire_at" :valueFormat="dateFormat" :format="dateFormat"/>
        </a-form-item>
        <a-form-item :label="$t('require_login')" name="require_login">
          <a-switch v-model:checked="form.require_login" @change="form.for_member = false" />
          <span class="pl-3">{{ $t("require_login_note") }}</span>
        </a-form-item>
        <a-form-item :label="$t('for_member')" name="for_member" v-if="form.require_login">
          <a-switch v-model:checked="form.for_member"/>
          <span class="pl-3">{{ $t("for_member_note") }}</span>
        </a-form-item>
        <a-form-item :label="$t('published')" name="published">
          <a-switch v-model:checked="form.published" @change="form.with_attendance = false" :disabled="form.entries_count > 0" />
          <span class="pl-3">{{ $t("published_note") }}</span><br />
          <span v-if="form.entries_count > 0">
            The form has responses, please backup before unpublished.</span>
        </a-form-item>
        <a-form-item :label="$t('with_attendance')" name="with_attendance" v-if="form.published">
          <a-switch v-model:checked="form.with_attendance"/>
          <span class="pl-3">{{ $t("with_attendance_note") }}</span>
        </a-form-item>
        <!-- Thumbnail-->
        <a-form-item :label="$t('thumbnail')">
          <template v-if="form.thumbnail">
            <img :src="form.thumbnail" width="300px" />
            <a @click="onDeleteImage(form)">{{ $t('delete_photo') }}</a>
          </template>
          <template v-else>
            <template v-if="previewImage">
              <img :src="previewImage" class="w-64" />
              <a @click="onRemoveImage">{{ $t('remove_photo') }}</a>
            </template>
            <template v-else>
              <div class="flex items-center justify-center w-64">
                <label for="dropzone-file"
                  class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                  <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                      xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                    </svg>
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                      <div v-html="$t('upload_drag_drop')" />
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('image_size_note') }}</p>
                  </div>
                  <input id="dropzone-file" type="file" @change="onSelectFile" accept="image/png, image/gif, image/jpeg"
                    style="display:none" />
                </label>
              </div>
            </template>
          </template>
        </a-form-item>
        <a-form-item :wrapper-col="{ offset: 12, span: 10 }">
          <a-button type="primary" html-type="submit">{{ $t('submit') }}</a-button>
        </a-form-item>
      </a-form>
      <div v-if="form.id">
        <a :href="route('forms.show', { form: form, t: form.uuid })" target="_blank" ref="formUrl">
          {{ route('forms.show', {form: form,t: form.uuid}) }}</a>
        <a-button @click="copyUrl">{{ $t('copy_to_clipboard') }}</a-button>
      </div>
    </div>

  </OrganizationLayout>
</template>

<script>
import OrganizationLayout from "@/Layouts/OrganizationLayout.vue";
import { quillEditor, Quill } from "vue3-quill";
import { message } from "ant-design-vue";

export default {
  components: {
    OrganizationLayout,
    quillEditor,
    message,
  },
  props: ["form"],
  data() {
    return {
      breadcrumb: [
        { label: "表格列表", url: route('manage.forms.index') },
        { label: this.form.id ? '表格修改' : '表格新增', url: null }
      ],
      dateFormat: "YYYY-MM-DD",
      loading: false,
      previewImage: null,
      selectedMedia: null,
      imageUrl: null,
      rules: {
        name: { required: true },
        title: { required: true },
        valid_at: { required: true },
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
  },
  mounted() {
  },
  methods: {
    onSelectFile(event) {
      const file = event.target.files[0]

      if (file.size > 1024 * 1024 * 1) {
        alert('oversize')
        return false
      }
      this.form.thumbnail_upload = file
      this.previewImage = URL.createObjectURL(file)

    },
    onRemoveImage() {
      this.form.thumbial_upload = null
      this.previewImage = null
    },
    onDeleteImage(form) {
      this.$inertia.post(route('manage.form.deleteImage', this.form), {
        onSuccess: (page) => {
          console.log(page)
        },
        onError: (err) => {
          console.log(err);
        },
      })

    },
    uploader(editor) {
      editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
        return new UploadAdapter(loader);
      };
    },
    onFinish(event) {
      if (this.form.id) {
        this.form._method = 'PATCH';
        this.$inertia.post(route("manage.forms.update", this.form.id), this.form, {
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (error) => {
            console.log(error);
          },
        });
      } else {
        this.$inertia.post(route("manage.forms.store"), this.form, {
          onSuccess: (page) => {
            // this.modal.data = {};
            // this.modal.isOpen = false;
          },
          onError: (err) => {
            console.log(err);
          },
        });
      }
    },
    onFinishFailed({ values, errorFields, outOfDate }) {
      message.error("Some required fields are missing!");
    },
    copyUrl() {
      navigator.clipboard.writeText(this.$refs.formUrl.href)
    },

  },
};
</script>
