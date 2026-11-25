<template>
  <MemberLayout title="留言板">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ forum.title }}
        </h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Action Bar & Board Header -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600">
                會員交流區
              </span>
              <span class="text-xs text-gray-400">
                共 {{ forum_contents?.length || 0 }} 則主題
              </span>
            </div>
            <p v-if="forum.description" class="text-sm text-gray-500 mt-1.5">
              {{ forum.description }}
            </p>
          </div>

          <button
            type="button"
            @click="openComposer"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all shrink-0 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            發佈留言
          </button>
        </div>

        <!-- New Post / Message Composer Box -->
        <div
          v-if="createBlogContent"
          ref="composerBox"
          class="bg-white rounded-3xl shadow-xl border border-indigo-100 p-6 sm:p-8 transition-all"
        >
          <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
              </div>
              <div>
                <h3 class="text-base font-bold text-gray-900">發表新主題留言</h3>
                <p class="text-xs text-gray-400">與智滙精英社團成員分享觀點、資訊或提問</p>
              </div>
            </div>
            <button
              type="button"
              @click="cancelBlogContent"
              class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
              aria-label="關閉"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <a-form
            ref="modalRef"
            :model="new_forum_content"
            name="forum_content"
            layout="vertical"
            :rules="rules"
            :validate-messages="validateMessages"
          >
            <a-form-item :label="$t('title')" name="title">
              <a-input
                v-model:value="new_forum_content.title"
                size="large"
                placeholder="請輸入留言標題..."
                class="!rounded-xl"
              />
            </a-form-item>

            <a-form-item :label="$t('content')" name="content">
              <a-textarea
                v-model:value="new_forum_content.content"
                :rows="5"
                show-count
                :maxlength="2000"
                placeholder="請輸入詳細內容..."
                class="!rounded-xl"
              />
            </a-form-item>
          </a-form>

          <div class="flex items-center justify-end gap-3 pt-2">
            <button
              type="button"
              @click="cancelBlogContent"
              class="px-5 py-2.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
            >
              {{ $t("cancel") }}
            </button>
            <button
              type="button"
              :disabled="submitting"
              @click="createContent"
              class="px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md disabled:opacity-50 transition-all"
            >
              {{ submitting ? '發佈中...' : $t("confirm") }}
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-if="!forum_contents || forum_contents.length === 0"
          class="bg-white rounded-3xl shadow-sm border border-dashed border-gray-200 p-12 text-center"
        >
          <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-gray-600">目前尚無任何留言</p>
          <p class="text-xs text-gray-400 mt-1">點擊右上角「發佈留言」開啟第一個討論話題</p>
        </div>

        <!-- Topic Cards List -->
        <div v-else class="space-y-3" ref="scrollContainer">
          <template v-for="(bc, idx) in forum_contents" :key="bc.id || idx">
            <a
              v-if="idx < page * 6"
              :href="route('member.forum.contents.show', { forum: forum, content: bc })"
              class="group block bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-200"
            >
              <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <!-- Title & Excerpt -->
                <div class="min-w-0 flex-1">
                  <h3 class="text-base sm:text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-1">
                    {{ bc.title }}
                  </h3>
                  <p class="text-sm text-gray-500 mt-1.5 line-clamp-2 leading-relaxed">
                    {{ bc.content }}
                  </p>
                </div>

                <!-- Reply Counter Badge -->
                <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                  <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-colors"
                    :class="bc.reply_contents?.length > 0 ? 'bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white' : 'bg-gray-100 text-gray-500'"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    {{ bc.reply_contents?.length || 0 }} 回覆
                  </span>
                </div>
              </div>

              <!-- Card Footer Meta -->
              <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-xs text-gray-400">
                <div class="flex items-center gap-2">
                  <span v-if="bc.author || bc.member || bc.user" class="font-medium text-gray-600">
                    {{ bc.author?.name_zh || bc.member?.name_zh || bc.user?.name }}
                  </span>
                  <span v-if="bc.author || bc.member || bc.user">·</span>
                  <time>{{ formatDate(bc.created_at) }}</time>
                </div>

                <span class="text-indigo-500 font-medium opacity-0 group-hover:opacity-100 transition-opacity inline-flex items-center gap-1">
                  查看討論
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </span>
              </div>
            </a>
          </template>

          <!-- Pagination / Load More Indicator -->
          <div v-if="forum_contents.length > page * 6" class="py-4 text-center">
            <span v-if="pageLoading" class="text-xs text-gray-400">載入更多留言中...</span>
            <button
              v-else
              type="button"
              @click="page++"
              class="px-5 py-2 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-full transition-colors"
            >
              載入更多 ({{ forum_contents.length - page * 6 }})
            </button>
          </div>
        </div>

      </div>
    </div>
  </MemberLayout>
</template>

<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import dayjs from "dayjs";

export default {
  components: { MemberLayout },
  props: {
    forum: { type: Object, required: true },
    forum_contents: { type: Array, default: () => [] },
  },
  data() {
    return {
      page: 1,
      pageLoading: false,
      submitting: false,
      createBlogContent: false,
      new_forum_content: {
        title: "",
        content: "",
      },
      rules: {
        title: { required: true },
        content: { required: true },
      },
      validateMessages: {
        required: "${label} 為必填項目！",
      },
    };
  },
  created() {
    window.addEventListener("scroll", this.handleScroll, { passive: true });
  },
  unmounted() {
    window.removeEventListener("scroll", this.handleScroll);
  },
  methods: {
    formatDate(date) {
      return dayjs(date).format("YYYY-MM-DD HH:mm");
    },
    openComposer() {
      this.createBlogContent = true;
      this.$nextTick(() => {
        this.$refs.composerBox?.scrollIntoView({ behavior: "smooth", block: "center" });
      });
    },
    createContent() {
      if (this.submitting) return;
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.submitting = true;
          this.$inertia.post(
            route("member.forum.contents.store", this.forum.id),
            this.new_forum_content,
            {
              preserveScroll: true,
              onSuccess: () => {
                this.cancelBlogContent();
              },
              onFinish: () => {
                this.submitting = false;
              },
            }
          );
        })
        .catch(() => {});
    },
    cancelBlogContent() {
      this.createBlogContent = false;
      this.new_forum_content = { title: "", content: "" };
    },
    handleScroll() {
      const body = document.getElementById("app");
      if (!body || this.pageLoading) return;
      const scrollEnd = window.innerHeight + window.scrollY >= body.scrollHeight - 80;

      if (scrollEnd && this.forum_contents.length > this.page * 6) {
        this.pageLoading = true;
        setTimeout(() => {
          this.pageLoading = false;
          this.page++;
        }, 400);
      }
    },
  },
};
</script>