<template>
  <DefaultLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{$t('form_registration')}}
      </h2>
    </template>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
      <div
        class="mt-8 p-4 bg-white overflow-hidden shadow sm:rounded-lg"
      >
        <div class="text-center">
          <a-typography-title :level="3">{{ form.title }}</a-typography-title>
        </div>
        <div id="pure-html">
          <div v-html="form.content" />
        </div>
        <a-form
          :model="formData"
          ref="formRef"
          name="default"
          layout="vertical"
          :validate-messages="validateMessages"
        >
          <template v-for="field in form.fields" :key="field.id">

            <div v-if="form.require_member">
              <a-form-item
                label="Member Id"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-input type="input" v-model:value="$page.props.user.id" />
              </a-form-item>
            </div>

            <div v-if="field.type == 'input'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-input type="input" v-model:value="formData[field.id]" />
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'number'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-input-number type="input" v-model:value="formData[field.id]" />
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'textarea'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-textarea v-model:value="formData[field.id]" :rows="field?parseInt(field.options):3"/>
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'richtext'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <!-- <quill-editor
                  v-model:value="formData[field.id]"
                  style="min-height: 200px"
                /> -->
              </a-form-item>
              <div class="text-gray-500">{{ field.remark }}</div>
            </div>
            <div v-else-if="field.type == 'radio'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-radio-group v-model:value="formData[field.id]">
                  <a-radio
                    v-for="option in field.options"
                    :key="option.id"
                    :style="field.direction == 'H' ? '' : verticalStyle"
                    :value="option.value"
                    >{{ option.label }}</a-radio
                  >
                </a-radio-group>
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'checkbox'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-checkbox-group v-model:value="formData[field.id]">
                  <a-checkbox
                    v-for="option in field.options"
                    :key="option.id"
                    :style="field.direction == 'H' ? '' : verticalStyle"
                    :value="option.value"
                    >{{ option.label }}</a-checkbox
                  >
                </a-checkbox-group>
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'dropdown'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-select
                  v-model:value="formData[field.id]"
                  :options="field.options"
                ></a-select>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'true_false'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-switch v-model:checked="formData[field.id]" />
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'date'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-date-picker
                  v-model:value="formData[field.id]"
                  :format="dateFormat"
                  :valueFormat="dateFormat"
                />
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'datetime'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }]"
              >
                <a-date-picker
                  v-model:value="formData[field.id]"
                  show-time
                  :format="dateTimeFormat"
                  :valueFormat="dateTimeFormat"
                />
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'email'">
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }, { type: 'email' }]"
              >
                <a-input type="input" v-model:value="formData[field.id]" />
                <div class="text-gray-500">{{ field.remark }}</div>
              </a-form-item>
            </div>
            <div v-else-if="field.type == 'photo'">
              <a-button @click="showCropModal = true">{{
                $t("upload_profile_image")
              }}</a-button>
              <CropperModal
                v-if="showCropModal"
                :minAspectRatioProp="{ width: 8, height: 8 }"
                :maxAspectRatioProp="{ width: 8, height: 8 }"
                @croppedImageData="setCroppedImageData"
                @showModal="showCropModal = false"
              />
              <div class="flex flex-wrap mt-4 mb-6">
                <div class="w-full md:w-1/2 px-3">
                  <div v-if="avatarPreview !== null">
                    <img :src="avatarPreview" />
                  </div>
                  <div v-else>
                    <img :src="formData[field.id]" />
                  </div>
                </div>
              </div>
            </div>
            <div v-else>
              <a-form-item
                :label="field.field_label"
                :name="field.id"
                :rules="[{ required: field.required }, { type: 'email' }]"
              >
                <p>Data type undefined</p>
              </a-form-item>
            </div>

          </template>
          <div class="text-center pb-10">
            <a-button @click="storeRecord" type="primary">{{ $t("submit") }}</a-button>
          </div>
        </a-form>
      </div>
    </div>
  </DefaultLayout>
</template>

<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import DefaultLayout from "@/Layouts/DefaultLayout.vue";
import CropperModal from "@/Components/Member/CropperModal.vue";
// import { quillEditor } from "vue3-quill";

export default {
  components: {
    MemberLayout,
    DefaultLayout,
    // quillEditor,
    CropperModal,
  },
  props: ["form"],
  data() {
    return {
      formData: {},
      avatarPreview: null,
      showCropModal: "",
      richText: "<p>Jose</p>",
      dateFormat: "YYYY-MM-DD",
      dateTimeFormat: "YYYY-MM-DD HH:mm",
      columns: [
        {
          title: "Name",
          dataIndex: "name",
        },
        {
          title: "Title",
          dataIndex: "title",
        },
        {
          title: "With Login",
          dataIndex: "with_login",
        },
        {
          title: "With Member",
          dataIndex: "with_member",
        },
        {
          title: "Action",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        field: { required: true },
        label: { required: true },
      },
      validateMessages: {
        required: "必填欄位 ",
        types: {
          email: "不是有效電郵",
          number: "不是數字格式",
        },
        number: {
          range: "必須介於 ${min} 至 ${max}",
        },
      },
      labelCol: {
        style: {
          width: "150px",
        },
      },
      verticalStyle: {
        display: "flex",
        height: "30px",
        lineHeight: "30px",
        width: "100%",
        marginLeft: "20px",
      },
    };
  },
  created() {},
  methods: {
    setCroppedImageData(data) {
      this.avatarPreview = data.imageUrl;
      this.formData[this.form.fields.find((x) => x.type == "photo").id] = data;
      //console.log(data);
    },
    onFormChange() {
      console.log(this.formData);
    },
    storeRecord() {
      this.$refs.formRef
        .validateFields()
        .then(() => {
          this.$inertia.post(
            route("forms.store"),
            {
              form: this.form,
              fields: this.formData,
            },
            {
              onSuccess: (page) => {
                this.formData = {};
              },
              onError: (err) => {
                console.log(err);
              },
            }
          );
        })
        .catch((err) => {
          console.log(err);
        });
    },
  },
};
</script>

<style>
.ant-form-vertical .ant-form-item-label {
  padding: 0px !important;
}

/* #pure-html {
  all: initial;
}
#pure-html * {
  all: revert;
}
 */
/* Make the container scrollable if content overflows */
#pure-html {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch; /* smooth scrolling on iOS */
  word-wrap: break-word;
}

/* Scale images to fit */
#pure-html img {
  max-width: 100%;
  height: auto;
  display: block; /* avoid extra spacing */
}

/* Responsive tables: allow horizontal scroll on small screens */
#pure-html table {
  display: block;
  overflow-x: auto;
  white-space: nowrap; /* prevent wrapping; adjust if needed */
  max-width: 100%;
}

/* If you want tables to wrap instead of scroll, remove white-space: nowrap */
/* but scroll is often safer for complex tables */

/* Ensure iframes/videos are responsive */
#pure-html iframe,
#pure-html video {
  max-width: 100%;
  height: auto;
}

/* Prevent long text from breaking layout */
#pure-html p,
#pure-html div,
#pure-html span {
  word-wrap: break-word;
  overflow-wrap: break-word;
}

</style>
