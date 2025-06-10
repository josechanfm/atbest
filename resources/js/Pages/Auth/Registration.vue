<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Modal } from 'ant-design-vue';

export default {
    components: {
        DefaultLayout
    },
    props: ['organizations'],
    data() {
        return {
            formState: {
                username: '',
                password: '',
            },
        }
    },
    methods: {
        onFinish(values) {
            // this.$inertia.post(route('registration.store'), this.formState, {
            this.$inertia.post(route('register.store'), this.formState, {
                onSuccess: (page) => {
                    console.log(page);
                    //this.modal.isOpen = false;
                },
                onError: (err) => {
                    console.log(err);

                    Modal.error({
                        title: this.$t('registration_error'),
                        content: err ? Object.values(err).join("\r\n") : '',
                        okText:"確認"
                    });
                }
            });

        },
        onFinishFailed(errorInfo) {
            console.log('Failed:', errorInfo);
        }
    }
}

</script>

<template>
    <!-- <DefaultLayout title="Dashboard"> -->
        <div class="register-background flex flex-col h-screen sm:justify-center items-center pt-14 p-6 sm:pt-0">
            
            <a-typography-title :level="2" class="text-xl">{{$t('account_registration')}}</a-typography-title>
            <div class="w-full max-w-lg mt-6 px-6 py-4 bg-gray-50 shadow-md overflow-hidden sm:rounded-lg"><!--v-if-->
                <a-form :model="formState" name="basic" layout="vertical" autocomplete="off" @finish="onFinish"
                    @finishFailed="onFinishFailed">
                    <a-form-item :label="$t('name')" name="name"
                        :rules="[{ required: true, message: $t('please_input_name') }]">
                        <a-input type="input" v-model:value="formState.name" />
                    </a-form-item>
                    <a-form-item :label="$t('given_name')" name="given_name"
                        :rules="[{ required: true, message: $t('please_input_given_name') }]">
                        <a-input type="input" v-model:value="formState.given_name" />
                    </a-form-item>
                    <!-- <a-form-item :label="$t('middle_name')" name="middle_name">
                        <a-input type="input" v-model:value="formState.middle_name" />
                    </a-form-item> -->
                    <a-form-item :label="$t('family_name')" name="family_name"
                        :rules="[{ required: true, message: $t('please_input_family_name') }]">
                        <a-input type="input" v-model:value="formState.family_name" />
                    </a-form-item>
                    <!-- <a-form-item :label="$t('affiliate')" name="organization_id"
                        :rules="[{ required: true, message: 'Please input your organization belongs to!' }]">
                        <a-select v-model:value="formState.organization_id" :options="organizations"
                            :fieldNames="{ value: 'id', label: 'name_zh' }" />
                    </a-form-item> -->
                    <a-form-item :label="$t('registration_code')" name="registration_code"
                        :rules="[{ required: true, message: $t('please_input_registration_code') }]">
                        <a-input type="input" v-model:value="formState.registration_code" />
                    </a-form-item>
                    <a-form-item :label="$t('login_email')" name="email"
                        :rules="[{ required: true, message: $t('please_input_email') }]">
                        <a-input type="input" v-model:value="formState.email"/>
                    </a-form-item>
                    <a-form-item :label="$t('password')" name="password"
                        :rules="[{ required: true, message: $t('please_input_password') }]">
                        <a-input-password v-model:value="formState.password" />
                    </a-form-item>
                    <a-form-item :label="$t('confirm_password')" name="password_confirmation"
                        :rules="[{ required: true, message: $t('please_input_confirm_password') }]">
                        <a-input-password v-model:value="formState.password_confirmation" />
                    </a-form-item>
                    <div class="flex flex-row item-center justify-center">
                        <a-button type="primary" html-type="submit">{{$t('submit')}}</a-button>
                    </div>
                </a-form>
            </div>
        </div>
    <!-- </DefaultLayout> -->
</template>


<style >
.register-background {
    background: rgb(210, 220, 230);
    background: linear-gradient(135deg, rgba(160, 175, 200, 0.3) 0%, rgba(190, 205, 220, 0.583) 25%, rgba(200, 215, 225, 0.538) 60%, rgba(210, 220, 230, 0.8) 100%);
}
.ant-modal-confirm-content{
    white-space: pre-line;
}
</style>
