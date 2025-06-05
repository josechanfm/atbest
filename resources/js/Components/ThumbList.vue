<script>
import {
    gsap
} from 'gsap';
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export default {
    components: {},
    props: ["records", "routePath", "forms", "articles", "cardStyle"],
    data() {
        return {

        }
    },
    mounted() {
        this.setupCardAnimations();
    },
    methods: {
        scaleUp(event) {
            gsap.to(event.currentTarget.querySelector('div'), {
                scale: 1.05,
                duration: 0.3,
            });
        },
        scaleDown(event) {
            gsap.to(event.currentTarget.querySelector('div'), {
                scale: 1,
                duration: 0.3,
            });
        },
        
        setupCardAnimations() {
            gsap.utils.toArray('.card').forEach( (section,index) => {
                gsap.set(section, {
                    y: 50,
                    opacity: 0,
                    duration: 0.5,
                    ease: 'power3.out',
                    overwrite: 'auto',
                });
                let delay = 0.2 + 1* ( ( index ) / 10 )
                ScrollTrigger.create({
                    trigger: section,
                    start: 'top bottom',
                    // markers: true,
                    onEnter: () => gsap.to(section, {
                        y: 0,
                        opacity: 1,
                        duration: 0.5,
                        stagger: 0.2,
                        delay: delay
                    }),
                    onEnterBack: () => gsap.to(section, {
                        y: 0,
                        opacity: 1,
                        duration: 0.5,
                        stagger: -0.2,
                        delay: delay
                    }),
                });
            })
        }
    },
}
</script>

<template>

 
<div class="card-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 my-4" style="  ">
    <div v-for="record in records" 
        :key="record.id" 
        @mouseenter="scaleUp($event)" 
        @mouseleave="scaleDown($event)"
        class="relative card bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl flex flex-col">

        <div v-if="record.thumb_url" style="background-image: url(record.thumb_url)" class="min-h-48 bg-cover "> </div>
        <div v-else style="background-image: url('/storage/images/site_logo.png')" class="min-h-48 bg-no-repeat bg-center "> </div> 
        
        <div class="flex-1 p-6 flex flex-col ">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>

                <a :href="route(routePath, { t: record.uuid })" target="_blank">
                    <h2 class="text-xl font-bold">{{ record.title }}</h2>
                </a>
            </div>
            <div class="flex-1">
                <div v-if="record.intro" class="text-justify">
                    {{ record.intro }}
                </div>
                <div v-else-if="record.content">
                    {{ record.content.replace(/<[^>]+>/g, "").substring(0, 100) }} ...
                </div>
                <div v-else-if="record.description">
                    {{ record.description.replace(/<[^>]+>/g, "").substring(0, 100) }} ...
                </div>
            </div>
            <a-divider class="my-3 " />
            <div class=" text-gray-600 leading-relaxed px-2">
                <div class="flex flex-row justify-between items-center">
                    
                    <div class="flex items-center gap-2">
                        <span v-if="record.organization_abbr" class="bg-rose-600 text-white py-1 px-2 rounded-full text-sm">{{ record.organization_abbr }}</span>
                        <span v-if="routePath=='article.item'" class="bg-blue-500 text-slate-100 py-1 px-2 rounded-full text-sm">Article</span>
                        <span v-else-if="routePath=='form.item'" class="bg-green-300 text-gray-500 py-1 px-2 rounded-full text-sm">Form</span>
                        <span v-else-if="routePath=='event.item'" class="bg-pink-600 text-white py-1 px-2 rounded-full text-sm">Event</span>
                        <span v-if="record.category_code" class="bg-lime-400 text-gray-600 py-1 px-2 rounded-full text-sm">{{ record.category_code}}</span>
                    </div>
                    
                    <div class=" text-gray-600">
                        <div v-if="record.url">
                            <inertia-link :href="record.url" target="_blank">{{
                                $t("url_link")
                            }}</inertia-link>
                        </div>
                    </div>
                    <!-- <a class="text-blue-700 underline" href="/products/atbest">連結</a> -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <div v-for="record in records" :key="record.id" class="pt-2">
    <div class="bg-white relative shadow rounded-lg md:pl-5">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-[14vw] md:mr-4 shrink-0 flex justify-center">
                <img v-if="record.thumb_url" :src="record.thumb_url" class="object-cover" alt="thumbnail" width="200px" />
                <img v-else src="/storage/images/site_logo.png" alt="Thumnail" />
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
                <div class="mt-3 text-gray-600">
                    <div v-if="record.intro" class="text-justify">
                        {{ record.intro }}
                    </div>
                    <div v-else-if="record.content">
                        {{ record.content.replace(/<[^>]+>/g, "").substring(0, 100) }} ...
                    </div>
                    <div v-if="record.url" class="mt-2">
                        <inertia-link :href="record.url" target="_blank">{{
                            $t("url_link")
                        }}</inertia-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->

</template>


<style>
/* 自定義動畫 */
.hover-scale {
    transition: transform 0.3s ease;
}

.hover-scale:hover {
    transform: translateY(-5px);
}

/* 漸層文字效果 */
.gradient-text {
    background: linear-gradient(45deg, #4F46E5, #EC4899);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.card-grid{

justify-content: space-around;
}
</style>
