<template>
  <OrganizerLayout :title="$t('email')" :breadcrumb="breadcrumb">
      <div class="bg-white relative shadow rounded-lg p-5">
        <a-form 
          :model="email" 
          name="nest-messages"  
          :validate-messages="validateMessages"
          layout="vertical" 
          :rules="rules" 
          @finish="onFinish"
          @finishFailed="onFinishFailed"
        >
          <a-form-item :label="$t('email.sender')" name="sender">
            <a-input type="input" v-model:value="email.sender" />
          </a-form-item>
          <a-form-item :label="$t('email.recipient')" name="recipient">
            <a-input type="input" v-model:value="email.recipient" />
          </a-form-item>
          <a-form-item :label="$t('email.subject')" name="subject">
            <a-input type="input" v-model:value="email.subject" />
          </a-form-item>
          <a-form-item :label="$t('email.message')" name="message">
            <a-textarea v-model:value="email.message" />
          </a-form-item>
          <div class="flex flex-row item-center justify-center">
            <inertia-link :href="route('organizer.emails.index')" class="ant-btn">{{$t('back')}}</inertia-link>
            <a-button type="primary" html-type="submit" class="ml-5">{{$t('submit')}}</a-button>
          </div>
        </a-form>
      </div>
    
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
// import { quillEditor } from 'vue3-quill';
import { message } from "ant-design-vue";
import dayjs from 'dayjs';

export default {
  components: {
    OrganizerLayout,
    // quillEditor,
    message,
    dayjs,
  },
  props: ['email'],
  data() {
    return {
      breadcrumb: [
        { label: "電郵列表", url:route('organizer.emails.index')},
        { label: this.email.id?'電郵修改':'電郵新增', url: null }
      ],
      mode: null,
      dateFormat: "YYYY-MM-DD",
      rules: {
        sender: { required: true, type:'email' },
        recipient: { required: true, type:'email' },
        subject: { required: true },
        body: { required: true },
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
  mounted() {
  },
  methods: {
    onFinish() {
        console.log(this.email);
      if(this.email.id===undefined){
        console.log(this.email);
        this.$inertia.post(route('organizer.emails.store'), this.email,{
            onSuccess:(page)=>{
                console.log(page);
            },
            onError:(err)=>{
                console.log(err);
            }
        });
      }else{
        this.$inertia.patch(route('organizer.emails.update',this.email.id), this.email,{
            onSuccess:(page)=>{
                console.log(page);
            },
            onError:(err)=>{
                console.log(err);
            }
        });
      }

    },
    onFinishFailed({ values, errorFields, outOfDate }){
      message.error("Some required fields are missing!");
    },

  },
};
</script>

