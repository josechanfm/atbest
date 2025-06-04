<template>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-16 p-6 relative">
        <!-- 卡片1 -->
        <div ref="card1" class="card min-h-20" @mouseenter="animateCardIn('card1', 'blue')" @mouseleave="animateCardOut('card1')">
            <div class="card-title text-blue-300">介紹社團業務</div>
            <div class="card-content">為社團提供介紹其業務及展示其歷史的網站，充當社團面向大眾的資訊平台</div>
        </div>
    
        <!-- 卡片2 -->
        <div ref="card2" class="card" @mouseenter="animateCardIn('card2', 'purple')" @mouseleave="animateCardOut('card2')">
            <div class="card-title text-purple-300">會員資訊端</div>
            <div class="card-content">提供會員專屬入口，可查看個人資料、活動記錄，並進行線上報名、意見反饋等互動功能，增強會員參與感</div>
        </div>
    
        <!-- 卡片3 -->
        <div ref="card3" class="card" @mouseenter="animateCardIn('card3', 'emerald')" @mouseleave="animateCardOut('card3')">
            <div class="card-title text-emerald-300">行政管理端</div>
            <div class="card-content">後台管理系統整合會員資料管理、活動籌辦、財務收支追蹤及報表生成功能，提升社團運作效率與決策品質</div>
        </div>
    </div>
    </template>
    
    <script setup>
    import { ref, onMounted } from 'vue'
    import gsap from 'gsap'
    
    // 卡片引用
    const card1 = ref(null)
    const card2 = ref(null)
    const card3 = ref(null)
    
    // 顏色映射表
    const colorMap = {
        blue: '#2563eb', // blue-600
        purple: '#9333ea', // purple-600
        emerald: '#059669' // emerald-600
    }
    
    // 卡片進入動畫
    const animateCardIn = (cardRef, colorType) => {
        const card = eval(cardRef).value
        
        gsap.killTweensOf(card)
        
        gsap.timeline()
            .to(card, {
                y: -10,
                scale: 1.05,
                boxShadow: `0 25px 50px -12px ${colorMap[colorType]}40`,
                borderColor: colorMap[colorType],
                duration: 0.3,
                ease: 'power2.out'
            })
            .to(card.querySelector('.card-title'), {
                color: colorMap[colorType],
                duration: 0.2
            }, 0)
    }
    
    // 卡片離開動畫
    const animateCardOut = (cardRef) => {
        const card = eval(cardRef).value
        
        gsap.killTweensOf(card)
        
        gsap.timeline()
            .to(card, {
                y: 0,
                scale: 1,
                boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)',
                borderColor: '#4b5563', // slate-600
                duration: 0.3,
                ease: 'power2.out'
            })
    }
    </script>
    
    <style scoped>
    /* 基礎卡片樣式 */
    .card {
        @apply relative z-10 flex flex-col gap-4 text-center backdrop-blur-sm bg-gray-100/70 p-6 md:px-12 rounded-lg shadow-xl border border-slate-600 overflow-hidden;
        transition: all 0.3s ease;
        will-change: transform, box-shadow, border-color;
    }
    
    .card-title {
        @apply text-xl transition-colors duration-300 font-bold;
    }
    
    .card-content {
        @apply text-lg text-gray-800 font-medium ;
    }
    </style>