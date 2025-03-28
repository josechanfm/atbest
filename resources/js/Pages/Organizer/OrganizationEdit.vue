<template>
    <OrganizerLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                課程規劃
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
                    <a-row :gutter="5">
                        <a-col :span="8">
                            <a-form-item label="Abbr" name="abbr" :labelCol="{ span: 9 }">
                                <a-input v-model:value="organization.abbr" />
                            </a-form-item>
                        </a-col>
                        <a-col :span="16">
                            <a-form-item label="Name Zh" name="name_zh">
                                <a-input v-model:value="organization.name_zh" />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="5">
                        <a-col :span="8">
                            <a-form-item :label="$t('email')" name="email" :labelCol="{ span: 9 }">
                                <a-input v-model:value="organization.email" />
                            </a-form-item>
                        </a-col>
                        <a-col :span="8">
                            <a-form-item :label="$t('phone')" :labelCol="{ span: 9 }">
                                <a-input v-model:value="organization.phone" />
                            </a-form-item>
                        </a-col>
                        <a-col :span="8">
                            <a-form-item :label="$t('country')" :labelCol="{ span: 9 }">
                                <a-input v-model:value="organization.country" />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-form-item :label="$t('address')">
                        <a-input v-model:value="organization.address" />
                    </a-form-item>
                    <a-form-item label="Website">
                        <a-input v-model:value="organization.href" />
                    </a-form-item>
                    <a-form-item label="Description">
                        <a-input v-model:value="organization.description" />
                    </a-form-item>
                    <a-form-item label="Content">
                        <a-input v-model:value="organization.content" />
                    </a-form-item>
                    <a-form-item label="Card Style">
                        <a-select v-model:value="organization.card_style" :options="cardStyles" />
                    </a-form-item>
                    <a-form-item label="Logo">
                        <a-input v-model:value="organization.logo" />
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
                                        class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 20 16">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                            </svg>
                                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                                <div v-html="$t('upload_drag_drop')"/>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('logo_size_note')}}</p>
                                        </div>
                                        <input id="dropzone-file" type="file" @change="onSelectFile"
                                            accept="image/png, image/gif, image/jpeg" style="display:none" />
                                    </label>
                                </div>

                            </template>
                        </template>
                    </a-form-item>

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
import axios from 'axios';

export default {
    components: {
        OrganizerLayout,
        axios
    },
    props: ['organization'],
    data() {
        return {
            dataFormat: 'YYYY-MM-DD',
            cardStyles: [],
            previewImage: null,
            rules: {
                name_zh: { required: true },
                email: { required: true },
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
    created() {
        axios.get(route("api.config.item", { key: 'card_styles' })).then((resp) => {
            Object.entries(resp.data).forEach(([key, card]) => {
                this.cardStyles.push({ value: key, label: card.name })
            })
        });
    },
    methods: {
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