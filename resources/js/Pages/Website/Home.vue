<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import ThumbList from "@/Components/ThumbList.vue";
import { default as Carousel } from "@/Components/Carousel01.vue";

defineProps({
  organization:Object,
  articles: Object,
});
</script>

<template>
  <WebsiteLayout title="Dashboard" :organization="organization">
    <Carousel/>
    <div class="px-4 py-2 mt-2 bg-white rounded-md">
      <h2 class="font-bold text-2xl text-gray-800">最新消息</h2>
    </div>
      <ThumbList :records="articles" routePath="article.item"/>
  </WebsiteLayout>
</template>
