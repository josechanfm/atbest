<template>
    <OrganizerLayout :title="$t('organization_edit')">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('organization_edit') }}
            </h2>
        </template>
            <div class="bg-white relative shadow rounded-lg p-5">
                <a-form 
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
                            <a-col :span="8">
                                <a-form-item label="Abbr" name="abbr" :labelCol="{ span: 9 }">
                                    <a-input type="input" v-model:value="organization.abbr_en" />
                                </a-form-item>
                            </a-col>
                            <a-col :span="16">
                                <a-form-item label="Name Zh" name="name_zh">
                                    <a-input type="input" v-model:value="organization.name_zh" />
                                </a-form-item>
                            </a-col>
                        </a-row>
                    </section>

                    <section class="mb-5 rounded-lg border border-gray-200 p-4 sm:p-5" aria-labelledby="contact-information-heading">
                        <h3 id="contact-information-heading" class="mb-4 text-base font-semibold text-gray-900">{{ $t('contact_information') }}</h3>
                        <a-row :gutter="5">
                            <a-col :span="8">
                                <a-form-item :label="$t('email')" name="email" :labelCol="{ span: 9 }">
                                    <a-input type="input" v-model:value="organization.email" />
                                </a-form-item>
                            </a-col>
                            <a-col :span="8">
                                <a-form-item :label="$t('phone')" :labelCol="{ span: 9 }">
                                    <a-input type="input" v-model:value="organization.phone" />
                                </a-form-item>
                            </a-col>
                            <a-col :span="8">
                                <a-form-item :label="$t('country')" :labelCol="{ span: 9 }">
                                    <a-input type="input" v-model:value="organization.country" />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-form-item :label="$t('address')">
                            <a-input type="input" v-model:value="organization.address" />
                        </a-form-item>
                        <a-form-item label="Website">
                            <a-input type="input" v-model:value="organization.href" />
                        </a-form-item>
                    </section>

                    <section class="mb-5 rounded-lg border border-gray-200 p-4 sm:p-5" aria-labelledby="description-heading">
                        <h3 id="description-heading" class="mb-4 text-base font-semibold text-gray-900">{{ $t('description') }}</h3>
                        <a-form-item label="Description">
                            <a-input type="input" v-model:value="organization.description" />
                        </a-form-item>
                        <a-form-item label="Content">
                            <a-input type="input" v-model:value="organization.content" />
                        </a-form-item>
                    </section>

                    <section class="mb-5 rounded-lg border border-gray-200 p-4 sm:p-5" aria-labelledby="card-style-heading">
                        <h3 id="card-style-heading" class="mb-4 text-base font-semibold text-gray-900">{{ $t('card_style') }}</h3>
                        <a-form-item label="Card Style">
                            <a-select :value="selectedCardStyle" :options="cardStyleOptions" @change="selectCardStyle" />
                        </a-form-item>
                    </section>

                    <section class="mb-5 rounded-lg border border-gray-200 p-4 sm:p-5" aria-labelledby="logo-heading">
                        <h3 id="logo-heading" class="mb-4 text-base font-semibold text-gray-900">{{ $t('logo') }}</h3>
                        <a-form-item label="Logo">
                            <a-input type="input" v-model:value="organization.logo" />
                        </a-form-item>

                        <a-form-item :label="$t('logo')">

                            <template v-if="organization.logo">
                                <img :src="organization.logo" width="300px" />
                                <a @click="onDeleteLogo(organization)">Delete</a>
                            </template>

                            <template v-else>
                                <template v-if="previewImage">
                                    <img :src="previewImage" class="w-64" />
                                    <a @click="onRemoveLogo">Remove</a>
                                </template>

                                <template v-else>
                                    <div class="flex items-center justify-center w-64">
                                        <label for="dropzone-file"
                                            class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-8 h-8 mb-4 text-gray-500"
                                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 20 16">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                                </svg>
                                                <p class="mb-2 text-sm text-gray-500">
                                                    <div v-html="$t('upload_drag_drop')"/>
                                                </p>
                                                <p class="text-xs text-gray-500">{{ $t('logo_size_note')}}</p>
                                            </div>
                                            <input id="dropzone-file" type="file" @change="onSelectFile"
                                                accept="image/png, image/gif, image/jpeg" style="display:none" />
                                        </label>
                                    </div>

                                </template>
                            </template>
                        </a-form-item>
                    </section>

                    <div class="flex flex-row item-center justify-center">
                        <a-button type="primary" html-type="submit">{{ $t('submit') }}</a-button>
                    </div>
                </a-form>
            </div>
    </OrganizerLayout>
</template>

<script>
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue';
import { Modal } from 'ant-design-vue';

export default {
    components: {
        OrganizerLayout,
    },
    props: ['organization', 'cardStyles'],
    data() {
        return {
            dataFormat: 'YYYY-MM-DD',
            previewImage: null,
            rules: {
                name_zh: { required: true },
                // email: { required: true },
                state: { required: true },
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
        }
    },
    computed: {
        cardStyleOptions() {
            return Object.entries(this.cardStyles || {}).map(([value, style]) => ({
                value, label: style.name, style,
            }));
        },
        selectedCardStyle() {
            const style = this.organization.card_style;
            return typeof style === 'string' ? style : style?.preset;
        },
    },
    methods: {
        selectCardStyle(value) {
            const preset = this.cardStyleOptions.find(style => style.value === value);
            if (!preset) {
                console.error('Unknown card style preset:', value);
                return;
            }
            this.organization.card_style = { ...preset.style, preset: value };
        },
        onSelectFile(event) {
            const file = event.target.files[0]
            if (file.size > 1024 *1024) {
                Modal.error({
                    title: this.$t('file_size_over'),
                    content: this.$t('logo_size_note'),
                });
                return false
            }
            this.organization.logo_upload = file
            this.previewImage = URL.createObjectURL(file)

        },
        onRemoveLogo() {
            this.organization.logo_upload = null
            this.previewImage = null
        },
        onDeleteLogo(feature) {
            this.$inertia.post(route('organizer.organization.deleteLogo', this.organization), {
                onSuccess: (page) => {
                    console.log(page)
                },
                onError: (err) => {
                    console.log(err);
                },
            })

        },

        onFinish() {
            console.log(this.organization);
            this.organization._method = "PATCH"
            this.$inertia.post(route('organizer.organizations.update', this.organization.id), this.organization, {
                onSuccess: (page) => {
                    console.log(page)
                },
                onError: (err) => {
                    console.log(err);
                }
            });
        },
    },
}
</script>