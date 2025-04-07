<template>
  <OrganizerLayout :title="$t('events')" :breadcrumb="breadcrumb">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Event</h2>
    </template>

    <div class="bg-white relative shadow rounded-lg p-5">
      <a-form
        :model="event"
        name="nest-messages"
        :validate-messages="validateMessages"
        layout="vertical"
        :rules="rules"
        @finish="onFinish"
        @finishFailed="onFinishFailed"
      >
        <a-form-item :label="$t('event_title')" name="title">
          <a-input v-model:value="event.title" />
        </a-form-item>
        <a-form-item :label="$t('type')" name="category_code">
          <a-select v-model:value="event.category_code" :options="categories" />
        </a-form-item>
        <a-row>
          <a-col :xs="24" :md="8">
            <a-form-item :label="$t('credit')" name="credit">
              <a-input-number v-model:value="event.credit" min="0" />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :md="8">
            <a-form-item :label="$t('start_date')" name="valid_at">
              <a-date-picker
                v-model:value="event.valid_at"
                :format="dateFormat"
                :valueFormat="dateFormat"
              />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :md="8">
            <a-form-item :label="$t('end_date')" name="expire_at">
              <a-date-picker
                v-model:value="event.expire_at"
                :format="dateFormat"
                :valueFormat="dateFormat"
              />
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item :label="$t('description')" name="content">

          <RichTextEditor v-model="event.content"/>
          <!-- <quill-editor v-model:value="event.content" style="min-height: 200px" /> -->
        </a-form-item>
        <a-form-item :label="$t('require_login')" name="require_login">
          <a-switch v-model:checked="event.require_login" @change="event.for_member = false" />
          <span class="pl-3">{{ $t("require_login_note") }}</span>
        </a-form-item>
        <a-form-item :label="$t('for_member')" name="for_member" v-if="event.require_login">
          <a-switch v-model:checked="event.for_member" />
          <span class="pl-3">{{ $t("for_member_note") }}</span>
        </a-form-item>
        <a-form-item :label="$t('published')" name="published">
          <a-switch v-model:checked="event.published" @change="event.with_attendance = false" />
          <span class="pl-3">{{ $t("published_note") }}</span><br />
          <span v-if="event.entries_count > 0">
            The form has responses, please backup before unpublished.</span>
        </a-form-item>
        <a-form-item :label="$t('with_attendance_note')" name="with_attendance">
          <a-switch v-model:checked="event.with_attendance"/>
        </a-form-item>
        <!-- Form images-->
        <a-form-item :label="$t('banner')" name="banner_image">
          <div class="flex gap-5">
              <div v-if="event.banner_url" class="flex">
                <img :src="event.banner_url" width="100" />
                <div class="flex flex-col justify-end">
                  <inertia-link :href="route('organizer.event.deleteImage',{event:event,collection:'banner'})" method="delete" class="text-red-500">
                    <DeleteOutlined />
                  </inertia-link>
                </div>
              </div>
              <a-upload
                v-model:file-list="event.banner_image"
                :multiple="false"
                :max-count="1"
                :accept="'image/*'"
                list-type="picture-card"
                @change="handleBannerUpload"
                >
                <!--before upload preview-->
                <div v-if="!event.banner_image">
                    <plus-outlined></plus-outlined>
                    <div class="ant-upload-text">Upload</div>
                </div>
              </a-upload>
          </div>
        </a-form-item>
        <a-form-item :label="$t('thumbnail')" name="thumb_image">
              <div class="flex gap-5">
                  <div v-if="event.thumb_url" class="flex">
                    <img :src="event.thumb_url" width="100" />
                    <div class="flex flex-col justify-end">
                      <inertia-link :href="route('organizer.event.deleteImage',{event:event,collection:'thumb'})" method="delete" class="text-red-500">
                        <DeleteOutlined />
                      </inertia-link>
                    </div>
                  </div>
                  <a-upload
                    v-model:file-list="event.thumb_image"
                    :multiple="false"
                    :max-count="1"
                    :accept="'image/*'"
                    list-type="picture-card"
                    @change="handleThumbUpload"
                    >
                    <!--before upload preview-->
                    <div v-if="!event.thumb_image">
                        <plus-outlined></plus-outlined>
                        <div class="ant-upload-text">Upload</div>
                    </div>
                  </a-upload>
              </div>
        </a-form-item>
        <a-form-item :label="$t('applications')" name="form_id">
          <a-select v-model:value="event.form_id" :options="forms" :fieldNames="{value:'id',label:'title'}"/>
        </a-form-item>
        <a-form-item :label="$t('remark')" name="remark">
          <RichTextEditor v-model="event.remark"/>
          <!-- <quill-editor v-model:value="event.remark" style="min-height: 200px" /> -->
        </a-form-item>
        <div class="flex flex-row item-center justify-center">
          <a-button type="primary" html-type="submit">{{ $t("submit") }}</a-button>
        </div>
      </a-form>
    </div>
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { message } from "ant-design-vue";
import dayjs from "dayjs";
import duration from "dayjs/plugin/duration";
import { PlusOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import RichTextEditor from '@/Components/RichTextEditor.vue'; // Adjust the path accordingly

dayjs.extend(duration);
export default {
  components: {
    OrganizerLayout,
    message,
    dayjs,
    PlusOutlined, DeleteOutlined,
    RichTextEditor

  },
  props: ["event", "categories","forms"],
  data() {
    return {
      breadcrumb: [
        { label: "活動列表", url:route('organizer.events.index')},
        { label: this.event.id?'活動修改':'活動新增', url: null }
      ],
      mode: null,
      dateFormat: "YYYY-MM-DD",
      rules: {
        title_en: { required: true },
        category_code: { required: true },
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
      virticalStyle: {
        display: "flex",
        height: "30px",
        lineHeight: "30px",
        marginLeft: "8px",
      },
    };
  },
  created() {
    
  },
  mounted() {},
  methods: {
    onFinish() {
      if (this.event.id === undefined) {
        this.$inertia.post(route("organizer.events.store"), this.event, {
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (err) => {
            console.log(err);
          },
        });
      } else {
        this.event._method = 'PATCH';
        this.$inertia.post(route("organizer.events.update", this.event.id), this.event, {
          onSuccess: (page) => {
            console.log(page);
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


  },
};
</script>
