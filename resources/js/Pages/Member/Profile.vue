<template>
  <MemberLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("profile") }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
          <a-form
            ref="formRef"
            name="Form"
            autocomplete="off"
            v-bind="layout"
            :model="member"
            layout="horizontal"
            :rules="rules"
            :validate-messages="validateMessages"
            enctype="multipart/form-data"
          >
            <a-collapse v-model:activeKey="activeKey">
              <a-collapse-panel key="1" :header="$t('profile_title')">
                <a-row :gutter="24" :span="24">
                  <a-col :span="12">
                    <a-form-item :label="$t('family_name')" name="family_name">
                      <a-input v-model:value="member.family_name" />
                    </a-form-item>
                    <a-form-item :label="$t('middle_name')" name="middle_name">
                      <a-input v-model:value="member.middle_name" />
                    </a-form-item>
                  </a-col>
                  <a-col :span="12">
                    <a-form-item :label="$t('given_name')" name="given_name">
                      <a-input v-model:value="member.given_name" />
                    </a-form-item>
                    <a-form-item :label="$t('display_name')" name="display_name">
                      <a-input v-model:value="member.display_name" />
                    </a-form-item>
                  </a-col>
                </a-row>
              </a-collapse-panel>
              <a-collapse-panel key="2" :header="$t('personal_title')">
                <a-row :gutter="24" :span="24">
                  <a-col :span="12">
                    <a-form-item :label="$t('dob')" name="dob">
                      <a-date-picker
                        v-model:value="member.dob"
                        :format="dateFormat"
                        :valueFormat="dateFormat"
                      />
                    </a-form-item>
                    <a-form-item :label="$t('country')" name="country">
                      <a-input v-model:value="member.country" />
                    </a-form-item>
                    <a-form-item :label="$t('nationality')" name="nationality">
                      <a-input v-model:value="member.nationality" />
                    </a-form-item>
                    <a-form-item :label="$t('address')" name="address">
                      <a-input v-model:value="member.address" />
                    </a-form-item>
                    <a-form-item :label="$t('email')" name="email">
                      <a-input v-model:value="member.email" />
                    </a-form-item>
                  </a-col>
                  <a-col :span="12">
                    <a-form-item :label="$t('gender')" name="gender">
                      <a-radio-group v-model:value="member.gender" button-style="solid">
                        <a-radio-button value="M">{{$t('male')}}</a-radio-button>
                        <a-radio-button value="F">{{$t('female')}}</a-radio-button>
                      </a-radio-group>
                    </a-form-item>
                    <a-form-item :label="$t('city')" name="city">
                      <a-input v-model:value="member.city" />
                    </a-form-item>
                    <a-form-item :label="$t('mobile_number')" name="mobile">
                      <a-input v-model:value="member.mobile" />
                    </a-form-item>
                  </a-col>
                </a-row>
              </a-collapse-panel>
              <a-collapse-panel key="3" :header="$t('picture_title')">
                <div v-if="member.avatar">
                  <img :src="member.avatar" class="w-full md:w-1/2 lg:w-1/3 h-auto"/>
                </div>
                <a-button @click="showCropModal = true">{{$t("upload_profile_image")}}</a-button>
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
                  </div>
                </div>
              </a-collapse-panel>
              <a-collapse-panel key="4" :header="$t('change_password')">
                <a-form>
                    <a-form
                      :model="password"
                      name="Change Password"
                      layout="horizontal"
                      :label-col="{ span: 4 }"
                      :wrapper-col="{ span: 8 }"
                      autocomplete="off"
                      @finish="onFinish"
                      @finishFailed="onFinishFailed"
                    >
                      <a-form-item
                        :label="$t('old_password')"
                        name="old"
                        :rules="[{ required: true, message: 'Please input your password!' }]"
                      >
                        <a-input-password v-model:value="password.old" />
                      </a-form-item>
                      <a-form-item
                        :label="$t('new_password')"
                        name="new"
                        :rules="[{ required: true, message: 'Please input your password!' }]"
                      >
                        <a-input-password v-model:value="password.new" />
                      </a-form-item>
                      <a-form-item
                        :label="$t('confirm_password')"
                        name="confirm"
                        :rules="[{ required: true, message: 'Please input your password!' }]"
                      >
                        <a-input-password v-model:value="password.confirm" />
                      </a-form-item>
                      <a-form-item :wrapper-col="{ offset: 8, span: 16 }">
                        <a-button danger html-type="submit"> {{ $t('confirm') }}</a-button>
                      </a-form-item>
                    </a-form>
                </a-form>
              </a-collapse-panel>
            </a-collapse>
            <a-form-item :wrapper-col="{ offset: 10, span: 24 }" style="padding-top: 20px">
              <a-button @click="onSubmit" type="primary">{{ $t("submit") }}</a-button>
            </a-form-item>
          </a-form>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import { PlusOutlined, LoadingOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import { quillEditor } from "vue3-quill";
import { UploadOutlined } from "@ant-design/icons-vue";
import CropperModal from "@/Components/Member/CropperModal.vue";

export default {
  components: {
    MemberLayout,
    PlusOutlined,
    LoadingOutlined,
    quillEditor,
    UploadOutlined,
    CropperModal,
  },
  props: ["member", "positions"],
  data() {
    return {
      dateFormat: "YYYY-MM-DD",
      showCropModal: false,
      avatarPreview: null,
      avatarData: null,
      activeKey: ["1"],
      loading: false,
      password:{},
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      rules: {
        given_name: { required: true },
        family_name: { required: true },
        display_name: { required: true },
        email: { required: true, type: "email" },
        field: { required: true },
        label: { required: true },
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
      layout: {
        labelCol: {
          span: 8,
        },
        wrapperCol: {
          span: 16,
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
    this.member.athlete = [];
  },
  methods: {
    setCroppedImageData(data) {
      this.avatarPreview = data.imageUrl;
      this.avatarData = data;
    },

    handleUploaded({ form, request, response }) {
      // update user avatar attribute
    },
    onSubmit() {
      if(this.avatarData){
        this.member.avatar = this.avatarData.blob;
      }
      //this.member.avatar = this.avatarData.blob;
      this.member._method = "PATCH";
      this.$inertia.post(route("member.profile.update", this.member.id), this.member, {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (err) => {
          console.log(err);
        },
      });
    },
    onFinish(){
      console.log('change password')
      this.password.id=this.member.id
      this.$inertia.post(route('member.profile.changePassword'),this.password,{
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (err) => {
          console.log(err);
        },
      })
    },
    onFinishFailed(){
      console.log('change password failed')
    }
  },
};
</script>
