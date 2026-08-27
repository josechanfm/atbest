<template>
  <DefaultLayout :title="$t('forms')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight p-5">
        {{ $t('forms') }}
      </h2>
    </template>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Grid of cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="form in forms"
          :key="form.id"
          class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 flex flex-col"
        >
          <!-- Thumbnail -->
          <div class="w-full h-48 bg-gray-100 overflow-hidden">
            <img
              v-if="form.thumbnail_url"
              :src="form.thumbnail_url"
              :alt="form.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
              <img src="/storage/images/site_logo.png" alt="Logo" class="w-24 h-24 object-contain" />
            </div>
          </div>

          <!-- Card body -->
          <div class="p-4 flex-1 flex flex-col">
            <!-- Organization -->
            <div v-if="form.organization" class="text-sm text-gray-500 mb-1">
              <span class="font-medium">{{ form.organization.abbr }}</span>
              <span v-if="form.organization.name_zh" class="ml-1">· {{ form.organization.name_zh }}</span>
            </div>

            <!-- Title -->
            <h3 class="text-lg font-semibold text-gray-900 leading-tight mb-2 line-clamp-2">
              {{ form.title }}
            </h3>

            <!-- Badges: require_login & for_member -->
            <div class="flex items-center gap-3 text-sm text-gray-600 mt-1">
              <span v-if="form.require_login" class="inline-flex items-center gap-1">
                <LockOutlined /> {{ $t('require_login') }}
              </span>
              <span v-if="form.for_member" class="inline-flex items-center gap-1">
                <UserOutlined /> {{ $t('for_member') }}
              </span>
            </div>

            <!-- Spacer to push button to bottom -->
            <div class="flex-1"></div>

            <!-- Action button -->
            <div class="mt-4">
              <Link
                :href="route('form.item', { t: form.uuid })"
                class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-150"
              >
                {{ $t('form_registration') }}
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="forms.length === 0" class="text-center py-12">
        <p class="text-gray-500">{{ $t('no_forms_available') }}</p>
      </div>
    </div>
  </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Link } from '@inertiajs/vue3';
import { LockOutlined, UserOutlined } from '@ant-design/icons-vue';

export default {
  components: {
    DefaultLayout,
    Link,
    LockOutlined,
    UserOutlined,
  },
  props: {
    forms: {
      type: Array,
      required: true,
    },
  },
  // No need for columns, table data, etc.
};
</script>

<style scoped>
/* Optional: ensure line-clamp works (Tailwind has it) */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>