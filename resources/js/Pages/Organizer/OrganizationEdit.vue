<template>
    <OrganizerLayout :title="$t('organization_edit')">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('organization_edit') }}
            </h2>
        </template>
        <div class="bg-white relative shadow rounded-lg p-5">
            <a-form
                ref="formRef"
                :model="organization"
                layout="vertical"
                :rules="rules"
                :validate-messages="validateMessages"
                @finish="onFinish"
                enctype="multipart/form-data"
            >
                <section class="mb-5 rounded-lg border border-gray-200 p-4 sm:p-5" aria-labelledby="basic-information-heading">
                    <h3 id="basic-information-heading" class="mb-4 text-base font-semibold text-gray-900">{{ $t('basic_information') }}</h3>
                    <a-row :gutter="5">
                        <a-col :xs="12" :sm="12">
                            <a-form-item :label="$t('abbreviation')" name="abbr_en">
                                <a-input v-model:value="organization.abbr_en" type="input" allow-clear />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="12" :sm="12">
                            <a-form-item :label="$t('invitation_code')" name="registration_code">
                                <a-input v-model:value="organization.registration_code" type="input" allow-clear />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="5">
                        <a-col :xs="24" :sm="8">
                            <a-form-item :label="$t('organization_name_zh')" name="name_zh">
                                <a-input v-model:value="organization.name_zh" type="input" allow-clear />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="8">
                            <a-form-item :label="$t('organization_name_en')">
                                <a-input v-model:value="organization.name_en" type="input" allow-clear />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="8">
                            <a-form-item :label="$t('organization_name_pt')">
                                <a-input v-model:value="organization.name_pt" type="input" allow-clear />
                            </a-form-item>
                        </a-col>
                    </a-row>
                </section>

                <section class="mb-5 rounded-lg border border-gray-200 p-4 sm:p-5" aria-labelledby="contact-information-heading">
                    <h3 id="contact-information-heading" class="mb-4 text-base font-semibold text-gray-900">{{ $t('contact_information') }}</h3>
                    <a-row :gutter="5">
                        <a-col :xs="24" :sm="8">
                            <a-form-item :label="$t('email')" name="email">
                                <a-input v-model:value="organization.email" type="input" />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="8">
                            <a-form-item :label="$t('phone')">
                                <a-input v-model:value="organization.phone" type="input" />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="8">
                            <a-form-item :label="$t('country')">
                                <a-input v-model:value="organization.country" type="input" />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-form-item :label="$t('address')">
                        <a-input v-model:value="organization.address" type="input" />
                    </a-form-item>
                    <a-form-item :label="$t('website')">
                        <a-input v-model:value="organization.href" type="input" />
                    </a-form-item>
                </section>

                <section class="mb-5 rounded-lg border border-gray-200 p-4 sm:p-5" aria-labelledby="description-heading">
                    <h3 id="description-heading" class="mb-4 text-base font-semibold text-gray-900">{{ $t('description') }}</h3>
                    <a-form-item :label="$t('description')">
                        <a-textarea v-model:value="organization.description" :rows="3" />
                    </a-form-item>
                    <a-form-item :label="$t('content')">
                        <a-textarea v-model:value="organization.content" :rows="4" />
                    </a-form-item>
                </section>

                <div class="flex flex-row item-center justify-center">
                    <a-button type="primary" @click="onSubmit">{{ $t('submit') }}</a-button>
                </div>
            </a-form>
        </div>
    </OrganizerLayout>
</template>

<script>
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue';

export default {
    components: { OrganizerLayout },
    props: ['organization'],
    data() {
        return {
            rules: {
                abbr_en: { required: true },
                name_zh: { required: true },
                email: [
                    { required: true, message: 'Email is required!' },
                    { type: 'email', message: 'Email is not a valid email!' },
                ],
            },
            validateMessages: {
                required: '${label} is required!',
                types: {
                    email: '${label} is not a valid email!',
                    number: '${label} is not a valid number!',
                },
                number: {
                    range: '${label} must be between ${min} and ${max}',
                },
            },
        };
    },
    methods: {
        async onSubmit() {
            try {
                await this.$refs.formRef.validateFields();
                this.onFinish();
            } catch (errInfo) {
                alert('Validation failed!');
                // Validation failed — do not post
            }
        },
        onFinish() {
            this.organization._method = 'PATCH';
            this.$inertia.post(route('organizer.organizations.update', this.organization.id), this.organization, {
                forceFormData: true,
            });
        },
    },
};
</script>