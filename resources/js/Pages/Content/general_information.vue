<script setup>
import WebLayout from '@/Layouts/WebLayout.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import { UserOutlined } from '@ant-design/icons-vue';

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
  isMember: Boolean,
  isOrganizer: Boolean,
  articles: Array,
});
</script>

<template>
  <WebLayout title="Dashboard">
    <div class="lg:h-96 bg-gradient-to-tr bg-white rounded-md">
      <div class="ml-5  lg:w-4/5 py-5">
        <a-typography-title :level="3">社團及財團登記</a-typography-title>
        <ol>
          <li><a href="https://www.gov.mo/zh-hant/services/ps-1069/faqs/" target="_blank">常見問題</a></li>
          <li><a href="https://www.gov.mo/zh-hant/services/ps-1069/faqs/" target="_blank">會員大會會議紀錄(樣本)</a></li>
        </ol>
      </div>
    </div>

  </WebLayout>
</template>
