<script setup>
import { reactive, ref, onBeforeUnmount } from "vue";
import { router, Head } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import { Modal, message } from "ant-design-vue";

defineProps({
    organizations: {
        type: Array,
        default: () => [],
    },
});

const submitting = ref(false);
const sendingCode = ref(false);
const verifyingCode = ref(false);
const preRegistered = ref(false);
const mobileVerified = ref(false);
const resendTimer = ref(0);
let timerInterval = null;

const formState = reactive({
    name: "",
    given_name: "",
    family_name: "",
    mobile: "",
    verification_code: "",
    email: "",
    registration_code: "",
    password: "",
    password_confirmation: "",
});

const normalizeMobile = (val) => {
    const digits = (val || "").replace(/\D/g, "");
    if (digits.startsWith("853")) {
        return "+" + digits;
    }
    return "+853" + digits;
};

const startResendTimer = () => {
    resendTimer.value = 60;
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        resendTimer.value--;
        if (resendTimer.value <= 0) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }, 1000);
};

const showErrorModal = (err) => {
    Modal.error({
        title: "註冊失敗",
        content: err ? Object.values(err).flat().join("\n") : "請檢查輸入資料",
        okText: "確認",
    });
};

// Step 1: Pre-register (validate fields, store in session, send 4-digit SMS)
const handlePreRegister = () => {
    sendingCode.value = true;
    mobileVerified.value = false;

    router.post(
        route("register.preRegister"),
        {
            ...formState,
            mobile: normalizeMobile(formState.mobile),
        },
        {
            preserveState: true,
            onSuccess: () => {
                sendingCode.value = false;
                preRegistered.value = true;
                startResendTimer();
                message.success("預先登記完成，驗證碼已發送");
            },
            onError: (err) => {
                sendingCode.value = false;
                showErrorModal(err);
            },
        }
    );
};

// Step 2: Verify 4-digit SMS code
const handleVerifyCode = () => {
    if (!/^\d{6}$/.test(formState.verification_code)) {
        message.error("請輸入6位數字驗證碼");
        return;
    }

    verifyingCode.value = true;

    router.post(
        route("register.verifyCode"),
        {
            code: formState.verification_code,
        },
        {
            preserveState: true,
            onSuccess: () => {
                verifyingCode.value = false;
                mobileVerified.value = true;
                message.success("手機驗證成功，請點擊提交完成註冊");
            },
            onError: (err) => {
                verifyingCode.value = false;
                mobileVerified.value = false;
                message.error(err.code ? Object.values(err).flat().join("\n") : "驗證碼無效或已過期");
            },
        }
    );
};

const validatePasswordConfirmation = async (_rule, value) => {
    if (value && value !== formState.password) {
        return Promise.reject(new Error("兩次輸入的密碼不一致"));
    }
    return Promise.resolve();
};

// Step 3: Convert pre-registration into final account
const onFinish = () => {
    if (!preRegistered.value) {
        handlePreRegister();
        return;
    }
    console.log(mobileVerified.value)
    if (!mobileVerified.value) {
        message.error("請先完成6位數簡訊驗證");
        return;
    }

    submitting.value = true;

    router.post(
        route("register.store"),
        {
            verification_code: formState.verification_code,
        },
        {
            onFinish: () => {
                submitting.value = false;
            },
            onError: (err) => {
                showErrorModal(err);
            },
        }
    );
};

const onFinishFailed = (errorInfo) => {
    console.log("Failed:", errorInfo);
};

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head :title="$t('account_registration')" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>
        <a-form
            :model="formState"
            name="register"
            layout="horizontal"
            :label-col="{ span: 6 }"
            :wrapper-col="{ span: 18 }"
            autocomplete="off"
            @finish="onFinish"
            @finishFailed="onFinishFailed"
        >
            <a-form-item
                :label="$t('name')"
                name="name"
                :rules="[{ required: true, message: $t('please_input_name') }]"
            >
                <a-input type="input" v-model:value="formState.name" size="large" :disabled="preRegistered" />
            </a-form-item>

            <div class="flex flex-col sm:flex-row sm:gap-4">
                <a-form-item
                    class="w-full sm:flex-[3]"
                    :label="$t('given_name')"
                    name="given_name"
                    :label-col="{ xs: { span: 6 }, sm: { span: 10 } }"
                    :wrapper-col="{ xs: { span: 18 }, sm: { span: 14 } }"
                    :rules="[{ required: true, message: $t('please_input_given_name') }]"
                >
                    <a-input type="input" v-model:value="formState.given_name" size="large" :disabled="preRegistered" />
                </a-form-item>

                <a-form-item
                    class="w-full sm:flex-[2]"
                    :label="$t('family_name')"
                    name="family_name"
                    :label-col="{ xs: { span: 6 }, sm: { span: 10 } }"
                    :wrapper-col="{ xs: { span: 18 }, sm: { span: 14 } }"
                    :rules="[{ required: true, message: $t('please_input_family_name') }]"
                >
                    <a-input type="input" v-model:value="formState.family_name" size="large" :disabled="preRegistered" />
                </a-form-item>
            </div>

            <a-form-item
                :label="$t('login_email')"
                name="email"
                :rules="[
                    { required: true, message: $t('please_input_email') },
                    { type: 'email', message: $t('please_input_email') },
                ]"
            >
                <a-input type="input" v-model:value="formState.email" size="large" :disabled="preRegistered" />
            </a-form-item>

            <a-form-item
                :label="$t('registration_code')"
                name="registration_code"
                :rules="[{ required: true, message: $t('please_input_registration_code') }]"
            >
                <a-input type="input" v-model:value="formState.registration_code" size="large" :disabled="preRegistered" />
            </a-form-item>

            <a-form-item
                :label="$t('password')"
                name="password"
                :rules="[{ required: true, message: $t('please_input_password') }]"
            >
                <a-input-password v-model:value="formState.password" size="large" :disabled="preRegistered" />
            </a-form-item>

            <a-form-item
                :label="$t('confirm_password')"
                name="password_confirmation"
                :rules="[
                    { required: true, message: $t('please_input_confirm_password') },
                    { validator: validatePasswordConfirmation },
                ]"
            >
                <a-input-password v-model:value="formState.password_confirmation" size="large" :disabled="preRegistered" />
            </a-form-item>

            <a-form-item
                :label="$t('mobile')"
                name="mobile"
                :rules="[{ required: true, message: $t('please_input_mobile') }]"
            >
                <div class="flex gap-2">
                    <a-input
                        type="input"
                        v-model:value="formState.mobile"
                        size="large"
                        :disabled="preRegistered"
                        placeholder="+853 6XXXXXXX"
                    />
                                <a-button
                type="default"
                size="large"
                :loading="sendingCode"
                :disabled="resendTimer > 0 || mobileVerified"
                @click="handlePreRegister"
            >
                {{
                    mobileVerified
                        ? "已驗證"
                        : resendTimer > 0
                            ? `${resendTimer}s`
                            : $t("send_verification_code")
                }}
            </a-button>

                </div>
                
            </a-form-item>

            <a-form-item
                v-if="preRegistered || mobileVerified"
                :label="$t('verification_code')"
                name="verification_code"
                :rules="[{ required: true, message: $t('code_required') }]"
            >
                <div class="flex gap-2">
                    <a-input
                        type="input"
                        v-model:value="formState.verification_code"
                        size="large"
                        :maxlength="6"
                        :disabled="mobileVerified"
                        placeholder="6位數驗證碼"
                        @input="formState.verification_code = formState.verification_code.replace(/\D/g, '')"
                    />
                    <a-button
                        type="primary"
                        ghost
                        size="large"
                        :loading="verifyingCode"
                        :disabled="mobileVerified"
                        @click="handleVerifyCode"
                    >
                        {{ mobileVerified ? "已驗證" : $t("verify") }}
                    </a-button>
                </div>
                
            </a-form-item>



            <div class="mt-6" v-if="preRegistered && mobileVerified">
                <a-button
                    type="primary"
                    html-type="submit"
                    size="large"
                    block
                    :loading="submitting"
                >
                    {{ $t("submit") }}
                </a-button>
            </div>
        </a-form>
    </AuthenticationCard>
</template>

<style scoped>
/* Prevent Ant Design Vue from stacking labels above inputs on screens < 576px */
@media (max-width: 575px) {
    :deep(.ant-form-horizontal .ant-form-item-label) {
        flex: 0 0 30%;
        max-width: 30%;
        padding-bottom: 0;
        padding-right: 12px;
        text-align: right;
        white-space: normal;
        line-height: 1.3;
    }

    :deep(.ant-form-horizontal .ant-form-item-control) {
        flex: 0 0 70%;
        max-width: 70%;
    }
}

:deep(.ant-modal-confirm-content) {
    white-space: pre-line;
}
</style>