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
            this.$inertia.post(route('registration.store'), this.formState, {
                onSuccess: (page) => {
                    console.log(page);
                    //this.modal.isOpen = false;
                },
                onError: (err) => {
                    console.log(err.code);
                      Modal.error({
                            title: this.$t('registration_error'),
                            content: this.$t(err.code),
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
    <DefaultLayout title="Dashboard">
        <div class="flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <a-typography-title :level="3">{{$t('account_registration')}}</a-typography-title>
            <div class="w-full max-w-lg mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"><!--v-if-->
                <a-form :model="formState" name="basic" layout="vertical" autocomplete="off" @finish="onFinish"
                    @finishFailed="onFinishFailed">
                    <a-form-item :label="$t('given_name')" name="given_name"
                        :rules="[{ required: true, message: 'Please input your given name!' }]">
                        <a-input v-model:value="formState.given_name" />
                    </a-form-item>
                    <!-- <a-form-item :label="$t('middle_name')" name="middle_name">
                        <a-input v-model:value="formState.middle_name" />
                    </a-form-item> -->
                    <a-form-item :label="$t('family_name')" name="family_name"
                        :rules="[{ required: true, message: 'Please input your family_name!' }]">
                        <a-input v-model:value="formState.family_name" />
                    </a-form-item>
                    <a-form-item :label="$t('affiliate')" name="organization_id"
                        :rules="[{ required: true, message: 'Please input your organization belongs to!' }]">
                        <a-select v-model:value="formState.organization_id" :options="organizations"
                            :fieldNames="{ value: 'id', label: 'name_zh' }" />
                    </a-form-item>
                    <a-form-item :label="$t('registration_code')" name="registration_code"
                        :rules="[{ required: true, message: 'Please input the organization registration' }]">
                        <a-input v-model:value="formState.registration_code" />
                    </a-form-item>
                    <a-form-item :label="$t('login_email')" name="email"
                        :rules="[{ required: true, message: 'Please input your email!' }]">
                        <a-input v-model:value="formState.email" type="email" />
                    </a-form-item>
                    <a-form-item :label="$t('password')" name="password"
                        :rules="[{ required: true, message: 'Please input your password!' }]">
                        <a-input-password v-model:value="formState.password" />
                    </a-form-item>
                    <div class="flex flex-row item-center justify-center">
                        <a-button type="primary" html-type="submit">{{$t('submit')}}</a-button>
                    </div>
                </a-form>
            </div>
        </div>
    </DefaultLayout>
</template>

