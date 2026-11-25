<script setup>
import { router, Link } from "@inertiajs/vue3";

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
              <p class="hidden sm:block text-xs text-indigo-200/90 tracking-widest mt-0.5">
                Atbest · NPO 精英會員平台
              </p>
            </div>
          </div>

          <!-- Menu -->
          <div class="flex items-center space-x-6">
            <Link
              :href="route('home')"
              class="text-sm font-medium text-white/90 hover:text-yellow-300 transition duration-150"
            >
              {{ $t("dashboard") }}
            </Link>

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

        </div>
      </nav>
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
  </div>
</template>
