<template>
  <DefaultLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{$t('event')}}
      </h2>
    </template>
    <div class="max-w-7xl mx-auto py-10 px-2 sm:px-6 lg:px-8">
        <h2 class="font-bold text-2xl text-gray-800">
            {{ event.title}}</h2>
        <div v-html="event.content"/>
    </div>
  </DefaultLayout>
</template>

<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import DefaultLayout from "@/Layouts/DefaultLayout.vue";
import CropperModal from "@/Components/Member/CropperModal.vue";
import { quillEditor } from "vue3-quill";

export default {
  components: {
    MemberLayout,
    DefaultLayout,
    quillEditor,
    CropperModal,
  },
  props: ["event"],
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
      this.formData[this.event.fields.find((x) => x.type == "photo").id] = data;
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

#pure-html {
  all: initial;
}
#pure-html * {
  all: revert;
}
</style>
