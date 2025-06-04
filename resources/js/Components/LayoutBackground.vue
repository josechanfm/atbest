<template>
<div class="fixed inset-0 z-10 w-full h-screen overflow-hidden bg-gradient-to-b from-indigo-50 to-purple-100">
    <!-- 多層雲朵 -->
    <div v-for="(cloud, index) in clouds" :key="index" class="absolute rounded-full" :class="`cloud-layer-${cloud.layer}`" :style="{
          width: `${cloud.size}px`,
          height: `${cloud.size}px`,
          left: `${cloud.x}px`,
          top: `${cloud.y}px`,
          backgroundColor: cloud.color,
        }" ref="cloudElements"></div>
</div>
</template>

  
<script setup>
import {
    ref,
    onMounted
} from 'vue'
import {
    gsap
} from 'gsap'

// 相似色系 (藍紫粉漸變)
const colorPalette = [
    '#f0f4f8', '#e5eef7', '#dbe6f0', '#d1dce8', // 冷調淺灰藍
    '#e8f0fe', '#e0e7ff', '#d7e1f9', '#ced8f2', // 微微藍調
    '#f5f7fa', '#ebf0f5', '#e1e8ed', '#d7e0e6'  // 中性淺灰帶藍
]

// 雲朵數據 (分3層)
const clouds = ref([
    // 第一層 (大雲朵，移動慢，高模糊)
    ...Array(5).fill().map((_, i) => ({
        size: 1000 + Math.random() * 300,
        x: Math.random() * window.innerWidth,
        y: Math.random() * window.innerHeight,
        color: colorPalette[Math.floor(Math.random() * 4)],
        speedX: 0.1 + Math.random() * 0.1,
        speedY: 0.05 + Math.random() * 0.05,
        layer: 1
    })),

    // 第二層 (中等雲朵，移動中等，中等模糊)
    ...Array(8).fill().map((_, i) => ({
        size: 300 + Math.random() * 200,
        x: Math.random() * window.innerWidth,
        y: Math.random() * window.innerHeight,
        color: colorPalette[3 + Math.floor(Math.random() * 4)],
        speedX: 0.3 + Math.random() * 0.2,
        speedY: 0.2 + Math.random() * 0.1,
        layer: 2
    })),

    // 第三層 (小雲朵，移動快，低模糊)
    ...Array(12).fill().map((_, i) => ({
        size: 150 + Math.random() * 150,
        x: Math.random() * window.innerWidth,
        y: Math.random() * window.innerHeight,
        color: colorPalette[6 + Math.floor(Math.random() * 4)],
        speedX: 0.5 + Math.random() * 0.3,
        speedY: 0.3 + Math.random() * 0.2,
        layer: 3
    }))
])

const cloudElements = ref([])

// 動畫函數
const animateClouds = () => {
    cloudElements.value.forEach((cloudEl, index) => {
        const cloud = clouds.value[index]

        // 主移動動畫
        gsap.to(cloudEl, {
            x: `+=${200 * cloud.speedX}`,
            y: `+=${100 * cloud.speedY}`,
            duration: 7 + Math.random() * 8,
            ease: 'sine.inOut',
            repeat: -1,
            yoyo: true
        })

        // 副動畫效果
        gsap.to(cloudEl, {
            scale: 1 + (0.2 * (1 / cloud.layer)),
            opacity: 0.3 + Math.random() * 0.5,
            duration: 5 + Math.random() * 8,
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut'
        })
    })
}

onMounted(() => {
    animateClouds()

    // 響應式調整
    window.addEventListener('resize', () => {
        clouds.value = clouds.value.map(cloud => ({
            ...cloud,
            x: Math.random() * window.innerWidth,
            y: Math.random() * window.innerHeight
        }))
    })
})
</script>

  
<style scoped>
/* 基礎樣式 */
body,
html {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
}

/* 雲朵層級模糊效果 */
.cloud-layer-1 {
    filter: blur(60px);
    opacity: 0.7;
    z-index: 1;
}

.cloud-layer-2 {
    filter: blur(45px);
    opacity: 0.8;
    z-index: 2;
}

.cloud-layer-3 {
    filter: blur(30px);
    opacity: 0.9;
    z-index: 3;
}

/* 動畫性能優化 */
.cloud-layer-1,
.cloud-layer-2,
.cloud-layer-3 {
    will-change: transform, opacity;
    transform: translateZ(0);
}
</style>
