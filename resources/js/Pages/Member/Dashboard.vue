<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import ThumbList from "@/Components/ThumbList.vue";
import axios from "axios";
import QRCodeVue3 from "qrcode-vue3";
import { gsap } from 'gsap';

export default {
  components: {
    MemberLayout,
    ThumbList,
    QRCodeVue3,
  },
  props: ["member","members", "features", "forms", "articles", "cardStyle"],
  data() {
    return {
      qrcode: "",
      interval: 0,
      defaultFeatures: [
        {
          image: "/images/features/1_news_events.png",
          title_zh: "新聞與活動",
          content_zh: "新聞部分是用來發布組織的最新消息和事件，向公眾展示其在社會和環境領域中所做的工作，並提高對其使命的認識和理解。",
          tags: ["#通告"],
          link: "/",
        },
        {
          image: "/images/features/2_form_application.png",
          title_zh: "表格及報名",
          content_zh: "包括各種類型的表格和報名表，方便訪問者提交相關資訊並表達他們的參與意願。",
          tags: ["#報名"],
          link: "forms",
        },
        {
          image: "/images/features/3_learn_share.png",
          title_zh: "學習興分享",
          content_zh: "提供教育資源、知識分享和學習機會的區域。提供有價值的學習內容，並促進知識交流和社群互動。",
          tags: ["#學習", "#分享"],
          link: "#",
        },
        {
          image: "/images/features/4_community.png",
          title_zh: "虛擬社區",
          content_zh: "交流和參與組織活動的線上平台。讓志願者、支持者和參與者能夠連結起來，分享資源、經驗和想法。",
          tags: ["#學習", "#交流"],
        },
      ],
      data: [
        {
          title: "News",
          url: "./",
          content: "Competition ABC is now open for registration",
        },
        {
          title: "Ant Design Title 2",
          url: "",
          content: "Competition ABC is now open for registration",
        },
        {
          title: "Ant Design Title 3",
          url: "",
          content: "Competition ABC is now open for registration",
        },
        {
          title: "Ant Design Title 4",
          url: "",
          content: "Competition ABC is now open for registration",
        },
      ],
      showQrcode: false,
      qrcodeLogo:'/storage/images/site_logo.png'
    };
  },
  created() {

    if(this.features.length>0){
      this.defaultFeatures=this.features
    }
    if(this.cardStyle.logo){
      this.qrcodeLogo='/storage/images/'+this.cardStyle.logo
    }else if(this.$page.props.member.organization.logo){
      this.qrcodeLogo=this.$page.props.member.organization.logo
    }
  },
  mounted() { 

    this.features.forEach((feature, index) => {
      const duration = 0.5 + ( index / 4 ); 
      gsap.fromTo(
        `.feature:nth-child(${index + 1})`,
        { x: '100%', opacity: 0 }, // 從右側進入
        {
          x: '0%',   // 移動到原位
          opacity: 1, // 透明度變為 1
          duration: index == 0 ? 0.5 : duration, // 動畫持續時間
        }
      );
    });

  },
  methods: {
    getQrcode() {
      axios.get(route("member.getQrcode")).then((response) => {
        this.qrcode = response.data;
      });
    },
    onShowQrcode() {
      this.showQrcode = !this.showQrcode;
      if (this.showQrcode) {
        this.getQrcode();
        this.interval = setInterval(() => {
          this.getQrcode();
        }, 10000);
      } else {
        clearInterval(this.interval);
      }
    },
    switchOrganization(member) {
      this.$inertia.post(route('member.membership.switch', { member: member.id }))
      //this.member=this.members.find(m=>m.id==member.id)
    }

  },
};
</script>

<template>
  <MemberLayout title="Dashboard">
    <div class="container mx-auto">
      <div class="flex flex-col-reverse md:flex-row gap-6 ">
        <div class="flex-auto">
          <!-- Feature Section -->
          <div class="container mx-auto my-5 bg-slate-200 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 py-3 px-2">
              <div class="feature bg-white" v-for="feature in defaultFeatures">
                <a :href="feature.link">
                  <div ref="feature_content" class="gutter-row">
                    <div class="max-w rounded overflow-hidden shadow-lg">
                      <img class="w-full" alt="Use any sample image here..." :src="feature.image">
                      <!-- <img class="w-full min-h-10" alt="Use any sample image here..." src="https://picsum.photos/200/300"> -->
                      <div  class="px-2 py-4 xs:h-64 lg:h-48">
                        <inertia-link v-if="feature.url" :href="feature.url">
                          <div class="font-bold text-xl mb-2">{{ feature.title_zh }}</div>
                        </inertia-link>
                        <inertia-link v-else :href="route('article.item', { t: feature.uuid })" target="_blank">
                          <div class="font-bold text-xl mb-2">{{ feature.title_zh }}</div>
                        </inertia-link>
                        <p class="text-gray-700 text-base pl-1">
                          {{ feature.content_zh }}
                        </p>
                      </div>
                      <div class="px-6 py-4">
                        <span v-for="tag in feature.tags_zh"
                          class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2">{{ tag }}</span>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>
          <!-- Feature Section end-->

          <ol class="list-disc">
            <li v-for="form in forms">
              <inertia-link :href="route('form.item', {t:form.uuid})">{{ form.title }}</inertia-link>
            </li>
          </ol>
          <!-- Body List -->
          <!-- ------------ -->
          <!-- News Section-->
          <div class="container mx-auto">
            <ThumbList :records="articles" routePath="article.item"/>
          </div>
          <!-- News Section end-->
        </div>

        <div class="flex-none w-[400px]">
          <div class="container mx-auto pt-5">
            <div class="bg-white relative shadow rounded-lg">
              <!-- QRcode -->
              <div class="flex flex-col justify-center items-center" v-if="showQrcode">
                <div>
                  <QRCodeVue3 :key="qrcode" v-bind:value="qrcode" :image="qrcodeLogo" 
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
                    }" :cornersSquareOptions="{
                      type: 'square',
                      color: '#e00404'
                    }" :cornersDotOptions="{
                      color: '#e00404'
                    }" 
                  />
                </div>
              </div>
              <!-- card start -->
              <div class="mx-auto relative py-4 w-96 hover:scale-105 transform transition-transform mb-4">
                <div :style="cardStyle['font_style']"
                  class="absolute z-10 flex rounded-lg flex-col m-4 text-sm w-[350px]"
                  @click="onShowQrcode">
                  <div class="flex flex-col w-xl">
                    <div class="flex justify-center">
                      <div class="text-lg font-bold">{{ member.organization['name_' + $t('lang')] }}</div>
                    </div>
                    <div class="flex">
                      <div class="flex flex-col flex-auto gap-1">
                        <div class="">姓名：</div>
                        <div class="mb-2">{{ member.family_name }}{{ member.given_name }}</div>
                        <div class="">會員編號：</div>
                        <div class="font-sans mb-2">{{ member.member_number }}</div>
                      </div>
                      <div class="flex">
                        <img v-if="member.avatar" class="w-20 h-20" :src="member.avatar" />
                        <img v-else class="w-20 h-20" src="/avatars/dummy-avatar.jpg" />
                      </div>
                    </div>
                    <div class="flex text-xs">
                      <div class="flex flex-colgap-1 flex-auto">
                        <div class="">發出日期：</div>
                        <div class="font-sans text-base">{{ member.valid_at }}</div>
                      </div>
                      <div class="flex flex-col gap-1 flex-auto">
                        <div class="">有效日期：</div>
                        <div class="font-sans text-base">{{ member.expired_at }}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <img class="relative object-cover w-full h-52 rounded-lg z-0"
                  :src="'/storage/images/' + cardStyle['background']" />
              </div>
              <!-- card end -->

              <div class="mt-16">
                <h1 class="font-bold text-center text-3xl text-gray-900">
                  {{ member.family_name }}{{ member.given_name }}
                </h1>
                <h1 class="font-bold text-center text-2xl text-gray-900">
                  {{ member.display_name }}
                </h1>
                <p class="text-center text-sm text-gray-400 font-medium">
                  {{ member.organization['name_' + $t('lang')] }}
                </p>
                <p>
                  <span> </span>
                </p>
                <div class="my-5 px-6">
                  <a href="#"
                    class="text-gray-200 block rounded-lg text-center font-medium leading-6 px-6 py-3 bg-gray-900 hover:bg-black hover:text-white">Connect
                    with <span class="font-bold">{{ member.email }}</span></a>
                </div>
                <div class="flex justify-between items-center my-5 px-6">
                  <a href="#"
                    class="text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition duration-150 ease-in font-medium text-sm text-center w-full py-3">Facebook</a>
                  <a href="#"
                    class="text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition duration-150 ease-in font-medium text-sm text-center w-full py-3">Twitter</a>
                  <a href="#"
                    class="text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition duration-150 ease-in font-medium text-sm text-center w-full py-3">Instagram</a>
                  <a href="#"
                    class="text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition duration-150 ease-in font-medium text-sm text-center w-full py-3">Email</a>
                </div>
                <div class="w-full">
                  <div v-if="members.length > 1">
                    <h3 class="font-medium text-gray-900 text-left px-6">Your Organizations</h3>
                    <div class="mt-5 w-full flex flex-col items-center overflow-hidden text-sm">
                      <template v-for="member in members">
                        <a href="#"
                          class="w-full border-t bor  der-gray-100 text-gray-600 py-4 pl-6 pr-3 w-full block hover:bg-gray-100 transition duration-150">
                          <img src="https://avatars0.githubusercontent.com/u/35900628?v=4" alt=""
                            class="rounded-full h-6 shadow-md inline-block mr-2" />
                          {{ member.organization.abbr }} - {{ member.organization['name_' + $t('lang')] }}
                          <span class="text-gray-500 text-xs float-right pr-2" v-if="members.length > 1">
                            <a @click="switchOrganization(member)">{{ $t('organization_switch') }}</a>
                          </span>
                        </a>
                      </template>
                    </div>
                  </div>

                  <h3 class="font-medium text-gray-900 text-left px-6">Recent updates</h3>
                  <div class="mt-5 w-full flex flex-col items-center overflow-hidden text-sm">
                    <template v-for="portfolio in member.portfolios">
                      <a href="#"
                        class="w-full border-t border-gray-100 text-gray-600 py-4 pl-6 pr-3 w-full block hover:bg-gray-100 transition duration-150">
                        <img src="https://avatars0.githubusercontent.com/u/35900628?v=4" alt=""
                          class="rounded-full h-6 shadow-md inline-block mr-2" />
                          {{ portfolio.title }} - {{ portfolio.description }}
                        <span class="text-gray-500 text-xs">{{ portfolio.start_date }}</span>
                      </a>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </MemberLayout>
</template>

<style scope>
#pure-html {
  all: initial;
}

#pure-html * {
  all: revert;
}
</style>