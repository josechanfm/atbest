<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import ThumbList from "@/Components/ThumbList.vue";
import { Head, Link } from '@inertiajs/vue3';
import { UserOutlined } from '@ant-design/icons-vue';

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
  isMember: Boolean,
  isOrganizer: Boolean,
  articles: Array,
  forms: Array,
  events: Array,
  welcomeMessage: Object,
});
</script>

<template>
  <DefaultLayout title="Dashboard">
    <div class="lg:h-96 bg-gradient-to-tr bg-[#0081C8] rounded-md flex items-center">
      <div class="ml-5 lg:ml-20 lg:w-4/5 py-5">
        <h2 class="text-white text-4xl" v-if="welcomeMessage">{{ welcomeMessage.title }}</h2>
        <h2 class="text-white text-4xl" v-else>{{ $t('welcome_message') }}</h2>
        <p class="lg:text-lg text-indigo-100 mr-4 capitalize font-thick tracking-wider leading-7 text-justify">
          <template v-if="welcomeMessage">
            {{ welcomeMessage.intro  }}
            <template v-if="welcomeMessage.url">
                <a-button :href="welcomeMessage.url">{{ $t('readmore') }}</a-button>
            </template>
            <template v-else-if="welcomeMessage.content">
              <a-button :href="route('article.item',{t:welcomeMessage.uuid})">{{ $t('readmore') }}</a-button>
            </template>
          </template>
          <template v-else>
            「公務人員聯合總會網首頁」是一個提供公務人員相關資訊和服務的網站。該網站結合了屬會和友會的功能，為公務人員提供一個資信平台，方便他們獲取所需的資訊和進行交流。
            此網站除了提供基本資訊外，還提供了電子會員卡和會員會籍管理系統等功能。通過這些功能，公務人員可以方便地管理自己的會籍信息，並獲得相關的會員福利。
            此外，「公務人員聯合總會網首頁」也可能提供其他相關服務，例如公務人員培訓課程、就業資訊、法規法案解讀等。透過這些服務，公務人員可以更好地了解自己所屬的組織和行業的最新動態，並提升自身的專業能力。
          </template>
        </p>
        <!-- <a href="#"
          class="uppercase inline-block mt-8 text-sm bg-white py-2 px-4 rounded font-semibold hover:bg-indigo-100"> Learn More
        </a> -->
      </div>
    </div>

    <div class="px-4 py-2 mt-2 bg-white rounded-md">
      <h2 class="font-bold text-2xl text-gray-800">最新消息</h2>
    </div>
      <ThumbList :records="articles" routePath="article.item"/>
      <ThumbList :records="forms" routePath="form.item"/>
      <ThumbList :records="events" routePath="event.item"/>
  </DefaultLayout>
</template>
