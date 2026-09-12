<template>
  <div class="guest-layout min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <!-- Optional logo / branding -->
      <div class="text-center">
        <img
          v-if="appLogo"
          :src="appLogo"
          alt="Logo"
          class="mx-auto h-16 w-auto"
        />
        <h2 v-else-if="appName" class="text-3xl font-extrabold text-gray-900">
          {{ appName }}
        </h2>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <!-- Main content slot -->
        <slot />
      </div>
    </div>

    <!-- Optional footer -->
    <div class="mt-8 text-center text-sm text-gray-500">
      <slot name="footer">
        <span v-if="copyright">{{ copyright }}</span>
      </slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GuestLayout',
  props: {
    appName: {
      type: String,
      default: 'Atbest',
    },
    appLogo: {
      type: String,
      default: null,
    },
    copyright: {
      type: String,
      default: '© 2026 Atbest. All rights reserved.',
    },
  },
};
</script>

<style scoped>
.guest-layout {
  background-color: #f7fafc;
}
</style>