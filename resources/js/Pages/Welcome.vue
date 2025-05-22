<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import ThumbList from "@/Components/ThumbList.vue";
import { gsap } from 'gsap';

export default {
    components: {
        DefaultLayout,
        ThumbList,
    },
    props: {
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
    },

    mounted() {
        gsap.fromTo(
            this.$refs.section_news, {
                y: 100,
                opacity: 0,
                duration: 1
            }, // 初始状态
            {
                y: 0,
                opacity: 1,
                duration: 1
            } // 结束状态
        );
    },
    methods: {}
};
</script>

<template>
<DefaultLayout title="Dashboard">

    <div class="flex flex-col gap-12 pt-16">
        <div class="h-[60vh] lg:h-[60vh] bg-slate-800 bg-gradient-to-tr relative overflow-hidden rounded-md flex items-center shadow-md"
            >
            <img class="w-full opacity-60" src="/storage/images/meeting.jpg"/>
            
            <div class="">
                
                <!-- <div class="relative w-full bg-cover bg-center min-h-[250px] min-w-[500px] "></div> -->
                <div class="absolute w-3/4 top-10 left-10 text-white bg-black/70 p-2 rounded-lg" >
                    <div class="ml-5 lg:ml-20 lg:w-4/5 py-5 ">
                        <h2 class="text-white text-4xl" v-if="welcomeMessage">{{ welcomeMessage.title }}</h2>
                        <h2 class="text-white text-4xl" v-else>{{ $t('welcome_message') }}</h2>
                        <p class="text-lg text-indigo-100 mr-4 capitalize font-thick tracking-wider leading-7 text-justify">
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
                                <div class="leading-8 ">
                                    「公務人員聯合總會網首頁」是一個提供公務人員相關資訊和服務的網站。該網站結合了屬會和友會的功能，為公務人員提供一個資信平台，方便他們獲取所需的資訊和進行交流。
                                    此網站除了提供基本資訊外，還提供了電子會員卡和會員會籍管理系統等功能。通過這些功能，公務人員可以方便地管理自己的會籍信息，並獲得相關的會員福利。
                                    此外，「公務人員聯合總會網首頁」也可能提供其他相關服務，例如公務人員培訓課程、就業資訊、法規法案解讀等。透過這些服務，公務人員可以更好地了解自己所屬的組織和行業的最新動態，並提升自身的專業能力。
                                </div>
                            </template>
                        </p>
                    </div>    
                </div>
            </div>

        </div>
        <div ref="section_news" class="my-4">
            <div class="px-4 text-3xl mt-4 mb-8 font-bold text-gray-800 max-w-md" style="border-bottom: 2px solid #404040">
                最新消息 <span class="text-base px-4 text-slate-500">News</span>
            </div>

            <ThumbList :records="articles" routePath="article.item" />
            <ThumbList :records="forms" routePath="form.item" />
            <ThumbList :records="events" routePath="event.item" />
        </div>
    </div>
</DefaultLayout>
</template>
