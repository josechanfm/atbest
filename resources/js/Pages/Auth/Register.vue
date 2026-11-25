<script setup>
import BlankLayout from "@/Layouts/BlankLayout.vue";
import PolicyPrivacy from "@/Components/PolicyPrivacy.vue";
import { reactive, ref, onBeforeUnmount } from "vue";
import { router, Head } from "@inertiajs/vue3";
import { Modal, message } from "ant-design-vue";

defineProps({
    organizations: {
        type: Array,
        default: () => [],
    },
});

const formRef = ref(null);
const submitting = ref(false);
const sendingCode = ref(false);
const verifyingCode = ref(false);
const preRegistered = ref(false);
const mobileVerified = ref(false);
const policyModalOpen = ref(false);
const agreeError = ref("");
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
    agree: false,
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

const checkAgree = () => {
    if (!formState.agree) {
        agreeError.value = "請先閱讀並同意《服務條款與私隱政策》";
        return false;
    }
    agreeError.value = "";
    return true;
};

const acceptPolicyFromModal = () => {
    formState.agree = true;
    agreeError.value = "";
    policyModalOpen.value = false;
};

const validatePasswordConfirmation = async (_rule, value) => {
    if (value && value !== formState.password) {
        return Promise.reject(new Error("兩次輸入的密碼不一致"));
    }
    return Promise.resolve();
};

// Step 1: Pre-register (validate fields including agree, store in session, send 6-digit SMS)
const handlePreRegister = async () => {
    const isAgreed = checkAgree();
    try {
        await formRef.value?.validateFields([
            "name",
            "given_name",
            "family_name",
            "email",
            "registration_code",
            "password",
            "password_confirmation",
            "mobile",
        ]);
    } catch {
        return;
    }

    if (!isAgreed) return;

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

// Step 2: Verify 6-digit SMS code
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
                message.success("手機驗證成功");
            },
            onError: (err) => {
                verifyingCode.value = false;
                mobileVerified.value = false;
                message.error(err.code ? Object.values(err).flat().join("\n") : "驗證碼無效或已過期");
            },
        }
    );
};

// Step 3: Convert pre-registration into final account
const onFinish = () => {
    if (!checkAgree()) return;

    if (!preRegistered.value) {
        handlePreRegister();
        return;
    }

    if (!mobileVerified.value) {
        message.error("請先完成6位數簡訊驗證");
        return;
    }

    submitting.value = true;

    router.post(
        route("register.store"),
        {
            verification_code: formState.verification_code,
            agree: formState.agree,
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
    checkAgree();
    console.log("Failed:", errorInfo);
};

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <BlankLayout>
        <Head :title="$t('account_registration')" />

        <div class="max-w-2xl mx-auto">
            <div class="p-5 bg-white shadow-md overflow-hidden rounded-lg">
                <h2 class="text-xl font-bold text-center text-gray-800 mb-4">{{ $t("account_registration") }}</h2>

                <a-form
                    ref="formRef"
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

                    <!-- Required Policy & Privacy Checkbox -->
                    <div class="my-4">
                        <div class="flex items-start gap-2">
                            <a-checkbox
                                v-model:checked="formState.agree"
                                @change="checkAgree"
                            />
                            <div class="text-sm text-gray-700 leading-snug">
                                我已閱讀並同意
                                <a
                                    href="#"
                                    class="text-blue-600 hover:underline font-medium"
                                    @click.prevent.stop="policyModalOpen = true"
                                >
                                    《服務條款與私隱政策》
                                </a>
                            </div>
                        </div>
                        <p v-if="agreeError" class="mt-1 text-xs text-red-500">
                            {{ agreeError }}
                        </p>
                    </div>

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
            </div>
        </div>

        <!-- Policy & Privacy Modal -->
        <a-modal
            v-model:open="policyModalOpen"
            title="服務條款與私隱政策"
            :width="720"
            centered
        >
            <div class="max-h-[65vh] overflow-y-auto pr-2">
                <PolicyPrivacy />
            </div>

            <template #footer>
                <a-button @click="policyModalOpen = false">關閉</a-button>
                <a-button type="primary" @click="acceptPolicyFromModal">
                    我已閱讀並同意
                </a-button>
            </template>
        </a-modal>
    </BlankLayout>
</template>

<style scoped>
/* Prevent Ant Design Vue from stacking labels above inputs on screens less than 576px */
@media (max-width: 575px) {
    :deep(.ant-form-horizontal .ant-form-item-label) {
        flex: 0 0 25%;
        max-width: 25%;
        padding-bottom: 0;
        padding-right: 12px;
        text-align: right;
        white-space: normal;
        line-height: 1.3;
    }

    :deep(.ant-form-horizontal .ant-form-item-control) {
        flex: 0 0 75%;
        max-width: 75%;
    }
}

:deep(.ant-modal-confirm-content) {
    white-space: pre-line;
}
</style>