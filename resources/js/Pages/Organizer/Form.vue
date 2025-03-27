<template>
  <OrganizerLayout :title="form.id ? '表格修改' : '表格新增'" :breadcrumb="breadcrumb">
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
        <a-form-item :label="$t('content')" name="content">
          <quill-editor v-model:value="form.content" style="min-height: 200px" />
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

        <!-- Form images-->
        <a-form-item :label="$t('banner')" name="banner_image">
              <div class="flex gap-5">
                  <div v-if="form.banner_url" class="flex">
                    <img :src="form.banner_url" width="100" />
                    <div class="flex flex-col justify-end">
                      <inertia-link :href="route('organizer.form.deleteImage',{form:form,collection:'banner'})" method="delete" class="text-red-500">
                        <DeleteOutlined />
                      </inertia-link>
                    </div>
                  </div>
                  <a-upload
                    v-model:file-list="form.banner_image"
                    :multiple="false"
                    :max-count="1"
                    :accept="'image/*'"
                    list-type="picture-card"
                    @change="handleBannerUpload"
                    >
                    <!--before upload preview-->
                    <div v-if="!form.banner_image">
                        <plus-outlined></plus-outlined>
                        <div class="ant-upload-text">Upload</div>
                    </div>
                  </a-upload>
              </div>
            </a-form-item>

            <a-form-item :label="$t('thumbnail')" name="thumb_image">
              <div class="flex gap-5">
                  <div v-if="form.thumb_url" class="flex">
                    <img :src="form.thumb_url" width="100" />
                    <div class="flex flex-col justify-end">
                      <inertia-link :href="route('organizer.form.deleteImage',{form:form,collection:'thumb'})" method="delete" class="text-red-500">
                        <DeleteOutlined />
                      </inertia-link>
                    </div>
                  </div>
                  <a-upload
                    v-model:file-list="form.thumb_image"
                    :multiple="false"
                    :max-count="1"
                    :accept="'image/*'"
                    list-type="picture-card"
                    @change="handleThumbUpload"
                    >
                    <!--before upload preview-->
                    <div v-if="!form.thumb_image">
                        <plus-outlined></plus-outlined>
                        <div class="ant-upload-text">Upload</div>
                    </div>
                  </a-upload>
              </div>
            </a-form-item>





        <a-form-item :wrapper-col="{ offset: 12, span: 10 }">
          <a-button type="primary" html-type="submit">{{ $t('submit') }}</a-button>
        </a-form-item>
      </a-form>
      <div v-if="form.id">
        <a :href="route('form.item', { t: form.uuid })" target="_blank" ref="formUrl">
          {{ route('form.item', {t: form.uuid}) }}</a>
        <a-button @click="copyUrl">{{ $t('copy_to_clipboard') }}</a-button>
      </div>
    </div>

  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { quillEditor, Quill } from "vue3-quill";
import { message } from "ant-design-vue";
import { PlusOutlined,DeleteOutlined } from "@ant-design/icons-vue";

export default {
  components: {
    OrganizerLayout,
    quillEditor,
    message,
    PlusOutlined, DeleteOutlined
  },
  props: ["form"],
  data() {
    return {
      breadcrumb: [
        { label: "表格列表", url: route('organizer.forms.index') },
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
    onDeleteImage(form) {
      this.$inertia.post(route('organizer.form.deleteImage', this.form), {
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


    checkFileSize(file) {
      const isLessThan200KB = file.size / 1024 / 1024 < 2;
      if (!isLessThan200KB) {
        this.$message.error('Image must be smaller than 200KB!');
        return false;
      }
      return true;
    },
    handleBannerUpload(info) {
      if(!this.checkFileSize(info.file)){
        this.form.banner_image = null;
        return false
      }
      if (info.file.status === 'uploading') {
        this.loading = true;
      }
      if (info.file.status === 'done' ) {
        // Reset the form.banner_image to only include the valid file
        this.form.banner_image = [info.file.originFileObj];
        this.loading = false;
      }
    },

    handleThumbUpload(info) {
      if(!this.checkFileSize(info.file)){
        this.form.thumb_image = null;
        return false
      }
      if (info.file.status === 'uploading') {
        this.loading = true;
      }
      if (info.file.status === 'done' ) {
        // Reset the form.banner_image to only include the valid file
        this.form.thumb_image = [info.file.originFileObj];
        this.loading = false;
      }
    }, 


    copyUrl() {
      navigator.clipboard.writeText(this.$refs.formUrl.href)
    },




  },
};
</script>
