<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Head, Link } from "@inertiajs/vue3";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
  title: String,
  organization:Object
});

const logout = () => {
  console.log("logout");
  router.post(route("logout"));
};

const showingNavigationDropdown = ref(false);
</script>

<template>
  <!-- component -->
  <!-- Header -->
  <header>
    <!-- navbar and menu -->
    <nav class="shadow bg-gradient-to-tr bg-[#0081C8]">
      <div class="flex justify-between items-center py-6 px-10 container mx-auto ">
        <div class="flex">
          <div class="shrink-0 flex items-center">
            <a v-if="organization.logo" href="/"><img :src="'/logos/'+organization.logo" class="block h-14 w-auto"/></a>
          </div>
          <h1 class="ml-2 pt-4 text-2xl font-bold">
            <a href="/" class="text-white">{{organization.name_zh}}</a>
          </h1>
        </div>

        <div>
          <!-- Hamburger -->
          <div class="-mr-2 flex items-center sm:hidden">
            <button
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 bg-white hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition"
              @click="showingNavigationDropdown = !showingNavigationDropdown"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path
                  :class="{
                    hidden: showingNavigationDropdown,
                    'inline-flex': !showingNavigationDropdown,
                  }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  :class="{
                    hidden: !showingNavigationDropdown,
                    'inline-flex': showingNavigationDropdown,
                  }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div class="flex items-center">
            <ul class="list-none sm:flex space-x-4 hidden items-center text-white">
              <li>
                <a href="https://www.lionsclubs.org/" target="_blank"
                  class="text-bold text-white hover:text-yellow-300 text-md">總會</a>
              </li>
              <li>
                <a href="https://www.lionsclubs.org.hk/" target="_blank"
                  class="text-bold text-white hover:text-yellow-300 text-md">區會</a
                >
              </li>
              <li v-if="organization.parent_domain">
                <a :href="organization.parent_domain" target="_blank"
                  class="text-bold text-white hover:text-yellow-300 text-md">母會</a>
              </li>
              <li>
                <a href="https://www.leo303.org" target="_blank"
                  class="text-bold text-white hover:text-yellow-300 text-md">會員</a>
              </li>
              
              <template v-if="$page.props.user.id">
                <li>
                  <a
                    :href="route('member.dashboard')"
                    target="_blank"
                    class="text-bold text-white hover:text-yellow-300 text-md"
                    >{{ $t("member_dashboard") }}</a
                  >
                </li>
                <li>
                  <a @click="logout">{{ $t("log_out") }}</a>
                </li>
              </template>
              <template v-else>
                <li>
                  <inertia-link :href="route('login')" class="text-bold text-white hover:text-yellow-300 text-md">{{
                    $t("login") }}</inertia-link>
                </li>
                <li>
                  <inertia-link :href="route('registration')"
                    class="text-bold text-white hover:text-yellow-300 text-md">{{ $t("register") }}</inertia-link>
                </li>
              </template>
            </ul>
            <!-- <div class="md:flex items-center hidden space-x-4 ml-8 lg:ml-12">
                            <h1 class="text-text-gray-600  py-2 hover:cursor-pointer hover:text-indigo-600"><inertia-link
                                    :href="route('login')">登入</inertia-link></h1>
                            <h1
                                class="text-text-gray-600  py-2 hover:cursor-pointer px-4 rounded text-white bg-gradient-to-tr from-indigo-600 to-green-600 hover:shadow-lg">
                                <inertia-link :href="route('login')">後台</inertia-link>
                            </h1>
                        </div> -->
          </div>
        </div>
      </div>
      <!-- Responsive Navigation Menu -->
      <div
        :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
        class="sm:hidden bg-white"
      >
        <div class="pt-2 pb-3 space-y-1">
          <ResponsiveNavLink :href="route('/')" :active="route().current('dashboard')">
            {{ $t('dashboard') }}
          </ResponsiveNavLink>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
          <div class="mt-3 space-y-1">
            <ResponsiveNavLink
              :href="route('member.dashboard')"
              :active="route().current('member.dashboard')"
            >
              {{ $t("member_dashboard") }}
            </ResponsiveNavLink>
            <!-- Authentication -->
            <template v-if="$page.props.user.id">
              <form method="POST" @submit.prevent="logout">
                <ResponsiveNavLink as="button"> {{ $t("log_out") }} </ResponsiveNavLink>
              </form>
            </template>
            <template v-else>
              <a :href="route('login')">
                <ResponsiveNavLink as="button"> {{ $t('login') }}</ResponsiveNavLink>
              </a>
              <a :href="route('registration')">
                <ResponsiveNavLink as="button"> {{ $t("register") }} </ResponsiveNavLink>
              </a>
            </template>
          </div>
        </div>
      </div>
    </nav>
  </header>
  <!-- Page Heading -->
  <header v-if="$slots.header" class="bg-gray-100 shadow">
    <div class="max-w-7xl mx-auto py-2 px-4 sm:px-6 lg:px-8">
      <slot name="header" />
    </div>
  </header>

  <main>
    <!-- section hero -->
    <section>
      <div class="bg-gray-100 p-0 lg:p-4 pt-2 min-h-full lg:min-h-screen space-y-6 sm:space-y-0 sm:gap-4">
        <div>
          <!-- Page Content -->
          <main>
            <slot />
          </main>
        </div>
      </div>
    </section>
  </main>
</template>

<style>
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
</style>
