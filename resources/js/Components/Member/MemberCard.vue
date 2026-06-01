<template>
  <div class="container mx-auto pt-12 flip-card-container" @click="flipCardAnimation">
    <div class="bg-white relative shadow rounded-lg flip-card hover:scale-105 transform transition-transform" ref="flipCard">
      <!-- Front side -->
      <div class="flip-card-front absolute inset-0">
        <div class="absolute inset-0 rounded-lg overflow-hidden">
          <img :src="'/storage/images/' + cardStyle.background" class="object-cover w-full h-full" />
          <div class="absolute inset-0 bg-black/30 backdrop-brightness-95"></div>
        </div>

        <div :style="cardStyle.font_style" class="relative z-10 flex flex-col h-full p-5 text-white">
          <div class="text-center text-lg font-bold">
            {{ member.organization["name_" + $t("lang")] }}
          </div>

          <div class="flex justify-between items-center mt-4 gap-4">
            <div class="flex-1 space-y-2">
              <div>
                <div class="text-xs opacity-80">姓名</div>
                <div class="font-bold text-base">{{ member.family_name }}{{ member.given_name }}</div>
              </div>
              <div>
                <div class="text-xs opacity-80">會員編號</div>
                <div class="font-mono font-bold tracking-wider">{{ member.member_number }}</div>
              </div>
            </div>

            <div class="flex-shrink-0">
              <img v-if="member.avatar" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md" :src="member.avatar" />
              <img v-else class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md" src="/avatars/dummy-avatar.jpg" />
            </div>
          </div>

          <div class="flex justify-start gap-8 pt-2 text-xs">
            <div>
              <div class="opacity-80">發出日期</div>
              <div class="font-semibold">{{ member.valid_at }}</div>
            </div>
            <div>
              <div class="opacity-80">有效日期</div>
              <div class="font-semibold">{{ member.expired_at }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Back side -->
      <div class="flex flip-card-back z-10" ref="back">
        <div class="flex">
          <QRCodeVue3
            :key="qrcode"
            :value="qrcode"
            :image="logoUrl"
            :dotsOptions="{
              type: 'dots',
              color: '#26249a',
              gradient: {
                type: 'linear',
                rotation: 0,
                colorStops: [
                  { offset: 0, color: '#26249a' },
                  { offset: 1, color: '#26249a' },
                ],
              },
            }"
            :width="150"
            :height="150"
            :cornersSquareOptions="{ type: 'square', color: '#e00404' }"
            :cornersDotOptions="{ color: '#e00404' }"
          />
        </div>
        <div class="flex flex-col px-4">
          <div class="w-full">
            <h3 class="text-xl font-bold mb-4 text-center text-orange-700">會員卡</h3>
            <div class="space-y-3 text-sm">
              <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>專屬會員活動優先報名</span>
              </div>
              <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>使用二維碼累計積分</span>
              </div>
            </div>
            <div class="mt-6 text-xs text-center text-gray-600">
              <p>客服專線: 0000-0000</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Background image (duplicate? can be removed if not needed) -->
      <img class="relative object-cover w-full h-full rounded-lg z-0 shadow-lg" :src="'/storage/images/' + cardStyle.background" />
    </div>
  </div>
</template>

<script>
import axios from "axios";
import QRCodeVue3 from "qrcode-vue3";
import { gsap } from "gsap";
import { ref, onMounted, onBeforeUnmount } from "vue";

export default {
  name: "MemberCard",
  components: { QRCodeVue3 },
  props: {
    member: { type: Object, required: true },
    cardStyle: { type: Object, required: true },
    logoUrl: { type: String, default: "/storage/images/site_logo.png" },
  },
  setup(props) {
    const flipCard = ref(null);
    const front = ref(null);
    const back = ref(null);
    const isFlipped = ref(false);
    const qrcode = ref("");
    const interval = ref(null);

    const getQrcode = () => {
      axios.get(route("member.getQrcode")).then((response) => {
        qrcode.value = response.data;
      });
    };

    const onShowQrcode = () => {
      getQrcode();
      interval.value = setInterval(() => {
        getQrcode();
      }, 5000);
    };

    const flipCardAnimation = () => {
      const duration = 0.6;
      const ease = "back.out(1)";

      if (!isFlipped.value) {
        onShowQrcode();
        gsap
          .timeline()
          .to(flipCard.value, { rotationY: 180, duration, ease })
          .to(front.value, { opacity: 0, duration: duration / 2, ease: "power1.in" }, 0)
          .to(back.value, { opacity: 1, duration: duration / 2, ease: "power1.out" }, duration / 2);
      } else {
        clearInterval(interval.value);
        gsap
          .timeline()
          .to(flipCard.value, { rotationY: 0, duration, ease })
          .to(back.value, { opacity: 0, duration: duration / 2, ease: "power1.in" }, 0)
          .to(front.value, { opacity: 1, duration: duration / 2, ease: "power1.out" }, duration / 2);
      }
      isFlipped.value = !isFlipped.value;
    };

    onMounted(() => {
      gsap.set(back.value, { rotationY: -180 });
    });

    onBeforeUnmount(() => {
      if (interval.value) clearInterval(interval.value);
    });

    return {
      flipCard,
      front,
      back,
      qrcode,
      flipCardAnimation,
    };
  },
};
</script>

<style scoped>
.flip-card-container {
  perspective: 1000px;
  height: 250px;
  cursor: pointer;
}
.flip-card {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  transition: transform 0.6s;
}
.flip-card-front,
.flip-card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  align-items: center;
  justify-content: center;
}
.flip-card-front {
  color: white;
}
.flip-card-back {
  opacity: 0;
  transform: rotateY(-180deg);
}
</style>