<script setup>
import { UserOutlined } from "@ant-design/icons-vue";

const props = defineProps({
  records: Object,
  routePath:String,
});
</script>

<template>
  <div v-for="record in records" :key="record.id" class="pt-2">
    <div class="bg-white relative shadow rounded-lg md:pl-5">
      <div class="flex flex-col md:flex-row items-center">
        <div class="md:w-[14vw] md:mr-4 shrink-0 flex justify-center">

          <img
            v-if="record.thumb_url"
            :src="record.thumb_url"
            class="object-cover"
            alt="thumbnail"
            width="200px"
          />
          <img v-else src="/images/site_logo.png" alt="Thumnail" />
        </div>
        <div class="p-2">
          <a :href="route(routePath, { t: record.uuid })" target="_blank">
            <h2 class="text-xl font-bold">{{ record.title }}</h2>
          </a>
          <div class="flex items-center mt-2 gap-2">
            <span v-if="record.organization_abbr" class="bg-gray-200 text-gray-700 py-1 px-2 rounded-full text-sm">{{ record.organization_abbr }}</span>
            <span v-if="routePath=='article.item'" class="bg-gray-200 text-gray-700 py-1 px-2 rounded-full text-sm">Article</span>
            <span v-else-if="routePath=='form.item'" class="bg-gray-200 text-gray-700 py-1 px-2 rounded-full text-sm">Form</span>
            <span v-else-if="routePath=='event.item'" class="bg-gray-200 text-gray-700 py-1 px-2 rounded-full text-sm">Event</span>
            <span v-if="record.category_code" class="bg-gray-200 text-gray-700 py-1 px-2 rounded-full text-sm">{{ record.category_code}}</span>
          </div>
          <div class="mt-2 text-gray-600">
            <div v-if="record.intro" class="text-justify">
              {{ record.intro }}
            </div>
            <div v-else-if="record.content">
              {{ record.content.replace(/<[^>]+>/g, "").substring(0, 100) }} ...
            </div>
            <div v-if="record.url">
              <inertia-link :href="record.url" target="_blank">{{
                $t("url_link")
              }}</inertia-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 
  <a-list item-layout="horizontal" :data-source="records">
    <template #renderItem="{ item }">
      <a-list-item>
        <a-list-item-meta>
          <template #title>
            <inertia-link :href="route('record.item', { t: item.uuid })">
              {{ item.title }}
            </inertia-link>
          </template>
          <template #description>
            <div v-if="item.intro">
              {{ item.intro_en }}
            </div>
            <div v-else>
              {{ item.content.replace(/<[^>]+>/g, '').substring(0, 100) }} ...
            </div>
            <a :href="item.url">{{ item.url }}</a>
          </template>
          <template #avatar>
            <a-avatar style="color: #f56a00; background-color: #fde3cf">
              <template v-if="item.author" #icon>
                {{ item.author.charAt(0).toUpperCase() }}
              </template>
              <template v-else #icon>
                <UserOutlined />
              </template>
            </a-avatar>
          </template>
        </a-list-item-meta>
      </a-list-item>
    </template>
  </a-list>
--></template>

