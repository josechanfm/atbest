<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import ThumbList from "@/Components/ThumbList.vue";
import IntroduceBox from "@/Components/IntroduceBox.vue";
import { gsap } from 'gsap';

export default {
    components: {
        IntroduceBox,
        DefaultLayout,
        ThumbList,
    },
    props: {
        canLogin: Boolean,
        canRegister: Boolean,
        laravelVersion: String,
        phpVersion: String,
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
<DefaultLayout title="智滙精英 AtBest" :sideBar="true">

    <div class="flex flex-col gap-4">
        <!-- Hero Banner: h-auto on mobile so the block expands to fit full-size text -->
        <div class="relative h-auto min-h-[45vh] md:min-h-[50vh] lg:min-h-[60vh] bg-slate-800 bg-gradient-to-tr overflow-hidden rounded-md flex items-center shadow-xl p-2 sm:p-6 md:p-10">
            <!-- Background image fills whatever height the content block requires -->
            <img class="absolute inset-0 w-full h-full object-cover opacity-60" src="/storage/images/meeting.jpg" alt="智滙精英背景" />

            <!-- Content Block: Full width on mobile, normal flow (relative z-10) so block height grows with content -->
            <div class="relative z-10 w-full md:w-4/5 lg:w-3/4 text-white bg-black/75 p-4 sm:p-6 md:p-8 rounded-lg">
                <div class="ml-0 md:ml-4 lg:ml-12 w-full lg:w-11/12">
                    <h2 class="text-white font-bold text-2xl md:text-3xl lg:text-4xl mb-3" v-if="welcomeMessage">
                        {{ welcomeMessage.title }}
                    </h2>
                    <h2 class="text-white font-bold text-2xl md:text-3xl lg:text-4xl mb-3" v-else>
                        {{ $t('welcome_message') }}
                    </h2>

                    <div class="text-indigo-100 tracking-wider text-justify">
                        <template v-if="welcomeMessage">
                            <p class="text-base md:text-lg leading-7 md:leading-8 mb-3">
                                {{ welcomeMessage.intro }}
                            </p>
                            <template v-if="welcomeMessage.url">
                                <a-button :href="welcomeMessage.url">{{ $t('readmore') }}</a-button>
                            </template>
                            <template v-else-if="welcomeMessage.content">
                                <a-button :href="route('article.item',{t:welcomeMessage.uuid})">{{ $t('readmore') }}</a-button>
                            </template>
                        </template>
                        <template v-else>
                            <div class="text-base md:text-lg leading-7 md:leading-8 space-y-3">
                                <p>
                                    「智滙精英（Atbest）會員平台」是一個紮根澳門、專為凝聚本地各領域優秀社團領袖與專業人士而設的非牟利組織（NPO）社團社交平台。網站匯聚澳門多元社團社群，配備專屬電子會員卡及一站式會籍管理系統，讓成員隨時輕鬆管理個人會籍、拓展跨會人脈網絡，並尊享全方位會員禮遇。
                                </p>
                                <p>
                                    此外，「智滙精英」亦致力打造充滿活力的澳門社團互動圈，持續提供社團聯誼活動、專業培訓課程、本地行業前沿資訊及特區政策法規解讀，促進跨界交流與資源共享，攜手推動澳門社團及社會的高質量發展。
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    
        <IntroduceBox />

        <div ref="section_news" class="my-4">
            <div class="mx-4 text-3xl mt-4 mb-8 font-bold text-gray-800 max-w-md" style="border-bottom: 2px solid #404040">
                最新消息 <span class="text-base px-4 text-slate-500">News</span>
            </div>
            <ThumbList :records="articles" routePath="article.item" />
            <ThumbList :records="forms" routePath="form.item" />
            <ThumbList :records="events" routePath="event.item" />
        </div>
    </div>
</DefaultLayout>
</template>