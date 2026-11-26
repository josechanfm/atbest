<script setup>
import BlankLayout from "@/Layouts/BlankLayout.vue";
import { ref, computed, watch, onMounted } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

defineProps({
  status: String,
  canResetPassword: Boolean,
});

const page = usePage();
const activeTab = ref("mobile");
const dismissedGoogleError = ref(false);

const form = useForm({
  email: "",
  mobile: "",
  password: "",
  remember: false,
  service: route().params.s,
});

const urlParams = computed(() => {
  if (typeof window === "undefined") return new URLSearchParams();
  return new URLSearchParams(window.location.search);
});

const rawGoogleNotBoundError = computed(() => {
  const fromProps =
    page.props.errors?.google_not_bound ||
    page.props.errors?.google ||
    "";

  if (fromProps) return fromProps;

  if (urlParams.value.get("google_error") === "not_bound") {
    return "google_not_bound";
  }

  return "";
});

const googleNotBoundError = computed(() => {
  if (dismissedGoogleError.value) return "";
  return rawGoogleNotBoundError.value;
});

const unboundGoogleEmail = computed(() => {
  return (
    page.props.flash?.google_unbound_email ||
    urlParams.value.get("google_email") ||
    ""
  );
});

const syncEmailFromUnboundGoogle = (email) => {
  if (!email) return;
  dismissedGoogleError.value = false;
  activeTab.value = "email";
  if (!form.email) {
    form.email = email;
  }
};

watch(rawGoogleNotBoundError, (val) => {
  if (val) {
    dismissedGoogleError.value = false;
  }
});

onMounted(() => {
  syncEmailFromUnboundGoogle(unboundGoogleEmail.value);
});

watch(unboundGoogleEmail, (email) => {
  syncEmailFromUnboundGoogle(email);
});

const handleTabChange = (key) => {
  activeTab.value = key;
  form.clearErrors("email", "mobile");

  if (key === "email") {
    form.mobile = "";
  } else {
    form.email = "";
  }
};

const forgotPasswordHref = computed(() => {
  return activeTab.value === "mobile"
    ? "/mobile-forgot-password"
    : route("password.request");
});

const submit = () => {
  form
    .transform((data) => {
      const payload = {
        ...data,
        login_type: activeTab.value,
        remember: form.remember ? "on" : "",
      };
      if (activeTab.value === "mobile") {
        payload.mobile = "+853" + form.mobile;
        delete payload.email;
      } else {
        delete payload.mobile;
      }
      return payload;
    })
    .post(route("login"), {
      onFinish: () => form.reset("password"),
    });
};
</script>

<template>
  <Head :title="$t('login')" />
  <BlankLayout>
    <div class="max-w-md mx-auto">
      <div class="p-5 bg-white shadow-md overflow-hidden rounded-lg">
        <h2 class="text-xl font-bold text-center text-gray-800 mb-4">{{ $t("login_header") }}</h2>

        <a-alert v-if="status" :message="status" type="success" show-icon class="mb-4" />

        <a-alert
          v-if="googleNotBoundError"
          type="error"
          show-icon
          closable
          class="mb-4"
          :message="$t(googleNotBoundError)"
          @close="dismissedGoogleError = true"
        >
          <template #description>
            <div class="text-xs text-gray-700 space-y-1 mt-1">
              <p v-if="unboundGoogleEmail">
                Google: <strong>{{ unboundGoogleEmail }}</strong>
              </p>
              <p>
                {{ $t("google_bind_after_login_prompt") }}
              </p>
            </div>
          </template>
        </a-alert>

        <a-tabs v-model:activeKey="activeTab" class="login-tabs" @change="handleTabChange">
          <a-tab-pane key="email" :tab="$t('email_login')" />
          <a-tab-pane key="mobile" :tab="$t('mobile_login')" />
        </a-tabs>

        <a-form :model="form" layout="vertical" autocomplete="off" @finish="submit">
          <input type="hidden" :value="form.service" name="service" />

          <!-- Email Login -->
          <a-form-item v-if="activeTab === 'email'" :label="$t('email')" name="email"
            :validate-status="form.errors.email ? 'error' : undefined" :help="form.errors.email" :rules="[
              { required: true, message: $t('please_input_email') },
              { type: 'email', message: $t('please_input_email') },
            ]">
            <a-input v-model:value="form.email" type="input" size="large" autofocus />
          </a-form-item>

          <!-- Mobile Login (+853) -->
          <a-form-item v-else :label="$t('mobile_number')" name="mobile"
            :validate-status="form.errors.mobile ? 'error' : undefined" :help="form.errors.mobile" :rules="[
              { required: true, message: $t('mobile_required') },
              { pattern: /^6\d{7}$/, message: $t('mobile_invalid') },
            ]">
            <a-input-group compact>
              <a-form-item-rest>
                <a-input style="width: 22%" value="+853" size="large" disabled />
              </a-form-item-rest>
              <a-input v-model:value="form.mobile" style="width: 78%" type="input" inputmode="numeric" size="large"
                :maxlength="8" :placeholder="$t('enter_8_digits_starting_with_6')"
                @input="form.mobile = form.mobile.replace(/\D/g, '')" />
            </a-input-group>
          </a-form-item>

          <a-form-item :label="$t('password')" name="password"
            :validate-status="form.errors.password ? 'error' : undefined" :help="form.errors.password"
            :rules="[{ required: true, message: $t('please_input_password') }]">
            <a-input-password v-model:value="form.password" size="large" autocomplete="current-password" />
          </a-form-item>

          <div class="flex items-center justify-between mb-4">
            <a-checkbox v-model:checked="form.remember">
              <span class="text-sm text-gray-600">{{ $t("remember_me") }}</span>
            </a-checkbox>

            <Link :href="route('register')" class="underline text-sm text-gray-600 hover:text-gray-900">
              {{ $t("register") }}
            </Link>
          </div>

          <div class="flex items-center justify-between mt-4">
            <Link v-if="canResetPassword || activeTab === 'mobile'" :href="forgotPasswordHref"
              class="underline text-sm text-gray-600 hover:text-gray-900">
              {{ $t("forgot_your_password") }}
            </Link>

            <a-button type="primary" html-type="submit" size="large" class="ml-4" :loading="form.processing">
              {{ $t("login") }}
            </a-button>
          </div>
        </a-form>

        <a-divider>{{ $t("or") }}</a-divider>

        <div>
          <a :href="route('auth.google')"
            class="flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
              <path fill="#4285F4"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
              <path fill="#34A853"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
              <path fill="#FBBC05"
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
              <path fill="#EA4335"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            {{ $t("google_login") }}
          </a>
        </div>
      </div>
    </div>
  </BlankLayout>
</template>