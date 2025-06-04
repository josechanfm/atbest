<script>
import { onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Head, Link } from "@inertiajs/vue3";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { usePage } from "@inertiajs/vue3";
import LanguageSwitcher from "@/Components/LanguageSwitcher.vue";
// import { loadLanguageAsync } from "laravel-vue-i18n";

export default {
  components: {
    Head,
    Link,
    ApplicationMark,
    Banner,
    Dropdown,
    DropdownLink,
    NavLink,
    ResponsiveNavLink,
    LanguageSwitcher,
    // loadLanguageAsync,
  },
  props: ["title"],
  setup(props) {
    const showingNavigationDropdown = ref(false);
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
    const isOrganizer = () =>{
      return page.props.auth.user.member.is_organizer==true
    }
    const page = usePage();
    //loadLanguageAsync(page.props.value.lang);

    const logout = () => {
      router.post(route("logout"));
    };
    return {
      showingNavigationDropdown,
      switchToTeam,
      logout,
      isOrganizer,
      // loadLanguageAsync,
    };
  },
  created() {},
  mounted() {
    //loadLanguageAsync(this.$page.props.lang)
  },
};
</script>

<template>
  <div>
    <Head :title="title" />
    <Banner />
    <div class="min-h-screen bg-gray-100">
      <nav class="bg-white border-b border-gray-100">
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex">
              <!-- Logo -->
              <div class="shrink-0 flex items-center">
                <Link :href="route('host')" v-if="$page.props.auth.user.member">
                  <img v-if="$page.props.auth.user.member.organization.logo" :src="$page.props.auth.user.member.organization.logo" class="block h-14 w-auto" />
                  <img v-else src="/storage/images/site_logo.png" class="block h-14 w-auto" />
                </Link>
              </div>
              <!-- Navigation Links -->
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  :href="route('member.dashboard')"
                  :active="route().current('/')"
                >
                  {{ $t("member_dashboard") }}
                </NavLink>
                <NavLink :href="route('member.profile.index')">
                  {{ $t("profile") }}
                </NavLink>
                <NavLink :href="route('member.entries.index')">
                  {{ $t("form_filled") }}
                </NavLink>
                <NavLink :href="route('member.blogs.index')">
                  {{ $t("blogs") }}
                </NavLink>
                <NavLink :href="route('widget.admin.dashboard')" v-permission="['widget']">
                  {{ $t("widget") }}
                </NavLink>
                <NavLink :href="route('member.guardian.back')" v-if="$page.props.by_guardian">
                  {{ $t("guardian") }}
                </NavLink>
                <NavLink v-if="isOrganizer()" :href="route('organizer.dashboard')">
                  {{ $t("manager") }} 
                </NavLink>
              </div>
            </div>

            <div class="flex items-center sm:ml-6">
              <!-- Settings Dropdown -->
              <language-switcher />
              <div class="ml-3 relative p-4">
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
                     
                    <form method="POST" @submit.prevent="logout">
                        <DropdownLink as="button"> {{ $t("log_out") }} </DropdownLink>
                    </form>
                  </template>
                </Dropdown>
              </div>
            </div>


          </div>
        </div>


      </nav>

      <!-- Page Heading -->
      <header v-if="$slots.header" class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>
