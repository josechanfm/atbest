<template>
    <MemberLayout title="Edit Business Card">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                編輯數碼名片
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Live Card Preview Banner -->
                <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl p-6 text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl"></div>

                    <div class="relative z-10 flex flex-col items-center text-center gap-4">
                        <div>
                            <template v-if="user.member?.avatar">
                                <img :src="user.member.avatar" alt="Avatar" class="w-20 h-20 rounded-full object-cover border-2 border-white/20 shadow-md" />
                            </template>
                            <template v-else>
                                <div class="w-20 h-20 rounded-full bg-white/10 border-2 border-white/20 flex items-center justify-center text-indigo-200 shadow-md">
                                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                    </svg>
                                </div>
                            </template>
                        </div>

                        <div>
                            <span class="text-xs font-semibold uppercase tracking-widest text-indigo-300 bg-white/10 px-3 py-1 rounded-full">
                                {{ user.member?.organization?.name_zh || user.member?.name_zh || '會員專區' }}
                            </span>
                            <h3 class="text-2xl font-bold mt-2">
                                {{ form.content.name_zh || form.content.name_en || '姓名 Name' }}
                            </h3>
                            <p class="text-sm text-indigo-200 mt-0.5">
                                {{ form.content.title_zh || form.content.title_en || '職稱 Title' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 sm:p-8">
                    <form @submit.prevent="submit" class="space-y-8">

                        <!-- Section 1: Card Face Core Info -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
                                名片正面基本資訊 (Card Face Core Info)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">中文姓名 (Name ZH)</label>
                                    <input type="text" v-model="form.content.name_zh" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm" placeholder="請輸入中文姓名" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">英文姓名 (Name EN)</label>
                                    <input type="text" v-model="form.content.name_en" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm" placeholder="Please enter English name" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">中文職稱 / 身份 (Title ZH)</label>
                                    <input type="text" v-model="form.content.title_zh" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm" placeholder="例如：理事長 / 永久會員" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">英文職稱 / 身份 (Title EN)</label>
                                    <input type="text" v-model="form.content.title_en" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm" placeholder="e.g. Director / Member" />
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Card Face Dynamic Contact Fields -->
                        <div>
                            <div class="mb-4 pb-2 border-b border-gray-100">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">
                                    名片正面顯示欄位 (Card Face Contacts)
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">選擇並新增欲顯示於名片正面的聯絡資料 (電話、電郵、社交媒體等)</p>
                            </div>

                            <div v-if="cardFaceFields.length === 0" class="text-sm text-gray-400 text-center py-6 bg-gray-50 rounded-2xl border border-dashed border-gray-200 mb-4">
                                尚未新增正面聯絡資料。
                            </div>

                            <!-- List of Fields -->
                            <div class="space-y-3 mb-4">
                                <div v-for="(field, index) in cardFaceFields" :key="index" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-gray-50 p-3 rounded-2xl border border-gray-200">
                                    <!-- Field Type Selector -->
                                    <select v-model="field.key" class="w-full sm:w-1/3 rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                        <optgroup label="基本聯絡 (Basic Contact)">
                                            <option value="mobile">手機 (Mobile)</option>
                                            <option value="phone">電話 (Phone)</option>
                                            <option value="office_tel">辦公室電話 (Office Tel)</option>
                                            <option value="email">電郵 (Email)</option>
                                            <option value="fax">傳真 (Fax)</option>
                                            <option value="postal_address">通訊地址 (Postal Address)</option>
                                        </optgroup>
                                        <optgroup label="網頁 / 專業連結 (Link)">
                                            <option value="website">網站 (Website)</option>
                                            <option value="linkedin">LinkedIn</option>
                                            <option value="facebook">Facebook</option>
                                            <option value="youtube">YouTube</option>
                                            <option value="github">GitHub</option>
                                        </optgroup>
                                        <optgroup label="社交帳號 (Account / Handle)">
                                            <option value="line">LINE ID</option>
                                            <option value="wechat">微信 WeChat ID</option>
                                            <option value="whatsapp">WhatsApp</option>
                                            <option value="telegram">Telegram</option>
                                            <option value="instagram">Instagram</option>
                                            <option value="x_twitter">X / Twitter</option>
                                            <option value="tiktok">TikTok</option>
                                        </optgroup>
                                        <optgroup label="其他 (Other)">
                                            <option value="custom">自定義欄位 (Custom)</option>
                                        </optgroup>
                                    </select>

                                    <!-- Custom Key Input if selected -->
                                    <input
                                        v-if="field.key === 'custom'"
                                        type="text"
                                        v-model="field.customKey"
                                        placeholder="自定義欄位名稱"
                                        class="w-full sm:w-1/4 rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                    />

                                    <!-- Field Value Input -->
                                    <input
                                        type="text"
                                        v-model="field.value"
                                        :placeholder="fieldPlaceholder(field.key)"
                                        class="flex-1 rounded-xl border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                                    />

                                    <!-- Remove Button -->
                                    <button
                                        type="button"
                                        @click="removeCardFaceField(index)"
                                        class="p-2 text-gray-400 hover:text-red-600 transition-colors self-end sm:self-center"
                                        title="刪除欄位"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Add Button -->
                            <div class="flex justify-center pt-1">
                                <button
                                    type="button"
                                    @click="addCardFaceField"
                                    class="w-full sm:w-auto inline-flex items-center justify-center text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-5 py-2.5 rounded-xl border border-indigo-100 transition-colors"
                                >
                                    + 新增正面欄位
                                </button>
                            </div>
                        </div>

                        <!-- Section 3: Personal Bio -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
                                個人簡介 (Personal Bio)
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">簡介摘要 (Summary)</label>
                                    <input type="text" v-model="form.bio.summary" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm" placeholder="一句話個人簡介或座右銘" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">詳細履歷 / 經歷 (Details)</label>
                                    <SimpleEditor
                                        v-model="form.bio.details"
                                        placeholder="詳細介紹您的專業經歷、公職服務或專長領域..."
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                            <!-- Preview Button (left) -->
                            <a
                                :href="route('member.businessCard.show', businessCard.uuid)"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-white hover:bg-gray-50 text-indigo-600 text-xs font-bold rounded-xl border border-indigo-200 hover:border-indigo-300 shadow-sm transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>預覽名片</span>
                            </a>

                            <!-- Save Button (right) -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md disabled:opacity-50 transition-all"
                            >
                                <span v-if="form.processing">儲存中...</span>
                                <span v-else>儲存名片設定</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </MemberLayout>
</template>

<script>
import MemberLayout from '@/Layouts/MemberLayout.vue';
import SimpleEditor from '@/Components/SimpleEditor.vue';
import { useForm } from '@inertiajs/vue3';

const STANDARD_OPTIONS = [
    // Basic contact
    'mobile', 'phone', 'office_tel', 'email', 'fax', 'postal_address',
    // Link-based social
    'website', 'linkedin', 'facebook', 'youtube', 'github',
    // Account-based social
    'line', 'wechat', 'whatsapp', 'telegram', 'instagram', 'x_twitter', 'tiktok',
];

const PLACEHOLDERS = {
    // Basic contact
    mobile: '例如：9123 4567',
    phone: '例如：2345 6789',
    office_tel: '例如：2345 6789',
    email: '例如：name@example.com',
    fax: '例如：2345 6789',
    postal_address: '輸入完整通訊地址',
    // Link-based (full URL or domain)
    website: '例如：https://example.com',
    linkedin: '例如：https://linkedin.com/in/username',
    facebook: '例如：https://facebook.com/username',
    youtube: '例如：https://youtube.com/@channel',
    github: '例如：https://github.com/username',
    // Account-based (handle/ID only)
    line: '輸入 LINE ID',
    wechat: '輸入微信號 WeChat ID',
    whatsapp: '輸入 WhatsApp 號碼',
    telegram: '輸入 Telegram 用戶名',
    instagram: '輸入 Instagram 帳號 (不需 @)',
    x_twitter: '輸入 X / Twitter 帳號 (不需 @)',
    tiktok: '輸入 TikTok 帳號 (不需 @)',
};

export default {
    components: {
        MemberLayout,
        SimpleEditor,
    },
    props: {
        businessCard: {
            type: Object,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        }
    },
    data() {
        const content = this.businessCard?.content || {};
        const bio = this.businessCard?.bio || {};

        const coreKeys = ['name_zh', 'name_en', 'title_zh', 'title_en'];

        const cardFaceFields = Object.entries(content)
            .filter(([key]) => !coreKeys.includes(key))
            .map(([key, value]) => ({
                key: STANDARD_OPTIONS.includes(key) ? key : 'custom',
                customKey: STANDARD_OPTIONS.includes(key) ? '' : key,
                value: value,
            }));

        return {
            cardFaceFields,
            form: useForm({
                content: {
                    name_zh: content.name_zh || this.user.member?.name_zh || '',
                    name_en: content.name_en || this.user.member?.name_en || '',
                    title_zh: content.title_zh || '',
                    title_en: content.title_en || '',
                },
                bio: {
                    summary: bio.summary || '',
                    details: bio.details || '',
                }
            })
        }
    },
    methods: {
        addCardFaceField() {
            this.cardFaceFields.push({ key: 'mobile', customKey: '', value: '' });
        },
        removeCardFaceField(index) {
            this.cardFaceFields.splice(index, 1);
        },
        fieldPlaceholder(key) {
            return PLACEHOLDERS[key] || '輸入詳細內容';
        },
        getFieldLabel(field) {
            const labels = {
                mobile: '手機',
                phone: '電話',
                office_tel: '辦公室電話',
                email: '電郵',
                fax: '傳真',
                postal_address: '通訊地址',
                website: '網站',
                linkedin: 'LinkedIn',
                facebook: 'Facebook',
                youtube: 'YouTube',
                github: 'GitHub',
                line: 'LINE',
                wechat: '微信',
                whatsapp: 'WhatsApp',
                telegram: 'Telegram',
                instagram: 'Instagram',
                x_twitter: 'X / Twitter',
                tiktok: 'TikTok',
            };
            return field.key === 'custom' ? (field.customKey || '自定義') : (labels[field.key] || field.key);
        },
        submit() {
            const finalContent = { ...this.form.content };

            this.cardFaceFields.forEach(field => {
                const k = field.key === 'custom' ? field.customKey.trim() : field.key;
                if (k) {
                    finalContent[k] = field.value;
                }
            });

            this.form.content = finalContent;

            if (this.form.bio.details === '<p><br></p>' || !this.form.bio.details?.trim()) {
                this.form.bio.details = '';
            }

            this.form.put(route('member.businessCard.update'), {
                preserveScroll: true,
            });
        }
    }
}
</script>