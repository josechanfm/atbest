<template>
<div class=" text-white relative">
    
    <div class="flex md:hidden -mr-2 items-center sm:hidden relative z-40">

        <button ref="bubbleHead" type="button" class="bubbleHead z-30 inline-flex bg-white text-gray-800 justify-center items-center rounded-full"
                :aria-label="$t('navigation_menu')" :aria-expanded="showingNavigationDropdown" @click="showingNavigation()">
            <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown, }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="block sm:hidden md:hidden absolute top-0 z-10">
        
        <div v-for="(item, index) in menuItems" :key="index" :ref="el => bubbleRefs[index] = el"
            :class="['absolute bubble flex justify-center items-center rounded-full', bubbleColors[index]]"
            :inert="!showingNavigationDropdown">
            <button v-if="item.action" type="button" class="cursor-pointer font-serif text-white hover:text-yellow-300 text-md" @click="item.action">
                {{ $t(item.labelKey) }}
            </button>
            <component v-else :is="item.external ? 'a' : Link" :href="item.href"
                :target="item.external ? '_blank' : undefined" :rel="item.external ? 'noopener noreferrer' : undefined"
                class="font-serif text-white hover:text-yellow-300 text-md">
                {{ $t(item.labelKey) }}
            </component>
        </div>
    </div>

    <div class="flex items-center" ref="menuBar">
        <ul class="list-none sm:flex space-x-4 hidden items-center text-white">
            <li v-for="item in menuItems" :key="item.id">
                <button v-if="item.action" type="button" class="cursor-pointer text-bold text-white hover:text-yellow-300 text-md" @click="item.action">
                    {{ $t(item.labelKey) }}
                </button>
                <component v-else :is="item.external ? 'a' : Link" :href="item.href"
                    :target="item.external ? '_blank' : undefined" :rel="item.external ? 'noopener noreferrer' : undefined"
                    class="text-bold text-white hover:text-yellow-300 text-md">
                    {{ $t(item.labelKey) }}
                </component>
            </li>
        </ul>
    </div>
</div>
</template>


<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { gsap } from 'gsap';
import { Link, router, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const bubbleHead = ref(null);
const bubbleRefs = [];
const bubbleColors = ['bg-green-600', 'bg-amber-600', 'bg-sky-600', 'bg-rose-600'];
const page = usePage();
const menuItems = computed(() => [
    { id: 'gazette', labelKey: 'home_menu.gazette', href: 'https://www.bo.dsaj.gov.mo/cn/bo/', external: true },
    { id: 'one-account', labelKey: 'home_menu.one_account', href: 'https://www.mo.gov.mo', external: true },
    ...(page.props.auth?.user
        ? [
            { id: 'member', labelKey: 'home_menu.member', href: route('member.dashboard'), external: true },
            { id: 'logout', labelKey: 'home_menu.logout', action: logout },
        ]
        : [
            { id: 'login', labelKey: 'home_menu.login', href: route('login') },
            { id: 'register', labelKey: 'home_menu.register', href: route('register') },
        ]),
]);
const scrollY = ref(0)
const isScrollingDown = ref(false)
let lastScrollY = 0

let navigationTimeline = gsap.timeline({ reversed: true })
    
onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true })

    setBubbleAnimation()
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    navigationTimeline.kill();
});

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
        .fromTo(bubbleRefs[0], { x:0, y:0, opacity:0.7, scale:0.9 }, 
            { x: -100, y: -20, zIndex: 30 , duration: 1, opacity:1, scale:1 ,delay: 0.2, ease: "elastic.out(1,0.5)", }, 'bubble')
        .fromTo(bubbleRefs[1], { x:0, y:0, opacity:0.7, scale:0.9 }, 
            { x: -60, y: 60, zIndex: 30, duration: 1, opacity:1, scale:1 ,delay: 0.4, ease: "elastic.out(1,0.5)", }, 'bubble')
        .fromTo(bubbleRefs[2], { x:0, y:0, opacity:0.7, scale:0.9 }, 
            { x: 20, y: 100, zIndex: 30, duration: 1, opacity:1, scale:1 ,delay: 0.6, ease: "elastic.out(1,0.5)", }, 'bubble')
        .fromTo(bubbleRefs[3], { x:0, y:0, opacity:0.7, scale:0.9 }, 
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