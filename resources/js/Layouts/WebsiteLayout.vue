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
            <a v-if="organization?.logo" href="/"><img :src="organization.logo" class="block h-14 w-auto"/></a>
          </div>
          <h1 class="ml-2 pt-4 text-2xl font-bold">
            <a href="/" class="text-white" v-if="organization">{{organization.name_zh}}</a>
            <div>Organization Name</div>
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
                <a
                  href="http://localhost:8000"
                  target="_blank"
                  class="text-bold text-white hover:text-yellow-300 text-md"
                  >{{ $t('host') }}</a
                >
              </li>
              <li>
                <a href="https://www.lionsclubs.org/en" target="_blank"
                  class="text-bold text-white hover:text-yellow-300 text-md">{{ $t('headquater') }}</a>
              </li>
            </ul>
          </div>

        </div>
      </div>
      <!-- Responsive Navigation Menu -->
      <div
        :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
        class="sm:hidden bg-white"
      >
        <div class="pt-2 pb-3 space-y-1">
          <ResponsiveNavLink :href="route('host')" :active="route().current('dashboard')">
            {{ $t('dashboard') }}
          </ResponsiveNavLink>
        </div>
        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
          <div class="mt-3 space-y-1">
            <a href="http://localhost:8000" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition">
              {{ $t("host") }}
            </a>

            <a href="https://lionsclubs.org" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition">
              {{ $t("headquater") }}
            </a>
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
