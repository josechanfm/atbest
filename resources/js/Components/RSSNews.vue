<template>
<div>
    <h1>最近新聞</h1>
    <div v-if="loading">載入中...</div>
    <div v-else class="shadow-md py-1 px-4 bg-white rounded-lg">
        <!-- {{ parseXml( feedItems ) }} -->
        <div v-for="item in parseXml(feedItems).slice(0,15)" :key="item.guid" 
            class="my-2 pb-4 news-item overflow-auto underline">
            <h2 class="px-2 rounded">{{ item.title }}</h2>
            <!-- <p class="w-full" v-html="item.description"></p> -->
            <div class="flex justify-end">
                <a class="text-blue-700 hover:text-blue-800 mx-4 " :href="item.link" target="_blank">閱讀更多</a>
            </div>
            <a-divider class="mt-3 mb-1"/>
        </div>
    </div>
</div>
</template>

<script>
import {
    XMLParser
} from 'fast-xml-parser';

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

            try {
                // 注意這裡使用的是你的 Laravel 後端 API 地址
                const response = await fetch(route('host.getRss'));

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // 如果後端返回的是 XML，你需要在前端解析
                const text = await response.text();
                // 使用 DOMParser 或其他 XML 解析庫解析
                this.feedItems = text

            } catch (err) {
                this.error = '無法載入 RSS 資料: ' + err.message;
                console.error(err);
            } finally {
                this.loading = false;
            }
        },
        parseXml(xmlString) {
            const options = {
                ignoreAttributes: false,
                attributeNamePrefix: "@_",
                allowBooleanAttributes: true,
                parseNodeValue: true,
                parseAttributeValue: true,
                trimValues: true
            };

            const parser = new XMLParser(options);
            const result = parser.parse(xmlString);

            // 根據你的 RSS 結構提取項目
            return result.rss.channel.item.map(item => ({
                title: item.title,
                link: item.link,
                description: item.description,
                pubDate: item.pubDate,
                guid: item.guid
            }));
        }
    }
}
</script>

<style>
</style>
