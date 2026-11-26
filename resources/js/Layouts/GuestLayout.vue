<script setup>
import { ref } from "vue";
import { router, Link } from "@inertiajs/vue3";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import SiteFooter from "@/Components/SiteFooter.vue";

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
  title: String,
  organization: Object,
});

const logout = () => {
  router.post(route("logout"));
};

const showingNavigationDropdown = ref(false);
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-50">
    <!-- Top Header & Navigation -->
    <header class="sticky top-0 z-50 shadow-md bg-gradient-to-r from-[#0081C8] to-[#0066a1]">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
          
          <!-- Brand / Logo -->
            <div class="flex items-center">
                <div class="shrink-0 flex">
                    <a href="/">
                        <img src="/storage/images/site_logo.png" class="block h-14 w-auto" alt="智滙精英 Logo" />
                    </a>
                </div>
                <div class="ml-3 flex flex-col justify-center">
                    <h1 class="text-xl md:text-2xl font-bold tracking-wider leading-tight">
                        <a href="/" class="text-white hover:text-indigo-200 transition-colors">
                            智滙精英
                        </a>
                    </h1>
                    <p class="block text-xs text-indigo-200/90 tracking-widest mt-0.5">
                        Atbest · NPO 精英會員平台
                    </p>
                </div>
            </div>

          <!-- Desktop Menu -->
          <div class="hidden sm:flex sm:items-center sm:space-x-6">
            <a
              href="http://localhost:8000"
              target="_blank"
              class="text-sm font-medium text-white/90 hover:text-yellow-300 transition duration-150"
            >
              {{ $t("host") }}
            </a>

            <!-- Logout Button (Shown if user is logged in) -->
            <button
              v-if="$page.props.auth?.user"
              @click="logout"
              type="button"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[#0081C8] bg-white rounded-lg shadow-sm hover:bg-blue-50 hover:text-[#0066a1] focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-[#0081C8] transition duration-150 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span>{{ $t("Log Out") || "Logout" }}</span>
            </button>
          </div>

          <!-- Mobile Hamburger Button -->
          <div class="flex items-center sm:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              type="button"
              class="inline-flex items-center justify-center p-2 rounded-lg text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white transition"
              aria-label="Toggle navigation"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path
                  :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

        </div>
      </nav>

      <!-- Responsive Mobile Dropdown Menu -->
      <div
        v-show="showingNavigationDropdown"
        class="sm:hidden bg-white border-t border-gray-100 shadow-lg"
      >
        <div class="pt-2 pb-3 space-y-1">
          <ResponsiveNavLink :href="route('home')" :active="route().current('dashboard')">
            {{ $t("dashboard") }}
          </ResponsiveNavLink>
        </div>

        <div class="pt-3 pb-3 border-t border-gray-200 space-y-1">
          <a
            href="http://localhost:8000"
            target="_blank"
            class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 hover:border-[#0081C8] transition"
          >
            {{ $t("host") }}
          </a>

          <!-- Mobile Logout Button -->
          <div v-if="$page.props.auth?.user" class="pt-2 mt-2 border-t border-gray-100 px-3">
            <button
              @click="logout"
              type="button"
              class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span>{{ $t("Log Out") || "Logout" }}</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Optional Sub-Header Slot -->
    <header v-if="$slots.header" class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Main Page Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <slot />
    </main>

    <!-- Footer -->
    <SiteFooter />
  </div>
</template>