<template>
  <a-layout style="min-height: 100vh">
    <a-layout-sider v-model:collapsed="collapsed" :trigger="null" collapsible theme="light" width="250px"
      class="shadow-md">
      <div class="m-4 text-center text-lg" v-if="collapsed">
        <inertia-link href="/">{{ $page.props.auth.user.member.organization.abbr }}</inertia-link>
      </div>
      <div class="m-4 text-center text-lg" v-else>
        <inertia-link :href="route('organizer.dashboard')" v-if="$page.props.auth.user.member.organization">
          {{ $page.props.auth.user.member.organization['name_'+$t('lang')] }}
        </inertia-link>
      </div>
      <OrganizationMenu :menuKeys="menuKeys" />
    </a-layout-sider>
    <a-layout>
      <a-layout-header class="shadow-md border-b-2 border-red-600 flex" style="background: #fff; padding: 0">
        <menu-unfold-outlined v-if="collapsed" class="trigger" @click="() => (collapsed = !collapsed)" />
        <menu-fold-outlined v-else class="trigger" @click="() => (collapsed = !collapsed)" />

        <div class="flex-1"></div>
        <div class="sm:flex sm:items-center sm:ml-6">
          <!-- Settings Dropdown -->
          <div class="ml-3 relative">
            <Dropdown align="right" width="48">
              <template #trigger>
                <button v-if="$page.props.auth.user.member.avatar"
                  class="text-sm border-2 border-transparent rounded-full mt-5"
                >
                  <img
                    class="h-8 w-8 rounded-full object-cover"
                    :src="$page.props.auth.user.member.avatar"
                    :alt="$page.props.auth.user.member.given_name"
                  />
                </button>
                <a-avatar v-else>{{ $page.props.auth.user.member.given_name.charAt($page.props.auth.user.member.given_name.length-1) }}</a-avatar>
                <span class="inline-flex rounded-md">
                  <button
                    type="button"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition"
                  >
                    {{ $page.props.auth.user.member.given_name }}
                    <svg
                      class="ml-2 -mr-0.5 h-4 w-4"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>
                </span>
              </template>

              <template #content>
                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-gray-400">
                  {{ $t("manage_account") }}
                </div>

                <DropdownLink :href="route('member.profile.index')">
                  {{ $t("account") }}
                </DropdownLink>

                <DropdownLink
                  v-if="$page.props.hasApiFeatures"
                  :href="route('api-tokens.index')"
                >
                  {{ $t("api_tokens") }}
                </DropdownLink>

                <div class="border-t border-gray-100" />

                <!-- Authentication -->
                <form @submit.prevent="logout">
                  <DropdownLink as="button"> {{ $t("log_out") }} </DropdownLink>
                </form>
              </template>
            </Dropdown>
          </div>
        </div>
      </a-layout-header>
      
      <!-- {{ $page.props }} -->
      <a-layout-content>
        <header class="flex justify-between items-center bg-gray-200 m-4 py-4 px-6 mb-5 bg-white shadow sm:rounded-lg">
          <div class="text-lg font-bold">
            {{ title }}
          </div>
          <nav class="text-sm" v-if="breadcrumb">
            <ol class="list-none flex">
              <li class="breadcrumb-item hidden md:inline" v-for="(item, idx) in breadcrumb">
                <inertia-link v-if="item.url" :href="item.url">{{ item.label }}</inertia-link>
                <span v-else>{{ item.label }}</span>
                <span class="pl-2 pr-2" v-if="idx < breadcrumb.length - 1">&gt;</span>
              </li>
              <li class="breadcrumb-item block md:hidden">
                <span v-if="breadcrumb.length > 1">
                  <inertia-link :href="breadcrumb[breadcrumb.length - 2].url">
                    {{ breadcrumb[breadcrumb.length - 2].label }}
                  </inertia-link>
                </span>
                <span v-else>
                  <inertia-link :href="route('organizer.dashboard')">
                    Home
                  </inertia-link>
                </span>
              </li>
              <li>
                <span class="pl-2 pr-2">|</span>
                <a href="javascript:history.back();" class="inline">{{ $t('back') }}</a>
              </li>
            </ol>

          </nav>
        </header>

          <main>
            <div class="xs:p-2 p-4">
              <slot />
            </div>
          </main>

      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<script>
import { ref, reactive } from "vue";
import { router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { MenuUnfoldOutlined, MenuFoldOutlined } from "@ant-design/icons-vue";
import OrganizationMenu from "@/Components/Organization/OrganizationMenu.vue";

export default {
  components: {
    Dropdown,
    DropdownLink,
    OrganizationMenu,
    MenuUnfoldOutlined,
    MenuFoldOutlined,
  },
  props: ["title", "breadcrumb"],
  setup(props) {
    const menuKeys = reactive({
      menuOpenKey: "",
      menuSelectKey: "",
    });

    const showingNavigationDropdown = ref(false);
    const selectedKeys = ref(["1"]);
    const collapsed = ref(false);

    const switchToTeam = (team) => {
      router.put(
        route("current-team.update"),
        {
          team_id: team.id,
        },
        {
          preserveState: false,
        }
      );
    };
    const page = usePage();
    const logout = () => {
      router.post(route("logout"));
    };

    return {
      showingNavigationDropdown,
      selectedKeys,
      menuKeys,
      collapsed,
      switchToTeam,
      logout,
    };
  },
  mounted() {
  },

};
// defineProps({
//     title: String,
// });
</script>

<style>
#app .trigger {
  font-size: 18px;
  line-height: 64px;
  padding: 0 24px;
  cursor: pointer;
  transition: color 0.3s;
}

#app .trigger:hover {
  color: #1890ff;
}

#app .logo {
  height: 32px;
  background: rgba(255, 255, 255, 0.3);
  margin: 16px;
}

.site-layout .site-layout-background {
  background: #fff;
}
</style>
