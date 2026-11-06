<template>
<div class="flex flex-col gap-3 my-4">
    <div class="text-xl font-mono">最近新聞</div>
    <div v-if="loading">{{ $t('loading') }}</div>
    <div v-else-if="error" role="alert" class="rounded-lg bg-white px-4 py-3 text-red-700">{{ $t('rss_load_failed') }}</div>
    <div v-else class="shadow-md py-1 px-4 bg-white rounded-lg">
        <div v-for="(item, index) in feedItems.slice(0,15)" :key="index"
            class="my-2 pb-4 news-item overflow-auto underline">
            <div class="px-2 text-lg rounded ">{{ item.title }}</div>
            <!-- <p class="w-full" v-html="item.description"></p> -->
            <div class="flex justify-end mt-2">
                <a class="text-blue-700 hover:text-blue-800 mx-4 " :href="item.link" target="_blank">閱讀更多</a>
            </div>
            <a-divider class="mt-3 mb-1"/>
        </div>
    </div>
</div>
</template>

<script>
import { parseRss } from '@/utils/parseRss';

export default {
    data() {
        return {
            feedItems: [],
            loading: true,
            error: null
        };
    },
    mounted() {
        this.getRss()
    },
    methods: {
        async getRss() {
            this.loading = true;
            this.error = null;
            try {
                const response = await fetch(route('host.getRss'));
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const text = await response.text();
                this.feedItems = parseRss(text);
            } catch (err) {
                this.error = true;
                console.error(err);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style>
</style>
