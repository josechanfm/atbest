<template>
  <GuestLayout :title="$t('reset_password')">
    <div class="max-w-md mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-center mb-6">
          {{ $t('reset_password') }}
        </h2>

        <a-steps :current="currentStep" class="mb-8">
          <a-step :title="$t('mobile')" />
          <a-step :title="$t('verification')" />
          <a-step :title="$t('new_password')" />
        </a-steps>

        <!-- Step 1: Mobile -->
        <div v-if="currentStep === 0">
          <a-form
            layout="vertical"
            ref="formRef"
            :model="form"
            @finish="sendCode"
            @finishFailed="onFinishFailed"
            :validate-trigger="['submit']"
          >
            <a-form-item
                :label="$t('mobile_number')"
                name="mobile"
                :rules="[
                    { required: true, message: $t('mobile_required') },
                    { pattern: /^6\d{7}$/, message: $t('mobile_invalid') }
                ]"
                :help="$t('format_hint')"
                >
                <a-input-group compact>
                    <a-form-item-rest>
                    <a-input style="width: 20%" value="+853" disabled />
                    </a-form-item-rest>
                    <a-input
                    v-model:value="form.mobile"
                    style="width: 80%"
                    :placeholder="$t('enter_8_digits_starting_with_6')"
                    :maxlength="8"
                    @input="form.mobile = form.mobile.replace(/\D/g, '')"
                    />
                </a-input-group>
            </a-form-item>

            <a-form-item>
              <a-button type="primary" html-type="submit" :loading="sending">
                {{ $t('send_verification_code') }}
              </a-button>
            </a-form-item>
          </a-form>
        </div>

        <!-- Step 2: Verification Code -->
        <div v-else-if="currentStep === 1">
          <a-form
            layout="vertical"
            ref="formRef"
            :model="form"
            @finish="verifyCode"
            @finishFailed="onFinishFailed"
            :validate-trigger="['submit']"
          >
            <a-form-item
              :label="$t('verification_code')"
              name="code"
              :rules="[
                { required: true, message: $t('code_required') },
                { len: 6, message: $t('code_length') }
              ]"
            >
              <a-input
                v-model:value="form.code"
                :placeholder="$t('enter_6_digit_code')"
                :maxlength="6"
                @input="form.code = form.code.replace(/\D/g, '')"
              />
            </a-form-item>

            <div class="flex items-center justify-between">
              <a-button type="primary" html-type="submit" :loading="verifying">
                {{ $t('verify') }}
              </a-button>
              <a-button type="link" @click="resendCode" :disabled="resendTimer > 0">
                {{ resendTimer > 0 ? $t('resend_in') + ' ' + resendTimer + 's' : $t('resend_code') }}
              </a-button>
            </div>
          </a-form>
        </div>

        <!-- Step 3: New Password -->
        <div v-else-if="currentStep === 2">
          <a-form
            layout="vertical"
            ref="formRef"
            :model="form"
            @finish="resetPassword"
            @finishFailed="onFinishFailed"
            :validate-trigger="['submit']"
          >
            <a-form-item
              :label="$t('new_password')"
              name="password"
              :rules="[
                { required: true, message: $t('password_required') },
                { min: 8, message: $t('password_min') }
              ]"
            >
              <a-input-password
                v-model:value="form.password"
                :placeholder="$t('enter_new_password')"
              />
            </a-form-item>

            <a-form-item
              :label="$t('confirm_password')"
              name="password_confirmation"
              :rules="[
                { required: true, message: $t('confirm_password_required') },
                { validator: validatePasswordMatch, trigger: 'submit' }
              ]"
            >
              <a-input-password
                v-model:value="form.password_confirmation"
                :placeholder="$t('re_enter_new_password')"
              />
            </a-form-item>

            <a-form-item>
              <a-button type="primary" html-type="submit" :loading="resetting">
                {{ $t('reset_password') }}
              </a-button>
            </a-form-item>
          </a-form>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { message } from 'ant-design-vue';

export default {
  name: 'MobilePasswordReset',
  components: { GuestLayout },
  data() {
    return {
      currentStep: 0,
      sending: false,
      verifying: false,
      resetting: false,
      resendTimer: 0,
      timerInterval: null,
      form: {
        mobile: '',
        code: '',
        password: '',
        password_confirmation: '',
      },
    };
  },
  methods: {
    // Step 1: Send SMS code
    sendCode() {
        console.log('Sending code to mobile:', this.form);
      this.sending = true;
      this.$inertia.post(route('password.mobile.send'), {
        mobile: '+853' + this.form.mobile,
      }, {
        preserveState: true,
        onSuccess: () => {
          this.sending = false;
          this.currentStep = 1;
          this.startResendTimer();
          message.success(this.$t('code_sent'));
        },
        onError: (errors) => {
          this.sending = false;
          message.error(errors.mobile || this.$t('send_failed'));
        },
      });
    },

    // Step 2: Verify code
    verifyCode() {
      this.verifying = true;
      this.$inertia.post(route('password.mobile.verify'), {
        mobile: '+853' + this.form.mobile,
        code: this.form.code,
      }, {
        preserveState: true,
        onSuccess: () => {
          this.verifying = false;
          this.currentStep = 2;
          message.success(this.$t('code_verified'));
        },
        onError: (errors) => {
          this.verifying = false;
          message.error(errors.code || this.$t('invalid_code'));
        },
      });
    },

    // Step 3: Reset password
    resetPassword() {
      this.resetting = true;
      this.$inertia.post(route('password.mobile.reset'), {
        mobile: '+853' + this.form.mobile,
        code: this.form.code,
        password: this.form.password,
        password_confirmation: this.form.password_confirmation,
      }, {
        preserveState: true,
        onSuccess: () => {
          this.resetting = false;
          message.success(this.$t('password_reset_success'));
          this.$inertia.visit(route('login'));
        },
        onError: (errors) => {
          this.resetting = false;
          message.error(errors.password || this.$t('reset_failed'));
        },
      });
    },

    // Resend code
    resendCode() {
      if (this.resendTimer > 0) return;
      // Reuse the sendCode logic but stay on step 1
      this.sending = true;
      this.$inertia.post(route('password.mobile.send'), {
        mobile: '+853' + this.form.mobile,
      }, {
        preserveState: true,
        onSuccess: () => {
          this.sending = false;
          this.startResendTimer();
          message.success(this.$t('code_sent'));
        },
        onError: (errors) => {
          this.sending = false;
          message.error(errors.mobile || this.$t('send_failed'));
        },
      });
    },

    // Timer for resend
    startResendTimer() {
      this.resendTimer = 60;
      if (this.timerInterval) clearInterval(this.timerInterval);
      this.timerInterval = setInterval(() => {
        this.resendTimer--;
        if (this.resendTimer <= 0) {
          clearInterval(this.timerInterval);
          this.timerInterval = null;
        }
      }, 1000);
    },

    // Password match validator
    validatePasswordMatch(rule, value) {
      if (value !== this.form.password) {
        return Promise.reject(this.$t('passwords_do_not_match'));
      }
      return Promise.resolve();
    },

    // Handle validation errors
    onFinishFailed(errorInfo) {
      // The error messages are already shown by the form items,
      // so we can just log for debugging.
      console.warn('Form validation failed:', errorInfo);
    },
  },
  beforeUnmount() {
    if (this.timerInterval) clearInterval(this.timerInterval);
  },
};
</script>