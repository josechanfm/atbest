<template>
    <DefaultLayout title="系統介紹">
        <div class="mx-auto">
            <!-- 標題 -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">系統介紹</h1>
                <p class="text-base text-gray-500 mt-2">智滙精英（Atbest）· NPO 精英會員平台</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-10 space-y-10">
                <!-- 一、平台概述 -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="inline-block w-1.5 h-6 bg-[#0081C8] rounded"></span>
                        一、平台概述
                    </h2>
                    <p class="text-gray-700 leading-7 text-justify">
                        「智滙精英（Atbest）會員平台」是一個紮根澳門、專為凝聚本地各領域優秀社團領袖與專業人士而設的非牟利組織（NPO）社團社交平台。平台匯聚澳門多元社團社群，配備專屬電子會員卡及一站式會籍管理系統，讓成員隨時輕鬆管理個人會籍、拓展跨會人脈網絡，並尊享全方位會員禮遇。
                    </p>
                    <p class="text-gray-700 leading-7 text-justify mt-3">
                        平台亦致力打造充滿活力的澳門社團互動圈，持續提供社團聯誼活動、專業培訓課程、本地行業前沿資訊及特區政策法規解讀，促進跨界交流與資源共享，攜手推動澳門社團及社會的高質量發展。
                    </p>
                </section>

                <!-- 二、主要功能與特色 -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="inline-block w-1.5 h-6 bg-[#0081C8] rounded"></span>
                        二、主要功能與特色
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-[#0081C8] mb-2">社團資訊平台</h3>
                            <p class="text-sm text-gray-600 leading-6">
                                為社團提供介紹其業務及展示其歷史的專屬網站，充當社團面向大眾的資訊平台，提升社團形象與能見度。
                            </p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-[#0081C8] mb-2">電子會員卡</h3>
                            <p class="text-sm text-gray-600 leading-6">
                                每位會員均獲配專屬電子會員卡，可隨時查看會籍狀態、會員編號及所屬社團，方便出示與驗證。
                            </p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-[#0081C8] mb-2">會員資訊端</h3>
                            <p class="text-sm text-gray-600 leading-6">
                                提供會員專屬入口，可查看個人資料、活動記錄，並進行線上報名、意見反饋等互動功能，增強會員參與感。
                            </p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-[#0081C8] mb-2">行政管理端</h3>
                            <p class="text-sm text-gray-600 leading-6">
                                後台管理系統整合會員資料管理、活動籌辦、財務收支追蹤及報表生成功能，提升社團運作效率與決策品質。
                            </p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-[#0081C8] mb-2">活動與資訊</h3>
                            <p class="text-sm text-gray-600 leading-6">
                                集中發佈社團聯誼活動、專業培訓課程、最新消息及政策法規解讀，讓會員第一時間掌握平台動態。
                            </p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                            <h3 class="font-semibold text-[#0081C8] mb-2">多語言支援</h3>
                            <p class="text-sm text-gray-600 leading-6">
                                平台支援中文、英文及葡文三種語言，方便不同背景的用戶使用，並可隨時切換語言偏好。
                            </p>
                        </div>
                    </div>
                </section>

                <!-- 三、註冊政策 -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="inline-block w-1.5 h-6 bg-[#0081C8] rounded"></span>
                        三、註冊政策
                    </h2>
                    <p class="text-gray-700 leading-7 text-justify mb-4">
                        本平台採行「邀請制」會籍管理，確保會員網絡的質素與可信度。註冊時須完成手機號碼驗證（以簡訊驗證碼確認），並須選擇以下其中一種方式加入：
                    </p>

                    <div class="space-y-4">
                        <!-- 方式一：邀請註冊 -->
                        <div class="border-l-4 border-[#0081C8] bg-blue-50/60 rounded-r-lg p-5">
                            <h3 class="font-semibold text-gray-800 mb-1">方式一：邀請註冊</h3>
                            <p class="text-sm text-gray-600 leading-6">
                                由現有會員或社團管理員向您發出邀請。您透過邀請連結進入註冊頁面，填寫個人資料並完成手機驗證後，即可加入邀請方所屬的社團。
                            </p>
                        </div>

                        <!-- 方式二：註冊編號 -->
                        <div class="border-l-4 border-emerald-500 bg-emerald-50/60 rounded-r-lg p-5">
                            <h3 class="font-semibold text-gray-800 mb-1">方式二：註冊編號註冊</h3>
                            <p class="text-sm text-gray-600 leading-6">
                                如欲加入特定社團，可向該社團索取專屬的「註冊編號」（Registration Code）。於註冊時輸入該編號，並完成手機驗證後，即可加入您所選擇的社團。
                            </p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 leading-6 mt-4">
                        請注意：註冊編號由您欲加入的社團提供，每個編號對應特定社團。如無有效邀請或註冊編號，將無法完成會籍註冊。
                    </p>
                </section>

                <!-- 四、聯絡 -->
                <section>
                    <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="inline-block w-1.5 h-6 bg-[#0081C8] rounded"></span>
                        四、聯絡我們
                    </h2>
                    <p class="text-gray-700 leading-7 text-justify mb-4">
                        如對平台功能或註冊流程有任何查詢，歡迎透過以下方式與我們聯絡，我們將樂意為您提供協助。
                    </p>
                    <div class="space-y-2 text-gray-700">
                        <p class="flex items-center gap-2">
                            <span class="font-semibold text-gray-800">電郵：</span>
                            <a href="mailto:info@atbest.net" class="text-[#0081C8] hover:underline">info@atbest.net</a>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="font-semibold text-gray-800">電話：</span>
                            <a href="tel:+85363816386" class="text-[#0081C8] hover:underline">+853 63816386</a>
                        </p>
                    </div>

                    <!-- 線上諮詢表格 -->
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h3 class="font-semibold text-gray-800 mb-4">線上諮詢</h3>

                        <a-alert v-if="status" :message="$t(status)" type="success" show-icon class="mb-4" />

                        <a-form :model="form" layout="vertical" autocomplete="off" @finish="submit">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                                <a-form-item :label="$t('enquiry.name')" name="name"
                                    :validate-status="form.errors.name ? 'error' : undefined" :help="form.errors.name"
                                    :rules="[{ required: true, message: $t('enquiry.name_required') }]">
                                    <a-input type="input" v-model:value="form.name" :placeholder="$t('enquiry.name')" />
                                </a-form-item>

                                <a-form-item :label="$t('enquiry.email')" name="email"
                                    :validate-status="form.errors.email ? 'error' : undefined" :help="form.errors.email"
                                    :rules="[
                                        { required: true, message: $t('enquiry.email_required') },
                                        { type: 'email', message: $t('enquiry.email_invalid') },
                                    ]">
                                    <a-input type="input" v-model:value="form.email" :placeholder="$t('enquiry.email')" />
                                </a-form-item>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                                <a-form-item :label="$t('enquiry.phone')" name="phone"
                                    :validate-status="form.errors.phone ? 'error' : undefined" :help="form.errors.phone">
                                    <a-input type="input" v-model:value="form.phone" :placeholder="$t('enquiry.phone')" />
                                </a-form-item>

                                <a-form-item :label="$t('enquiry.subject')" name="subject"
                                    :validate-status="form.errors.subject ? 'error' : undefined" :help="form.errors.subject">
                                    <a-input type="input" v-model:value="form.subject" :placeholder="$t('enquiry.subject')" />
                                </a-form-item>
                            </div>

                            <a-form-item :label="$t('enquiry.message')" name="message"
                                :validate-status="form.errors.message ? 'error' : undefined" :help="form.errors.message"
                                :rules="[{ required: true, message: $t('enquiry.message_required') }]">
                                <a-textarea v-model:value="form.message" :rows="4" :placeholder="$t('enquiry.message_placeholder')" />
                            </a-form-item>

                            <a-form-item v-if="form.errors['g-recaptcha-response']" name="g-recaptcha-response"
                                validate-status="error"
                                :help="form.errors['g-recaptcha-response']">
                                <span />
                            </a-form-item>

                            <a-button type="primary" html-type="submit" size="large" :loading="form.processing">
                                {{ $t('enquiry.submit') }}
                            </a-button>
                        </a-form>
                    </div>
                </section>
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { useForm } from '@inertiajs/vue3';

export default {
    name: 'SystemIntro',
    components: {
        DefaultLayout
    },
    props: {
        status: { type: String, default: null },
        recaptchaSiteKey: { type: String, default: null },
    },
    data() {
        return {
            form: useForm({
                name: '',
                email: '',
                phone: '',
                subject: '',
                message: '',
                'g-recaptcha-response': '',
            }),
        };
    },
    methods: {
        async submit() {
            if (this.recaptchaSiteKey && window.grecaptcha) {
                try {
                    const token = await window.grecaptcha.execute(this.recaptchaSiteKey, { action: 'enquiry' });
                    this.form['g-recaptcha-response'] = token;
                } catch (e) {
                    this.$message.error(this.$t('enquiry.recaptcha_failed'));
                    return;
                }
            }
            this.form.post(route('enquiry.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.form.reset('name', 'email', 'phone', 'subject', 'message', 'g-recaptcha-response');
                },
            });
        },
    },
};
</script>