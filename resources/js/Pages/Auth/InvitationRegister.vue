<template>
    <GuestLayout>
        <Head title="邀請註冊" />

        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-900">邀請註冊</h1>
            <p class="text-sm text-gray-500 mt-1">
                <strong>{{ inviter.name }}</strong> 邀請您加入
            </p>
        </div>

        <a-form
            ref="formRef"
            :model="form"
            :rules="rules"
            layout="vertical"
            autocomplete="off"
            @finish="submit"
        >
            <!-- Name (required) -->
            <a-form-item
                label="姓名 (Name)"
                name="name"
                :help="form.errors.name"
                :validate-status="form.errors.name ? 'error' : undefined"
            >
                <a-input
                    type="input"
                    v-model:value="form.name"
                    size="large"
                    :disabled="codeSent"
                    placeholder="請輸入您的姓名"
                />
            </a-form-item>

            <!-- Email (optional) -->
            <a-form-item
                label="電郵 (Email)"
                name="email"
                :help="sendError || form.errors.email"
                :validate-status="(sendError || form.errors.email) ? 'error' : undefined"
            >
                <a-input
                    type="input"
                    v-model:value="form.email"
                    size="large"
                    :readonly="!!prefilledEmail"
                    :disabled="true"
                    placeholder="(選填) 請輸入電郵"
                />
            </a-form-item>

            <!-- Password (required) -->
            <a-form-item
                label="密碼 (Password)"
                name="password"
                :help="form.errors.password"
                :validate-status="form.errors.password ? 'error' : undefined"
            >
                <a-input-password
                    v-model:value="form.password"
                    size="large"
                    :disabled="codeSent"
                />
            </a-form-item>

            <!-- Confirm Password (required) -->
            <a-form-item
                label="確認密碼 (Confirm Password)"
                name="password_confirmation"
                :help="form.errors.password_confirmation"
                :validate-status="form.errors.password_confirmation ? 'error' : undefined"
            >
                <a-input-password
                    v-model:value="form.password_confirmation"
                    size="large"
                    :disabled="codeSent"
                />
            </a-form-item>

                        <!-- Mobile (required) -->
                <a-form-item
                    label="手機號碼 (Mobile)"
                    name="mobile"
                    :help="form.errors.mobile || sendError"
                    :validate-status="(form.errors.mobile || sendError) ? 'error' : undefined"
                >
                <div class="flex gap-2">
                    <a-input
                        type="input"
                        v-model:value="form.mobile"
                        size="large"
                        addon-before="+853"
                        :disabled="codeSent"
                        placeholder="6XXXXXXX"
                        @input="form.mobile = form.mobile.replace(/\D/g, '').slice(0, 8)"
                    />
                    <a-button
                        type="primary"
                        size="large"
                        :loading="sendingCode"
                        :disabled="resendTimer > 0 || mobileVerified"
                        @click="handleSendCode"
                    >
                        <span v-if="sendingCode">發送中...</span>
                        <span v-else-if="mobileVerified">已驗證</span>
                        <span v-else-if="resendTimer > 0">{{ resendTimer }}s</span>
                        <span v-else>發送驗證碼</span>
                    </a-button>
                </div>
            </a-form-item>

            <!-- Verification code (shown after sending) -->
            <a-form-item
                v-if="codeSent"
                label="驗證碼 (Verification Code)"
                name="verification_code"
                :help="codeError || form.errors.verification_code"
                :validate-status="(codeError || form.errors.verification_code) ? 'error' : undefined"
            >
                <div class="flex gap-2">
                    <a-input
                        type="input"
                        v-model:value="form.verification_code"
                        size="large"
                        :maxlength="6"
                        :disabled="mobileVerified"
                        placeholder="6位數字驗證碼"
                        @input="form.verification_code = form.verification_code.replace(/\D/g, '')"
                    />
                    <a-button
                        type="primary"
                        ghost
                        size="large"
                        :loading="verifyingCode"
                        :disabled="mobileVerified"
                        @click="handleVerifyCode"
                    >
                        <span v-if="verifyingCode">驗證中...</span>
                        <span v-else-if="mobileVerified">已驗證</span>
                        <span v-else>驗證</span>
                    </a-button>
                </div>
            </a-form-item>


            <a-button
                type="primary"
                html-type="submit"
                size="large"
                block
                :loading="form.processing"
                :disabled="!mobileVerified"
            >
                <span v-if="form.processing">建立中...</span>
                <span v-else-if="!mobileVerified">請先完成手機驗證</span>
                <span v-else>建立帳號</span>
            </a-button>
        </a-form>
    </GuestLayout>
</template>

<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

export default {
    components: { GuestLayout, Head },
    props: {
        token:   { type: String, required: true },
        inviter: { type: Object, required: true },
        email:   { type: String, default: '' },
    },
    data() {
        const prefilledEmail = this.email || '';

        const validatePasswordConfirm = async (_rule, value) => {
            if (value && value !== this.form.password) {
                return Promise.reject(new Error('兩次輸入的密碼不一致'));
            }
            return Promise.resolve();
        };

        return {
            prefilledEmail,
            form: useForm({
                name: '',
                mobile: '',
                verification_code: '',
                email: prefilledEmail,
                password: '',
                password_confirmation: '',
            }),
            rules: {
                name: [
                    { required: true, message: '請輸入您的姓名', trigger: 'blur' },
                ],
                mobile: [
                    { required: true, message: '請輸入手機號碼', trigger: 'blur' },
                    { pattern: /^6\d{7}$/, message: '請輸入8位數字（以6開頭）', trigger: 'blur' },
                ],
                verification_code: [
                    { required: true, message: '請輸入6位數字驗證碼', trigger: 'blur' },
                    { pattern: /^\d{6}$/, message: '驗證碼必須為6位數字', trigger: 'blur' },
                ],
                email: [
                    { type: 'email', message: '請輸入有效的電郵地址', trigger: 'blur' },
                ],
                password: [
                    { required: true, message: '請輸入密碼', trigger: 'blur' },
                    { min: 8, message: '密碼長度至少需要8個字元', trigger: 'blur' },
                ],
                password_confirmation: [
                    { required: true, message: '請再次輸入密碼', trigger: 'blur' },
                    { validator: validatePasswordConfirm, trigger: 'blur' },
                ],
            },
            codeSent: false,
            mobileVerified: false,
            sendingCode: false,
            verifyingCode: false,
            resendTimer: 0,
            sendError: '',
            codeError: '',
            timerInterval: null,
        };
    },
    beforeUnmount() {
        if (this.timerInterval) clearInterval(this.timerInterval);
    },
    methods: {
        normalizeMobile(val) {
            const digits = (val || '').replace(/\D/g, '');
            if (digits.startsWith('853')) {
                return '+' + digits;
            }
            return '+853' + digits;
        },

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

        // Step 1: validate fields + send 6-digit SMS code
        async handleSendCode() {
            try {
                await this.$refs.formRef.validateFields([
                    'name',
                    'mobile',
                    'email',
                    'password',
                    'password_confirmation',
                ]);
            } catch {
                return;
            }

            this.sendingCode = true;
            this.sendError = '';
            this.mobileVerified = false;

            this.form
                .transform((data) => ({
                    ...data,
                    mobile: this.normalizeMobile(data.mobile),
                }))
                .post(route('invitations.sendCode', this.token), {
                    preserveState: true,
                    onSuccess: () => {
                        this.sendingCode = false;
                        this.codeSent = true;
                        this.startResendTimer();
                    },
                    onError: (errors) => {
                        this.sendingCode = false;
                        // Only show general/SMS errors not tied to a specific input field
                        const fieldKeys = ['name', 'email', 'password', 'password_confirmation', 'mobile'];
                        const generalErrors = Object.entries(errors)
                            .filter(([key]) => !fieldKeys.includes(key))
                            .map(([, val]) => val)
                            .flat()
                            .join(' ');
                        this.sendError = generalErrors;
                    },
                });
        },

        // Step 2: verify the 6-digit SMS code
        async handleVerifyCode() {
            try {
                await this.$refs.formRef.validateFields(['verification_code']);
            } catch {
                return;
            }

            this.verifyingCode = true;
            this.codeError = '';

            router.post(
                route('invitations.verifyCode', this.token),
                {
                    code: this.form.verification_code,
                },
                {
                    preserveState: true,
                    onSuccess: () => {
                        this.verifyingCode = false;
                        this.mobileVerified = true;
                    },
                    onError: (errors) => {
                        this.verifyingCode = false;
                        this.codeError = Object.values(errors).flat().join(' ');
                    },
                }
            );
        },

        // Step 3: create the account (only after mobile verification)
        submit() {
            if (!this.mobileVerified) return;

            this.form
                .transform((data) => ({
                    ...data,
                    mobile: this.normalizeMobile(data.mobile),
                }))
                .post(route('invitations.register', this.token));
        },
    },
};
</script>