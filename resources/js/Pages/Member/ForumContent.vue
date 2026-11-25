<template>
  <MemberLayout title="留言板">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ forum.title }}
      </h2>
    </template>

    <div class="container mx-auto min-h-[800px] py-6">
      <div class="flex justify-end pt-2 pb-4">
        <a-button type="primary" shape="round" @click="replyContent(content.id)"
          >回覆</a-button
        >
      </div>
      <div
        class="border bg-white shadow-md rounded-md p-4 my-4"
        v-if="replyContentVisable == true"
      >
        <div class="">
          <a-form
            ref="modalRef"
            :model="reply_forum_content"
            name="forum_content"
            :rules="rules"
            :validate-messages="validateMessages"
          >
            <a-form-item :label="$t('content')" name="content">
              <a-textarea v-model:value="reply_forum_content.content"></a-textarea>
            </a-form-item>
          </a-form>
          <div class="flex justify-end gap-4">
            <a-button @click="cancelBlogContent">{{ $t("cancel") }}</a-button>
            <a-button type="primary" @click="createContent">{{ $t("confirm") }}</a-button>
          </div>
        </div>
      </div>
      <div class="flex flex-col gap-2">
        <a-card :title="content.title" :bordered="false">
          <div class="flex flex-col gap-1">
            <div>{{ content.user.name }}:</div>
            <div>
              {{ content.content }}
            </div>
          </div>
        </a-card>
        <div class="" v-for="(c, idx) in content.reply_contents" :key="idx">
          <a-card v-if="idx < page * 4">
            <div class="flex flex-col gap-1">
              <div class="flex justify-between">
                <div>{{ c.user.name }}:</div>
                <div>{{ dayjs(c.created_at).format("YYYY-MM-DD HH:mm:ss") }}</div>
              </div>
              <div>
                {{ c.content }}
              </div>
            </div>
            <div class="text-right">
              <a-button type="link" @click="replyContent(c.id)">回覆</a-button>
            </div>
            <a-card v-if="c.reply_contents.length != 0">
              <div v-for="rc in c.reply_contents" :key="rc.id" class="border-b-2 mb-4">
                {{ rc.user.name }}:{{ rc.content }}
              </div>
            </a-card>
          </a-card>
        </div>
        <div v-if="pageLoading" class="flex justify-center">
          <svg
            class="animate-spin"
            width="40"
            height="40"
            viewBox="-25 -25 250 250"
            version="1.1"
            xmlns="http://www.w3.org/2000/svg"
            style="transform: rotate(-90deg)"
          >
            <circle
              r="90"
              cx="100"
              cy="100"
              fill="transparent"
              stroke="#e0e0e0"
              stroke-width="16px"
              stroke-dasharray="565.48px"
              stroke-dashoffset="0"
            ></circle>
            <circle
              r="90"
              cx="100"
              cy="100"
              stroke="blue"
              stroke-width="16px"
              stroke-linecap="round"
              stroke-dashoffset="367px"
              fill="transparent"
              stroke-dasharray="565.48px"
            ></circle>
          </svg>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>
<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import { router } from "@inertiajs/vue3";
import { onBeforeUnmount, ref, shallowRef, onMounted } from "vue";
import dayjs from "dayjs";
import duration from "dayjs/plugin/duration";
dayjs.extend(duration);

export default {
  components: { MemberLayout, dayjs },
  props: ["forum", "content"],
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
      dayjs,
    };
  },
  created() {
    window.addEventListener("scroll", this.handleScroll);
  },
  unmounted() {
    window.removeEventListener("scroll", this.handleScroll);
  },
  data() {
    return {
      page: 1,
      pageLoading: false,
      newBlog: "",
      replyContentVisable: false,
      reply_forum_content: {},
      rules: {
        content: { required: true },
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
    };
  },
  methods: {
    createContent() {
      console.log(this.reply_forum_content);
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post(
            route("member.forum.content.reply", this.forum.id),
            this.reply_forum_content,
            {
              onSuccess: (page) => {
                console.log(page);
                this.cancelBlogContent();
              },
              onError: (error) => {
                console.log(error);
              },
            }
          );
        })
        .catch((err) => {
          console.log("error", err);
        });
    },
    cancelBlogContent() {
      this.replyContentVisable = false;
      this.reply_forum_content = {};
    },
    replyContent(reply_id) {
      this.replyContentVisable = true;
      this.reply_forum_content.reply_id = reply_id;
    },
    handleScroll(event) {
      const body = document.getElementById("app");
      const scrollHeight = body.scrollHeight;
      const scrollEnd = window.innerHeight + window.scrollY >= scrollHeight;

      if (scrollEnd == true && this.content.reply_contents.length > this.page * 4) {
        this.pageLoading = true;
        setTimeout(() => {
          this.pageLoading = false;
          this.page++;
        }, 1500);
      }
      console.log(scrollEnd);
    },
  },
};
</script>
