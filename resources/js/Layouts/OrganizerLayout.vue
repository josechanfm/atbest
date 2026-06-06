<template>
  <a-layout style="min-height: 100vh">
    <!-- Mobile Drawer for Side Menu -->
    <a-drawer
      v-model:visible="drawerVisible"
      placement="left"
      :closable="false"
      :body-style="{ padding: 0 }"
      :width="280"
      :z-index="1000"
      class="mobile-drawer"
      @close="drawerVisible = false"
    >
      <div class="drawer-content">
        <!-- Logo Area in Drawer -->
        <div class="py-6 px-4 border-b border-gray-200 bg-gradient-to-r from-white to-gray-50">
          <div class="flex items-center justify-between">
            <inertia-link :href="route('organizer.dashboard')" class="flex items-center space-x-3 flex-1">
              <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-md">
                <span class="text-white font-bold text-lg">
                  {{ $page.props.auth.organization?.abbr?.charAt(0) || 'O' }}
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-gray-800 text-sm truncate">
                  {{ $page.props.auth.organization?.name_zh || 'Organization' }}
                </div>
                <div class="text-xs text-gray-500">{{ $page.props.auth.organization?.abbr }}</div>
              </div>
            </inertia-link>
            <a-button 
              type="text" 
              @click="drawerVisible = false"
              class="!w-8 !h-8 !p-0"
            >
              <template #icon>
                <close-outlined />
              </template>
            </a-button>
          </div>
        </div>

        <!-- Menu in Drawer -->
        <div class="flex-1 overflow-y-auto custom-scrollbar" style="height: calc(100vh - 180px)">
          <OrganizationMenu :menuKeys="menuKeys" @menu-click="drawerVisible = false" />
        </div>

        <!-- User Info in Drawer -->
        <div class="border-t border-gray-200 p-4 bg-gray-50">
          <a-dropdown placement="topRight" :trigger="['click']">
            <div class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded-lg transition">
              <a-avatar 
                v-if="$page.props.auth.user.member?.avatar"
                :src="$page.props.auth.user.member.avatar"
                class="shadow-md"
              />
              <a-avatar v-else class="bg-gradient-to-br from-red-500 to-red-600">
                {{ $page.props.auth.user.member?.given_name?.charAt($page.props.auth.user.member?.given_name?.length - 1) }}
              </a-avatar>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 truncate">
                  {{ $page.props.auth.user.member?.given_name }}
                </div>
                <div class="text-xs text-gray-500 truncate">
                  {{ $page.props.auth.user.email }}
                </div>
              </div>
              <down-outlined class="text-gray-400 text-xs" />
            </div>
            <template #overlay>
              <a-menu>
                <a-menu-item>
                  <inertia-link :href="route('member.profile.index')" class="block">
                    {{ $t('account') }}
                  </inertia-link>
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item @click="logout">
                  {{ $t('log_out') }}
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </div>
    </a-drawer>

    <!-- Desktop Sidebar (hidden on mobile) -->
    <a-layout-sider 
      v-model:collapsed="collapsed" 
      :trigger="null" 
      collapsible 
      theme="light" 
      width="280px"
      :class="['desktop-sidebar', isMobile ? 'hidden-on-mobile' : '']"
      :style="{ 
        background: 'linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%)',
        borderRight: '1px solid #e9ecef',
        boxShadow: '2px 0 8px rgba(0,0,0,0.05)'
      }"
    >
      <!-- Logo Area -->
      <div class="py-6 px-4 border-b border-gray-200" :class="{ 'text-center': collapsed }">
        <div v-if="!collapsed" class="flex items-center justify-between">
          <inertia-link :href="route('organizer.dashboard')" class="flex items-center space-x-3 flex-1">
            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-md">
              <span class="text-white font-bold text-lg">
                {{ $page.props.auth.organization?.abbr?.charAt(0) || 'O' }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-gray-800 text-sm truncate max-w-[180px]">
                {{ $page.props.auth.organization?.name_zh || 'Organization' }}
              </div>
              <div class="text-xs text-gray-500">{{ $page.props.auth.organization?.abbr }}</div>
            </div>
          </inertia-link>
        </div>

        <div v-else class="flex justify-center">
          <inertia-link :href="route('organizer.dashboard')">
            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-md">
              <span class="text-white font-bold text-lg">
                {{ $page.props.auth.organization?.abbr?.charAt(0) || 'O' }}
              </span>
            </div>
          </inertia-link>
        </div>
      </div>

      <!-- Menu Area -->
      <div class="flex-1 overflow-y-auto py-4 custom-scrollbar">
        <OrganizationMenu :menuKeys="menuKeys" />
      </div>

      <!-- Footer User Info -->
      <div class="border-t border-gray-200 p-4 mt-auto" :class="{ 'text-center': collapsed }">
        <a-dropdown placement="topRight" :trigger="['click']" v-if="!collapsed">
          <div class="flex items-center space-x-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition">
            <a-avatar 
              v-if="$page.props.auth.user.member?.avatar"
              :src="$page.props.auth.user.member.avatar"
              class="shadow-md"
            />
            <a-avatar v-else class="bg-gradient-to-br from-red-500 to-red-600">
              {{ $page.props.auth.user.member?.given_name?.charAt($page.props.auth.user.member?.given_name?.length - 1) }}
            </a-avatar>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium text-gray-900 truncate">
                {{ $page.props.auth.user.member?.given_name }}
              </div>
              <div class="text-xs text-gray-500 truncate">
                {{ $page.props.auth.user.email }}
              </div>
            </div>
            <down-outlined class="text-gray-400" />
          </div>
          <template #overlay>
            <a-menu>
              <a-menu-item>
                <inertia-link :href="route('member.profile.index')" class="block">
                  {{ $t('account') }}
                </inertia-link>
              </a-menu-item>
              <a-menu-divider />
              <a-menu-item @click="logout">
                {{ $t('log_out') }}
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>

        <div v-else class="flex justify-center">
          <a-dropdown placement="topRight" :trigger="['click']">
            <div class="cursor-pointer">
              <a-avatar 
                v-if="$page.props.auth.user.member?.avatar"
                :src="$page.props.auth.user.member.avatar"
                class="shadow-md"
              />
              <a-avatar v-else class="bg-gradient-to-br from-red-500 to-red-600">
                {{ $page.props.auth.user.member?.given_name?.charAt($page.props.auth.user.member?.given_name?.length - 1) }}
              </a-avatar>
            </div>
            <template #overlay>
              <a-menu>
                <a-menu-item>
                  <inertia-link :href="route('member.profile.index')" class="block">
                    {{ $t('account') }}
                  </inertia-link>
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item @click="logout">
                  {{ $t('log_out') }}
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </div>
    </a-layout-sider>

    <a-layout>
      <a-layout-header class="shadow-md border-b-2 border-red-600 flex justify-between items-center" style="background: #fff; padding: 0 20px">
        <div class="flex items-center">
          <!-- Mobile Menu Button -->
          <a-button 
            v-if="isMobile" 
            type="text"
            @click="drawerVisible = true"
            class="mobile-menu-btn"
          >
            <template #icon>
              <menu-unfold-outlined class="text-xl" />
            </template>
          </a-button>
          
          <!-- Desktop trigger buttons -->
          <template v-else>
            <menu-unfold-outlined 
              v-if="collapsed" 
              class="trigger" 
              @click="() => (collapsed = !collapsed)" 
            />
            <menu-fold-outlined 
              v-if="!collapsed" 
              class="trigger" 
              @click="() => (collapsed = !collapsed)" 
            />
          </template>

          <!-- Page Title -->
          <div class="ml-4 hidden md:block">
            <h1 class="text-lg font-semibold text-gray-800">{{ title }}</h1>
          </div>
        </div>

        <div class="flex items-center space-x-4">
          <language-switcher />
          
          <a-dropdown :trigger="['click']" placement="bottomRight">
            <div class="flex items-center space-x-2 cursor-pointer group">
              <a-avatar 
                v-if="$page.props.auth.user.member?.avatar"
                :src="$page.props.auth.user.member.avatar"
                class="shadow-md"
                :size="32"
              />
              <a-avatar v-else class="bg-gradient-to-br from-red-500 to-red-600" :size="32">
                {{ $page.props.auth.user.member?.given_name?.charAt($page.props.auth.user.member?.given_name?.length - 1) }}
              </a-avatar>
              <span class="hidden sm:inline-block text-sm font-medium text-gray-700">
                {{ $page.props.auth.user.member?.given_name }}
              </span>
              <down-outlined class="hidden sm:block text-gray-400 text-xs group-hover:text-gray-600 transition" />
            </div>
            <template #overlay>
              <a-menu>
                <a-menu-item-group :title="$t('manage_account')">
                  <a-menu-item>
                    <inertia-link :href="route('member.profile.index')" class="block">
                      {{ $t('account') }}
                    </inertia-link>
                  </a-menu-item>
                  <a-menu-item v-if="$page.props.hasApiFeatures">
                    <inertia-link :href="route('api-tokens.index')" class="block">
                      {{ $t('api_tokens') }}
                    </inertia-link>
                  </a-menu-item>
                </a-menu-item-group>
                <a-menu-divider />
                <a-menu-item @click="logout" class="text-red-600">
                  {{ $t('log_out') }}
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </a-layout-header>

      <a-layout-content class="bg-gray-50">
        <!-- Mobile Breadcrumb -->
        <div class="bg-white shadow-sm border-b border-gray-200 md:hidden px-4 py-3">
          <nav class="text-sm">
            <a-breadcrumb>
              <a-breadcrumb-item v-if="breadcrumb && breadcrumb.length > 0">
                <inertia-link 
                  v-if="breadcrumb[breadcrumb.length - 2]?.url" 
                  :href="breadcrumb[breadcrumb.length - 2].url"
                >
                  {{ breadcrumb[breadcrumb.length - 2].label }}
                </inertia-link>
                <span v-else>{{ $t('home') }}</span>
              </a-breadcrumb-item>
              <a-breadcrumb-item>
                <span class="text-gray-900">{{ title }}</span>
              </a-breadcrumb-item>
            </a-breadcrumb>
          </nav>
        </div>

        <!-- Desktop Breadcrumb -->
        <div class="hidden md:block bg-white shadow-sm border-b border-gray-200">
          <div class="px-6 py-4">
            <div class="flex justify-between items-center">
              <a-breadcrumb>
                <a-breadcrumb-item v-for="(item, idx) in breadcrumb" :key="idx">
                  <inertia-link v-if="item.url" :href="item.url">
                    {{ item.label }}
                  </inertia-link>
                  <span v-else>{{ item.label }}</span>
                </a-breadcrumb-item>
              </a-breadcrumb>
              
              <a-button type="link" @click="goBack" class="flex items-center gap-1">
                <arrow-left-outlined />
                <span>{{ $t('back') }}</span>
              </a-button>
            </div>
          </div>
        </div>

        <main class="p-4 md:p-6">
          <div class="fade-enter-active">
            <slot />
          </div>
        </main>
      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<script>
import { ref, reactive, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import { 
  MenuUnfoldOutlined, 
  MenuFoldOutlined,
  CloseOutlined,
  DownOutlined,
  ArrowLeftOutlined
} from "@ant-design/icons-vue";
import OrganizationMenu from "@/Components/Organization/OrganizationMenu.vue";
import LanguageSwitcher from "@/Components/LanguageSwitcher.vue";

export default {
  components: {
    OrganizationMenu,
    LanguageSwitcher,
    MenuUnfoldOutlined,
    MenuFoldOutlined,
    CloseOutlined,
    DownOutlined,
    ArrowLeftOutlined,
  },
  props: ["title", "breadcrumb"],
  setup(props) {
    const menuKeys = reactive({
      menuOpenKey: "",
      menuSelectKey: "",
    });

    const collapsed = ref(false);
    const drawerVisible = ref(false);
    const isMobile = ref(false);

    const checkMobile = () => {
      isMobile.value = window.innerWidth < 768;
      if (isMobile.value) {
        collapsed.value = true;
      }
    };

    const logout = () => {
      router.post(route("logout"));
    };

    const goBack = () => {
      window.history.back();
    };

    onMounted(() => {
      checkMobile();
      window.addEventListener('resize', checkMobile);
    });

    onUnmounted(() => {
      window.removeEventListener('resize', checkMobile);
    });

    return {
      menuKeys,
      collapsed,
      drawerVisible,
      isMobile,
      logout,
      goBack,
    };
  },
};
</script>

<style scoped>
/* Desktop Sidebar Styles */
.desktop-sidebar {
  position: relative;
  z-index: 10;
}

.hidden-on-mobile {
  display: none;
}

/* Mobile Drawer Styles */
.mobile-drawer :deep(.ant-drawer-body) {
  padding: 0;
}

.drawer-content {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
}

.mobile-menu-btn {
  font-size: 18px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Trigger Button Styles */
.trigger {
  font-size: 18px;
  line-height: 64px;
  padding: 0 24px;
  cursor: pointer;
  transition: color 0.3s;
}

.trigger:hover {
  color: #1890ff;
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Animation */
.fade-enter-active {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Breakpoints */
@media (min-width: 768px) {
  .hidden-on-mobile {
    display: block !important;
  }
}

/* Menu Hover Effects */
:deep(.ant-menu-item-selected) {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
  color: #dc2626 !important;
}

:deep(.ant-menu-item:hover) {
  background: #fef2f2 !important;
  color: #dc2626 !important;
}

:deep(.ant-menu-item) {
  transition: all 0.3s ease;
}

/* Dropdown menu styling */
:deep(.ant-dropdown-menu) {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 8px 0;
}

:deep(.ant-dropdown-menu-item) {
  padding: 8px 16px;
}

/* Breadcrumb styling */
:deep(.ant-breadcrumb) {
  color: #6b7280;
}

:deep(.ant-breadcrumb a) {
  color: #6b7280;
  transition: color 0.2s;
}

:deep(.ant-breadcrumb a:hover) {
  color: #dc2626;
}

/* Mobile adjustments */
@media (max-width: 768px) {
  .ant-layout-header {
    padding: 0 12px !important;
  }
}
</style>