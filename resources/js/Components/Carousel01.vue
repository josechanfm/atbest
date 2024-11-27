html

Copy
<template>
  <div class="relative">
    <div class="overflow-x-hidden">
      <div class="flex transition-transform duration-500 ease-in-out" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
        <div v-for="(slide, index) in slides" :key="index" class="shrink-0 w-full">
          <img :src="slide.image" :alt="slide.title" class="w-full h-96 object-cover" />
        </div>
      </div>
    </div>
    <div class="absolute top-1/2 transform -translate-y-1/2 left-4 cursor-pointer" @click="prevSlide">
      <svg class="w-8 h-8 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
      </svg>
    </div>
    <div class="absolute top-1/2 transform -translate-y-1/2 right-4 cursor-pointer" @click="nextSlide">
      <svg class="w-8 h-8 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      currentIndex: 0,
      slides: [
        { image: '/images/800x400.png', title: 'Slide 1' },
        { image: '/images/800x400.png', title: 'Slide 2' },
        { image: '/images/800x400.png', title: 'Slide 3' },
        { image: '/images/800x400.png', title: 'Slide 4' },
      ],
    };
  },
  methods: {
    prevSlide() {
      this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
    },
    nextSlide() {
      this.currentIndex = (this.currentIndex + 1) % this.slides.length;
    },
  },
};
</script>