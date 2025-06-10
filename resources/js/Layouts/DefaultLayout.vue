<script>
import { ref, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import menuDashboard from "@/Components/menuDashboard.vue";
import LayoutBackground from "@/Components/LayoutBackground.vue";
import RSSNews from "@/Components/RSSNews.vue";
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
gsap.registerPlugin(ScrollTrigger);

export default {
    components: {
        Head,
        ResponsiveNavLink,
        menuDashboard,
        LayoutBackground,
        RSSNews,
    },
    props: {
        canLogin: Boolean,
        canRegister: Boolean,
        laravelVersion: String,
        phpVersion: String,
        title: String,
    },
    data() {
        return {
            feedItems: [],
            loading: true,
            error: null
        };
    },
    mounted() {
    },
    setup() {
        const menuBar = ref(null);
        const rightMenu = ref(null);
        const heroText = ref(null);
        const mainBody = ref(null);

        const logout = () => {
            console.log("logout");
            router.post(route("logout"));
        };

        const nav = ref(null)
        const placeholderHeight = ref(0)
        let animation

        const setupAnimation = () => {
            // 配置 GSAP 时间轴
            animation = gsap.timeline({
                paused: true,
                defaults: { 
                duration: 0.6,
                ease: "power4.out" 
                }
            })
            
            // 定义缩小状态动画
            animation.to(nav.value, {
                height: "4rem",
                boxShadow: "0 4px 6px -1px rgb(0 0 0 / 0.1)"
            }, 0)
        }

        const handleScroll = () => {
            const progress = Math.min(window.scrollY / 100, 1)
            animation.progress(progress)
        }
        // 佔 nav 的高度
        const updateHeight = () => {
            placeholderHeight.value = nav.value?.offsetHeight
        }
        
        onMounted(() => {
            setupAnimation()
            updateHeight()
            window.addEventListener('resize', updateHeight)
            window.addEventListener('scroll', handleScroll, { passive: true })

            gsap.from(
                menuBar.value, {
                    x: 150,
                    duration: 0.5,
                    opacity: 0
                }
            );
        });

        return {
            nav,
            placeholderHeight,
            menuBar,
            rightMenu,
            heroText,
            mainBody,
            logout,
        };
    },
    
};
</script>

<template>
<!-- component -->
<!-- Header -->
<header class="">

    <!-- navbar and menu -->
    <nav  ref="nav" class="fixed w-full z-50 transition-all duration-300 flex "
        :style="{ 'background-image': 'linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url(/storage/images/layout_background.jpg)' }">

        <div class="flex justify-between items-center py-6 px-10 container mx-auto">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="/"><img src="/storage/images/site_logo.png" class="block h-14 w-auto" /></a>
                </div>
                <h1 class="ml-2 text-2xl font-bold">
                    <a href="/" class="hidden md:block text-white">Atbest</a>
                </h1>
            </div>

            <menuDashboard />
        </div>

        <!-- Responsive Navigation Menu -->
        <!-- <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden bg-white">
            <div class="pt-2 pb-3 space-y-1">
                <ResponsiveNavLink :href="route('host')" :active="route().current('dashboard')">
                    {{ $t('dashboard') }}
                </ResponsiveNavLink>
            </div>
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="mt-3 space-y-1">
                    <ResponsiveNavLink :href="route('member.dashboard')" :active="route().current('member.dashboard')">
                        {{ $t("member_dashboard") }}
                    </ResponsiveNavLink>
                    
                    <template v-if="$page.props.user">
                        <form method="POST" @submit.prevent="logout">
                            <ResponsiveNavLink as="button"> {{ $t("log_out") }} </ResponsiveNavLink>
                        </form>
                    </template>
                    <template v-else>
                        <a :href="route('login')">
                            <ResponsiveNavLink as="button"> {{ $t('login') }}</ResponsiveNavLink>
                        </a>
                        <a :href="route('register')">
                            <ResponsiveNavLink as="button"> {{ $t("register") }} </ResponsiveNavLink>
                        </a>
                    </template>
                </div>
            </div>
        </div> -->
    </nav>
     <!-- 动态占位元素 -->
    <div class="bg-gray-200" :style="{ height: placeholderHeight + 'px' }"  ></div>
</header>
<!-- Page Heading -->
<div v-if="$slots.header" >
    <div class="mx-auto max-w-7xl bg-white h-12 flex items-center py-2 ">
        <slot name="header" />
    </div>
</div>

<main class="">

    <LayoutBackground />

    <section class=""  >
        <div 
            class="bg-slate-200 relative min-h-screen p-0 lg:p-4 pt-2">
            
            <!-- 主內容區 -->
            <div ref="mainBody" class="main-body relative z-10 grid grid-cols-1 lg:grid-cols-5 md:gap-6">
                <!-- 左側主內容 -->
                
                <div class="col-span-4 ">
                    <!-- Page Content -->
                    <main>
                        <slot />
                    </main>
                </div>

                <!-- 右邊欄 -->
                <div ref="rightMenu" class="col-span-1 flex flex-col gap-4">

                    <div class="bg-slate-50/80 py-3 px-4 rounded-lg flex justify-around items-center shadow">
                        <input type="text" :placeholder="$t('search')" class="bg-gray-100 rounded-md outline-none pl-2 ring-indigo-700 w-full mr-2 p-2" />
                        <span><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor ">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                    </div>
                    <div class="bg-white backdrop-blur-sm shadow-md rounded-md">
                        <h1 class="text-center text-xl my-4 py-2 rounded-md border-b-2 cursor-pointer text-gray-600">
                            {{ $t('service') }}
                        </h1>
                        <div class="rounded-md list-none text-center">
                            <li class="py-3 border-b-2">
                                <inertia-link :href="route('forms')">報名表格</inertia-link>
                            </li>
                            <li class="py-3 border-b-2">
                                <inertia-link :href="route('events')">活動列表</inertia-link>
                            </li>
                            <li class="py-3 border-b-2">
                                <a href="https://www.gcs.gov.mo" target="_blank" class="list-none hover:text-indigo-600">新聞局</a>
                            </li>
                            <li class="py-3 border-b-2">
                                <a href="https://www.io.gov.mo/cn/home/" class="list-none hover:text-indigo-600" target="_blank">印務局</a>
                            </li>
                        </div>
                    </div>
                    
                    <RSSNews />
                </div>
            </div>
        </div>
    </section>
</main>
</template>

<style scoped>
.bg-primary {
    --bg-opacity: 1;
    background-color: #ff69b4;
    background-color: rgba(255, 105, 180, var(--bg-opacity));
}

.bg-secondary {
    --bg-opacity: 1;
    background-color: #333333;
    background-color: rgba(51, 51, 51, var(--bg-opacity));
}

.bg-brand {
    --bg-opacity: 1;
    background-color: #243c5a;
    background-color: rgba(36, 60, 90, var(--bg-opacity));
}

/* 自定義滾動行為 */
nav {
  will-change: height, background;
  transform: translateZ(0);
}

.router-link-active {
  @apply text-blue-600 font-medium;
}

</style>