<template>
<div class=" text-white relative">
    
    <div class="flex md:hidden -mr-2 items-center sm:hidden relative z-40">

        <a ref="bubbleHead" class="bubbleHead z-30 inline-flex bg-white text-gray-800 justify-center items-center rounded-full" 
                @click="showingNavigation()">
            <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown, }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>
    <div class="block sm:hidden md:hidden absolute top-0 z-10">
        
        <div ref="bubble1" target="_blank" href="http://www.faom.org.mo/portal/" 
            class="absolute bubble flex justify-center items-center bg-green-600 rounded-full " >
            <a  class=" text-white hover:text-yellow-300 text-md">工聯</a>
        </div>
        <div ref="bubble2" href="https://www.mo.gov.mo" 
            class="absolute bubble flex justify-center items-center bg-amber-600 rounded-full" >
            <a target="_blank" class=" text-white hover:text-yellow-300 text-md">一戶通</a>
        </div>
        <div ref="bubble3"
            class="absolute bubble text-white flex justify-center items-center bg-sky-600 rounded-full " >
            <template v-if="$page.props.user">
                <a :href="route('member.dashboard')" target="_blank" class="tracking-tight font-serif text-white hover:text-yellow-300 text-md">Member</a>
            </template>
            <template v-else>
                <a class="cursor-pointer font-serif hover:text-yellow-300 text-md" @click="login">{{ $t("login") }}</a>
            </template>
        </div>
        
        <div ref="bubble4" 
            class="text-sm absolute bubble text-white flex justify-center items-center bg-rose-600 rounded-full " >
            <template v-if="$page.props.user">
                <a class="cursor-pointer font-serif hover:text-yellow-300 text-md" @click="logout">{{ $t("log_out") }}</a>
            </template>
            <template v-else>
                <a class="cursor-pointer font-serif hover:text-yellow-300 text-md" @click="register">{{ $t("register") }}</a>
            </template>
        </div>
    </div>

    <div class="flex items-center" ref="menuBar">
        <ul class="list-none sm:flex space-x-4 hidden items-center text-white">
            <li>
                <a href="http://www.faom.org.mo/portal/" target="_blank" class="text-bold text-white hover:text-yellow-300 text-md">工聯</a>
            </li>
            <li>
                <a href="https://www.mo.gov.mo" target="_blank" class="text-bold text-white hover:text-yellow-300 text-md">一戶通</a>
            </li>
            <template v-if="$page.props.user">
                <li>
                    <a :href="route('member.dashboard')" target="_blank" class="text-bold text-white hover:text-yellow-300 text-md">{{ $t("member") }}</a>
                </li>
                <li>
                    <a class="cursor-pointer text-bold text-white hover:text-yellow-300 text-md" @click="logout">{{ $t("log_out") }}</a>
                </li>
            </template>
            <template v-else>
                <li>
                    <inertia-link :href="route('login')" class="text-bold text-white hover:text-yellow-300 text-md">{{
                    $t("login") }}</inertia-link>
                </li>
                <li>
                    <inertia-link :href="route('register')" class="text-bold text-white hover:text-yellow-300 text-md">{{ $t("register") }}</inertia-link>
                </li>
            </template>
        </ul>
    </div>
</div>
</template>


<script setup>
import {  ref, onMounted } from 'vue';
import { gsap } from 'gsap';
import { router } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const bubbleHead = ref(null);
const bubble1 = ref(null);
const bubble2 = ref(null);
const bubble3 = ref(null);
const bubble4 = ref(null);
const scrollY = ref(0)
const isScrollingDown = ref(false)
let lastScrollY = 0

let navigationTimeline = gsap.timeline({ reversed: true })
    
onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true })

    setBubbleAnimation()
})

const handleScroll = () => {
  scrollY.value = window.scrollY || window.pageYOffset
  
  // 檢測滾動方向
  isScrollingDown.value = scrollY.value > lastScrollY
  lastScrollY = scrollY.value
  
  if (scrollY.value > 0 && showingNavigationDropdown.value ) {

    showingNavigation()
  }
}

const logout = () => {
    router.post(route("logout"));
};
const register = () => {
    window.location.href = route('register')
};
const login = () => {
    window.location.href = route('login')
};

function showingNavigation(){
    showingNavigationDropdown.value = !showingNavigationDropdown.value

    showingAnimiation()
}

function showingAnimiation(){
    navigationTimeline.reversed(!navigationTimeline.reversed())
}

function setBubbleAnimation(){
    navigationTimeline.add('bubble')
        .to( bubbleHead.value, { duration: 1, scale:0.8, ease: "elastic.out(1,0.3)", delay: 0.4 }, 'bubble' ) 
        .fromTo(bubble1.value, { x:0, y:0, opacity:0.7, scale:0.9 }, 
            { x: -100, y: -20, zIndex: 30 , duration: 1, opacity:1, scale:1 ,delay: 0.2, ease: "elastic.out(1,0.5)", }, 'bubble')
        .fromTo(bubble2.value, { x:0, y:0, opacity:0.7, scale:0.9 }, 
            { x: -60, y: 60, zIndex: 30, duration: 1, opacity:1, scale:1 ,delay: 0.4, ease: "elastic.out(1,0.5)", }, 'bubble')
        .fromTo(bubble3.value, { x:0, y:0, opacity:0.7, scale:0.9 }, 
            { x: 20, y: 100, zIndex: 30, duration: 1, opacity:1, scale:1 ,delay: 0.6, ease: "elastic.out(1,0.5)", }, 'bubble')
        .fromTo(bubble4.value, { x:0, y:0, opacity:0.7, scale:0.9 }, 
            { x: -150, y: 50, zIndex: 30, duration: 1, opacity:1, scale:1 ,delay: 0.4, ease: "elastic.out(1,0.5)", }, 'bubble')
}

defineExpose({

})
</script>

<style>
.bubble{
    @apply w-14 h-14 md:w-16 md:h-16;
    /* width: 4.5rem;
    height: 4.5rem; */
    box-shadow: rgba(0, 0, 0, 0.15) 4px 4px 6px;
}
.bubbleHead{
    @apply w-14 h-14 md:w-16 md:h-16;
    box-shadow: rgba(0, 0, 0, 0.15) 4px 4px 6px;
}
</style>