<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    articles: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <WebsiteLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                歡迎回來
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Welcome Banner -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 via-indigo-700 to-slate-900 p-8 text-white shadow-xl">
                    <div class="absolute -right-12 -bottom-12 w-56 h-56 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative z-10">
                        <p class="text-xs font-bold uppercase tracking-widest text-cyan-200 mb-3">
                            澳門社會服務網絡
                        </p>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            你好，{{ user.name }}
                        </h1>
                        <p class="mt-3 text-sm text-indigo-100 leading-relaxed">
                            歡迎來到澳門社會服務網絡。以下是為您推薦的最新資訊。
                        </p>
                    </div>
                </div>

                <!-- Articles -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">
                        最新文章
                    </h3>

                    <!-- Empty State -->
                    <div
                        v-if="articles.length === 0"
                        class="bg-white rounded-2xl border border-dashed border-gray-200 p-12 text-center text-gray-400"
                    >
                        <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm">暫無文章。</p>
                    </div>

                    <!-- Article List -->
                    <div v-else class="space-y-4">
                        <Link
                            v-for="article in articles"
                            :key="article.id"
                            :href="route('article.item', {t:article.uuid})"
                            class="group block bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-200 p-5 sm:p-6 transition-all"
                        >
                            <div class="flex gap-4">
                                <!-- Thumbnail -->
                                <div
                                    v-if="article.cover_image"
                                    class="w-24 h-24 sm:w-28 sm:h-28 shrink-0 rounded-xl overflow-hidden bg-gray-100"
                                >
                                    <img
                                        :src="article.cover_image"
                                        :alt="article.title"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                </div>

                                <!-- Content -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span
                                            v-if="article.category"
                                            class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full"
                                        >
                                            {{ article.category }}
                                        </span>
                                        <span v-if="article.published_at" class="text-[11px] text-gray-400">
                                            {{ article.published_at }}
                                        </span>
                                    </div>

                                    <h4 class="text-base font-bold text-gray-900 group-hover:text-indigo-700 transition-colors line-clamp-2">
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

            </div>
        </div>
    </WebsiteLayout>
</template>