<template>
  <AdminLayout :title="$t('message_test.title')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t('message_test.title') }}
      </h2>
    </template>

    <div class="py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Email Gateway -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
              <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <MailOutlined /> {{ $t('message_test.email_gateway') }}
              </h3>
              <a-tag :color="emailConfigured ? 'green' : 'red'">
                {{ emailConfigured ? $t('message_test.configured') : $t('message_test.not_configured') }}
              </a-tag>
            </div>

            <div class="p-4">
              <!-- Health check -->
              <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-600">{{ $t('message_test.health_check') }}</span>
                  <a-button
                    size="small"
                    :loading="emailHealth.loading"
                    @click="runEmailHealth"
                  >
                    <HeartOutlined /> {{ $t('message_test.run_health_check') }}
                  </a-button>
                </div>
                <div v-if="emailHealth.result" class="text-sm">
                  <a-alert
                    :type="emailHealth.result.ok ? 'success' : 'error'"
                    :message="emailHealth.result.message"
                    show-icon
                  />
                </div>
                <div v-else class="text-sm text-gray-400">{{ $t('message_test.no_health_result') }}</div>
              </div>

              <a-divider />

              <!-- Test send -->
              <a-form ref="emailFormRef" :model="emailForm" :rules="emailRules" layout="vertical">
                <a-form-item :label="$t('message_test.to')" name="to">
                  <a-input v-model:value="emailForm.to" :placeholder="$t('message_test.enter_email')" allow-clear />
                </a-form-item>
                <a-form-item :label="$t('message_test.subject')" name="subject">
                  <a-input v-model:value="emailForm.subject" :placeholder="$t('message_test.enter_subject')" allow-clear />
                </a-form-item>
                <a-form-item :label="$t('message_test.content')" name="content">
                  <a-textarea
                    v-model:value="emailForm.content"
                    :placeholder="$t('message_test.enter_message_content')"
                    :rows="4"
                    show-count
                    :maxlength="5000"
                  />
                </a-form-item>
                <a-form-item>
                  <a-button type="primary" :loading="emailSending" @click="sendTestEmail">
                    <SendOutlined /> {{ $t('message_test.send_test_email') }}
                  </a-button>
                </a-form-item>
              </a-form>

              <div v-if="emailResult" class="text-sm">
                <a-alert
                  :type="emailResult.success ? 'success' : 'error'"
                  :message="emailResult.success ? $t('message_test.email_sent_successfully') : $t('message_test.email_send_failed')"
                  :description="emailResult.success ? '' : (emailResult.error || '')"
                  show-icon
                />
                <div v-if="emailResult.elapsedMs != null" class="text-gray-400 mt-1">
                  {{ $t('message_test.elapsed') }}: {{ emailResult.elapsedMs }}ms
                </div>
              </div>
            </div>
          </div>

          <!-- SMS Gateway -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
              <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <MessageOutlined /> {{ $t('message_test.sms_gateway') }}
              </h3>
              <a-tag :color="smsConfigured ? 'green' : 'red'">
                {{ smsConfigured ? $t('message_test.configured') : $t('message_test.not_configured') }}
              </a-tag>
            </div>

            <div class="p-4">
              <!-- Health check -->
              <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-600">{{ $t('message_test.health_check') }}</span>
                  <a-button
                    size="small"
                    :loading="smsHealth.loading"
                    @click="runSmsHealth"
                  >
                    <HeartOutlined /> {{ $t('message_test.run_health_check') }}
                  </a-button>
                </div>
                <div v-if="smsHealth.result" class="text-sm">
                  <a-alert
                    :type="smsHealth.result.ok ? 'success' : 'error'"
                    :message="smsHealth.result.message"
                    show-icon
                  />
                </div>
                <div v-else class="text-sm text-gray-400">{{ $t('message_test.no_health_result') }}</div>
              </div>

              <a-divider />

              <!-- Test send -->
              <a-form ref="smsFormRef" :model="smsForm" :rules="smsRules" layout="vertical">
                <a-form-item :label="$t('message_test.mobile')" name="mobile">
                  <a-input v-model:value="smsForm.mobile" :placeholder="$t('message_test.enter_mobile')" allow-clear />
                </a-form-item>
                <a-form-item :label="$t('message_test.content')" name="content">
                  <a-textarea
                    v-model:value="smsForm.content"
                    :placeholder="$t('message_test.enter_message_content')"
                    :rows="4"
                    show-count
                    :maxlength="500"
                  />
                </a-form-item>
                <a-form-item>
                  <a-button type="primary" :loading="smsSending" @click="sendTestSms">
                    <SendOutlined /> {{ $t('message_test.send_test_sms') }}
                  </a-button>
                </a-form-item>
              </a-form>

              <div v-if="smsResult" class="text-sm">
                <a-alert
                  :type="smsResult.success ? 'success' : 'error'"
                  :message="smsResult.success ? $t('message_test.sms_sent_successfully') : $t('message_test.sms_send_failed')"
                  :description="smsResult.success ? '' : (smsResult.error || '')"
                  show-icon
                />
                <div v-if="smsResult.elapsedMs != null" class="text-gray-400 mt-1">
                  {{ $t('message_test.elapsed') }}: {{ smsResult.elapsedMs }}ms
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import {
  MailOutlined,
  MessageOutlined,
  HeartOutlined,
  SendOutlined,
} from '@ant-design/icons-vue';

export default {
  name: 'MessageTest',
  components: {
    AdminLayout,
    MailOutlined,
    MessageOutlined,
    HeartOutlined,
    SendOutlined,
  },
  props: {
    emailConfigured: { type: Boolean, default: false },
    smsConfigured: { type: Boolean, default: false },
  },
  data() {
    return {
      emailHealth: { loading: false, result: null },
      smsHealth: { loading: false, result: null },
      emailSending: false,
      smsSending: false,
      emailResult: null,
      smsResult: null,
      emailForm: {
        to: '',
        subject: '',
        content: '',
      },
      smsForm: {
        mobile: '',
        content: '',
      },
      emailRules: {
        to: [
          { required: true, message: this.$t('message_test.email_required'), trigger: 'blur' },
          { type: 'email', message: this.$t('message_test.invalid_email'), trigger: 'blur' },
        ],
        subject: [
          { required: true, message: this.$t('message_test.subject_required'), trigger: 'blur' },
        ],
        content: [
          { required: true, message: this.$t('message_test.content_required'), trigger: 'blur' },
        ],
      },
      smsRules: {
        mobile: [
          { required: true, message: this.$t('message_test.mobile_required'), trigger: 'blur' },
          { pattern: /^[0-9+\-() ]+$/, message: this.$t('message_test.invalid_mobile'), trigger: 'blur' },
        ],
        content: [
          { required: true, message: this.$t('message_test.content_required'), trigger: 'blur' },
          { max: 500, message: this.$t('message_test.content_too_long'), trigger: 'blur' },
        ],
      },
    };
  },
  methods: {
    async runEmailHealth() {
      this.emailHealth.loading = true;
      this.emailHealth.result = null;
      try {
        const { data } = await axios.get(route('admin.messageTest.health'));
        this.emailHealth.result = data.email;
      } catch (error) {
        this.emailHealth.result = {
          ok: false,
          message: error.response?.data?.message || error.message || this.$t('message_test.request_failed'),
        };
      } finally {
        this.emailHealth.loading = false;
      }
    },
    async runSmsHealth() {
      this.smsHealth.loading = true;
      this.smsHealth.result = null;
      try {
        const { data } = await axios.get(route('admin.messageTest.health'));
        this.smsHealth.result = data.sms;
      } catch (error) {
        this.smsHealth.result = {
          ok: false,
          message: error.response?.data?.message || error.message || this.$t('message_test.request_failed'),
        };
      } finally {
        this.smsHealth.loading = false;
      }
    },
    async sendTestEmail() {
      try {
        await this.$refs.emailFormRef.validate();
      } catch (error) {
        return;
      }

      this.emailSending = true;
      this.emailResult = null;
      try {
        const { data } = await axios.post(route('admin.messageTest.sendEmail'), this.emailForm);
        this.emailResult = data;
        if (data.success) {
          this.$message.success(this.$t('message_test.email_sent_successfully'));
        } else {
          this.$message.error(this.$t('message_test.email_send_failed'));
        }
      } catch (error) {
        this.emailResult = {
          success: false,
          error: error.response?.data?.message || error.message || this.$t('message_test.request_failed'),
        };
        this.$message.error(this.$t('message_test.email_send_failed'));
      } finally {
        this.emailSending = false;
      }
    },
    async sendTestSms() {
      try {
        await this.$refs.smsFormRef.validate();
      } catch (error) {
        return;
      }

      this.smsSending = true;
      this.smsResult = null;
      try {
        const { data } = await axios.post(route('admin.messageTest.sendSms'), this.smsForm);
        this.smsResult = data;
        if (data.success) {
          this.$message.success(this.$t('message_test.sms_sent_successfully'));
        } else {
          this.$message.error(this.$t('message_test.sms_send_failed'));
        }
      } catch (error) {
        this.smsResult = {
          success: false,
          error: error.response?.data?.message || error.message || this.$t('message_test.request_failed'),
        };
        this.$message.error(this.$t('message_test.sms_send_failed'));
      } finally {
        this.smsSending = false;
      }
    },
  },
};
</script>
