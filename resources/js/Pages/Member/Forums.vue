<style>
.w-e-text-container {
  height: 700px !important;
}
</style>
<template>
  <MemberLayout title="留言板">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("forum") }}
      </h2>
    </template>

    <div class="container mx-auto min-h-[800px] py-6">
      <div class="grid xl:grid-cols-5 md:grid-cols-4 gap-4">
        <div v-for="b in forums" :key="b.id" class="shadow-md">
          <a :href="route('member.forums.show', b.id)">
            <a-card hoverable>
              <template #cover>
                <img alt="example" src="/storage/images/site_icon.png" />
              </template>
              <a-card-meta :title="b.title">
                <template #description>{{ b.description }}</template>
              </a-card-meta>
            </a-card>
          </a>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import { onBeforeUnmount, ref, shallowRef, onMounted } from "vue";
export default {
  components: { MemberLayout },
  props: ["forums"],
  setup() {
    const editorRef = shallowRef();
    const valueHtml = ref("<p>hello</p>");
    onMounted(() => {
      setTimeout(() => {
        valueHtml.value = "<p></p>";
      }, 1500);
    });
    const toolbarConfig = {};
    const editorConfig = { placeholder: "请输入内容...", MENU_CONF: {} };

    // 组件销毁时，也及时销毁编辑器
    onBeforeUnmount(() => {
      const editor = editorRef.value;
      if (editor == null) return;
      editor.destroy();
    });
    editorConfig.MENU_CONF["uploadImage"] = {
      async customUpload(file, insertFn) {
        const formData = new FormData();
        formData.append("file", file);
        console.log(file);
        window.axios
          .post(route("member.forum.media-upload"), formData, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((res) => {
            insertFn(res.data.path);
          });
      },
      // other config...
    };
    const handleCreated = (editor) => {
      editorRef.value = editor; // 记录 editor 实例，重要！
    };

    return {
      editorRef,
      valueHtml,
      mode: "default", // 或 'simple'
      toolbarConfig,
      editorConfig,
      handleCreated,
    };
  },
  data() {
    return {
      newBlog: "",
      createBlog: false,
    };
  },
};
</script>
