<template>
  <MemberLayout title="Dashboard">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col-reverse md:flex-row gap-6">
        <!-- Left column: features, forms, news -->
        <div class="flex-auto flex flex-col-reverse md:flex-col">
          <!-- Feature Section -->
          <div
            v-if="features.length > 0"
            class="px-4 mx-auto my-5 bg-slate-200 rounded-lg"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 py-3 px-2">
              <div
                v-for="(feature, i) in features"
                :key="i"
                class="feature bg-white"
              >
                <a :href="feature.link">
                  <div class="gutter-row">
                    <div class="max-w rounded overflow-hidden shadow-lg">
                      <img class="w-full" :src="feature.image" alt="" />
                      <div class="px-2 py-4 xs:h-64 lg:h-48">
                        <inertia-link
                          v-if="feature.url"
                          :href="feature.url"
                        >
                          <div class="font-bold text-xl mb-2">
                            {{ feature.title_zh }}
                          </div>
                        </inertia-link>
                        <inertia-link
                          v-else
                          :href="route('article.item', { t: feature.uuid })"
                          target="_blank"
                        >
                          <div class="font-bold text-xl mb-2">
                            {{ feature.title_zh }}
                          </div>
                        </inertia-link>
                        <p class="text-gray-700 text-base pl-1">
                          {{ feature.content_zh }}
                        </p>
                      </div>
                      <div class="px-6 py-4">
                        <span
                          v-for="(tag, idx) in feature.tags_zh"
                          :key="idx"
                          class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2"
                        >
                          {{ tag }}
                        </span>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>

          <!-- Forms list -->
          <ol class="list-disc">
            <li v-for="form in forms" :key="form.id">
              <inertia-link :href="route('form.item', { t: form.uuid })">
                {{ form.title }}
              </inertia-link>
            </li>
          </ol>

          <!-- News Section -->
          <div class="container mx-auto">
            <ThumbList :records="articles" routePath="article.item" />
          </div>
        </div>

        <!-- Right column: Member Card + Profile Sidebar -->
        <div class="flex-none mx-auto">
          <!-- Member Card Component -->
          <MemberCard
            :member="member"
            :card-style="cardStyle"
            :logo-url="qrcodeLogo"
          />

          <!-- Profile Information Sidebar -->
          <div class="container mx-auto">
            <div class="bg-white shadow-md mt-10 rounded-lg">
              <div class="py-10">
                <h1 class="font-bold text-center text-3xl text-gray-900">
                  {{ member.family_name }}{{ member.given_name }}
                </h1>
                <h1 class="font-bold text-center text-2xl text-gray-900">
                  {{ member.display_name }}
                </h1>
                <p class="text-center text-sm text-gray-400 font-medium">
                  {{ member.organization["name_" + $t("lang")] }}
                </p>

                <div class="my-5 px-6">
                  <a
                    href="#"
                    class="text-gray-200 block rounded-lg text-center font-medium leading-6 px-6 py-3 bg-gray-900 hover:bg-black hover:text-white"
                  >
                    Connect with <span class="font-bold">{{ member.email }}</span>
                  </a>
                </div>

                <div class="flex justify-between items-center my-5 px-6">
                  <a href="#" class="text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition duration-150 ease-in font-medium text-sm text-center w-full py-3">Facebook</a>
                  <a href="#" class="text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition duration-150 ease-in font-medium text-sm text-center w-full py-3">Twitter</a>
                  <a href="#" class="text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition duration-150 ease-in font-medium text-sm text-center w-full py-3">Instagram</a>
                  <a href="#" class="text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition duration-150 ease-in font-medium text-sm text-center w-full py-3">Email</a>
                </div>

                <div class="w-full">
                  <div v-if="members.length > 1">
                    <h3 class="font-medium text-gray-900 text-left px-6">
                      Your Organizations
                    </h3>
                    <div class="mt-5 w-full flex flex-col items-center overflow-hidden text-sm">
                      <template v-for="orgMember in members" :key="orgMember.id">
                        <a
                          v-if="orgMember.organization"
                          href="#"
                          class="w-full border-t border-gray-100 text-gray-600 py-4 pl-6 pr-3 block hover:bg-gray-100 transition duration-150"
                        >
                          <img
                            src="https://avatars0.githubusercontent.com/u/35900628?v=4"
                            alt=""
                            class="rounded-full h-6 shadow-md inline-block mr-2"
                          />
                         
                          {{ orgMember.organization.abbr_en }}
                          {{ orgMember.organization["name_" + $t("lang")] }}
                          <span
                            v-if="members.length > 1"
                            class="text-gray-500 text-xs float-right pr-2"
                          >
                            <a @click="switchOrganization(orgMember)">
                              {{ $t("organization_switch") }}
                            </a>
                          </span>
                        </a>
                      </template>
                    </div>
                  </div>

                  <h3 class="font-medium text-gray-900 text-left px-6">
                    Recent updates
                  </h3>
                  <div class="mt-5 w-full flex flex-col items-center overflow-hidden text-sm">
                    <template v-for="(portfolio, idx) in member.portfolios" :key="idx">
                      <a
                        href="#"
                        class="w-full border-t border-gray-100 text-gray-600 py-4 pl-6 pr-3 block hover:bg-gray-100 transition duration-150"
                      >
                        <img
                          src="https://avatars0.githubusercontent.com/u/35900628?v=4"
                          alt=""
                          class="rounded-full h-6 shadow-md inline-block mr-2"
                        />
                        {{ portfolio.title }} - {{ portfolio.description }}
                        <span class="text-gray-500 text-xs">
                          {{ portfolio.start_date }}
                        </span>
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

<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import ThumbList from "@/Components/ThumbList.vue";
import MemberCard from "@/Components/Member/MemberCard.vue";
import { gsap } from "gsap";

export default {
  components: {
    MemberLayout,
    ThumbList,
    MemberCard,
  },
  props: ["member", "members", "features", "forms", "articles", "cardStyle"],
  data() {
    return {
      qrcodeLogo: "/storage/images/site_logo.png",
      defaultFeatures: [
        // Keep your default features data if needed for fallback
      ],
    };
  },
  created() {
    if (this.cardStyle.logo) {
      this.qrcodeLogo = "/storage/images/" + this.cardStyle.logo;
    } else if (this.$page.props.member?.organization?.logo) {
      this.qrcodeLogo = this.$page.props.member.organization.logo;
    }
  },
  mounted() {
    // GSAP entrance animation for feature cards
    const featureBox = gsap.utils.toArray(".feature");
    featureBox.forEach((feature, index) => {
      gsap.fromTo(
        feature,
        { x: "100%", opacity: 0 },
        {
          x: "0%",
          opacity: 1,
          duration: index === 0 ? 0.5 : 0.5 + index * 0.25,
          delay: index * 0.25,
          ease: "power2.out",
        }
      );
    });
  },
  methods: {
    switchOrganization(member) {
      this.$inertia.post(
        route("member.membership.switch", { member: member.id })
      );
    },
  },
};
</script>