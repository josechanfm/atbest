<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    articles: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const featuredArticle = computed(() => props.articles[0] || null);
const remainingArticles = computed(() => props.articles.slice(1));
</script>

<template>
    <GuestLayout title="Dashboard">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-xl text-gray-800 tracking-tight">
                    歡迎回來
                </h2>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    {{ new Date().toLocaleDateString('zh-Hant', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                </span>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-10">
            <!-- Hero Welcome Banner -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0081C8] via-[#0066a1] to-slate-900 p-8 sm:p-10 text-white shadow-xl">
                <!-- Decorative background shapes -->
                <div class="absolute -right-16 -top-16 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute right-20 -bottom-20 w-56 h-56 bg-cyan-400/20 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-sm border border-white/20 text-xs font-semibold tracking-wider text-cyan-100 mb-4">
                        <span class="w-2 h-2 rounded-full bg-cyan-300 animate-pulse"></span>
                        澳門社會服務網絡
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                        你好，{{ user?.name || '訪客' }} 👋
                    </h1>

                    <p class="mt-3 text-sm sm:text-base text-blue-100 leading-relaxed">
                        歡迎來到澳門社會服務網絡。我們為您精選了最新的社區資訊、活動動態與服務指南。
                    </p>
                </div>
            </div>

            <!-- Articles Section -->
            <section class="space-y-6">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-1.5 h-6 bg-[#0081C8] rounded-full"></span>
                        <h3 class="text-lg font-bold text-gray-900 tracking-tight">
                            最新文章與資訊
                        </h3>
                    </div>
                    <span v-if="articles.length > 0" class="text-xs font-medium text-gray-500">
                        共 {{ articles.length }} 篇內容
                    </span>
                </div>

                <!-- Empty State -->
                <div
                    v-if="articles.length === 0"
                    class="bg-white rounded-3xl border border-dashed border-gray-300 p-16 text-center"
                >
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-blue-50 text-[#0081C8] flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-gray-900">暫無最新文章</h4>
                    <p class="text-sm text-gray-500 mt-1">目前尚未發布任何資訊，請稍後再回來查看。</p>
                </div>

                <!-- Articles Feed -->
                <div v-else class="space-y-6">
                    <!-- Featured First Article -->
                    <Link
                        v-if="featuredArticle"
                        :href="route('article.item', { t: featuredArticle.uuid })"
                        class="group block bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-200 overflow-hidden transition-all duration-300"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-12">
                            <!-- Featured Image -->
                            <div class="md:col-span-5 relative min-h-[220px] bg-gradient-to-br from-blue-50 to-slate-100 overflow-hidden">
                                <img
                                    v-if="featuredArticle.cover_image"
                                    :src="featuredArticle.cover_image"
                                    :alt="featuredArticle.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-blue-200">
                                    <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold bg-[#0081C8] text-white shadow-md">
                                    焦點推薦
                                </span>
                            </div>

                            <!-- Featured Content -->
                            <div class="md:col-span-7 p-6 sm:p-8 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-3 mb-3">
                                        <span
                                            v-if="featuredArticle.category"
                                            class="text-xs font-bold text-[#0081C8] bg-blue-50 px-2.5 py-1 rounded-md"
                                        >
                                            {{ featuredArticle.category }}
                                        </span>
                                        <span v-if="featuredArticle.published_at" class="text-xs text-gray-400">
                                            {{ featuredArticle.published_at }}
                                        </span>
                                    </div>

                                    <h4 class="text-xl sm:text-2xl font-bold text-gray-900 group-hover:text-[#0081C8] transition-colors line-clamp-2">
                                        {{ featuredArticle.title }}
                                    </h4>

                                    <p
                                        v-if="featuredArticle.excerpt"
                                        class="text-sm text-gray-600 mt-3 leading-relaxed line-clamp-3"
                                    >
                                        {{ featuredArticle.excerpt }}
                                    </p>
                                </div>

                                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center text-sm font-semibold text-[#0081C8]">
                                    <span>閱讀全文</span>
                                    <svg class="w-4 h-4 ml-1.5 group-hover:translate-x-1.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Remaining Articles Grid -->
                    <div v-if="remainingArticles.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <Link
                            v-for="article in remainingArticles"
                            :key="article.id"
                            :href="route('article.item', { t: article.uuid })"
                            class="group flex flex-col justify-between bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 hover:border-blue-200 p-5 transition-all duration-200"
                        >
                            <div class="flex gap-4">
                                <!-- Thumbnail -->
                                <div class="w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded-xl overflow-hidden bg-gray-100">
                                    <img
                                        v-if="article.cover_image"
                                        :src="article.cover_image"
                                        :alt="article.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span
                                            v-if="article.category"
                                            class="text-[11px] font-bold text-[#0081C8] bg-blue-50 px-2 py-0.5 rounded-md"
                                        >
                                            {{ article.category }}
                                        </span>
                                        <span v-if="article.published_at" class="text-[11px] text-gray-400">
                                            {{ article.published_at }}
                                        </span>
                                    </div>

                                    <h4 class="text-base font-bold text-gray-900 group-hover:text-[#0081C8] transition-colors line-clamp-2">
                                        {{ article.title }}
                                    </h4>

                                    <p
                                        v-if="article.excerpt"
                                        class="text-xs text-gray-500 mt-1.5 leading-relaxed line-clamp-2"
                                    >
                                        {{ article.excerpt }}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </GuestLayout>
</template>