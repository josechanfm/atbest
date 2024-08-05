<template>
  <OrganizationLayout :title="$t('events')" :breadcrumb="breadcrumb">
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
            <a-form-item :label="$t('start_date')" name="start_date">
              <a-date-picker
                v-model:value="event.start_date"
                :format="dateFormat"
                :valueFormat="dateFormat"
              />
            </a-form-item>
          </a-col>
          <a-col :xs="24" :md="8">
            <a-form-item :label="$t('end_date')" name="end_date">
              <a-date-picker
                v-model:value="event.end_date"
                :format="dateFormat"
                :valueFormat="dateFormat"
              />
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item :label="$t('description')" name="description">
          <quill-editor v-model:value="event.description" style="min-height: 200px" />
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

        <a-form-item :label="$t('remark')" name="remark">
          <quill-editor v-model:value="event.remark" style="min-height: 200px" />
        </a-form-item>
        <div class="flex flex-row item-center justify-center">
          <a-button type="primary" html-type="submit">{{ $t("submit") }}</a-button>
        </div>
      </a-form>
    </div>
  </OrganizationLayout>
</template>

<script>
import OrganizationLayout from "@/Layouts/OrganizationLayout.vue";
import { quillEditor } from "vue3-quill";
import { message } from "ant-design-vue";
import dayjs from "dayjs";
import duration from "dayjs/plugin/duration";
dayjs.extend(duration);
export default {
  components: {
    OrganizationLayout,
    quillEditor,
    message,
    dayjs,
  },
  props: ["event", "categories"],
  data() {
    return {
      breadcrumb: [
        { label: "活動列表", url:route('manage.events.index')},
        { label: this.event.id?'活動修改':'活動新增', url: null }
      ],
      mode: null,
      dateFormat: "YYYY-MM-DD",
      rules: {
        title_en: { required: true },
        category_code: { required: true },
        start_date: { required: true },
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
    console.log(this.event);
  },
  mounted() {},
  methods: {
    onFinish() {
      console.log(this.event);
      if (this.event.id === undefined) {
        this.$inertia.post(route("manage.events.store"), this.event, {
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (err) => {
            console.log(err);
          },
        });
      } else {
        this.$inertia.patch(route("manage.events.update", this.event.id), this.event, {
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
  },
};
</script>
