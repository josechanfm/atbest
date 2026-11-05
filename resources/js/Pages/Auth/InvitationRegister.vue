<template>
    <GuestLayout>
        <Head title="邀請註冊" />

        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-900">邀請註冊</h1>
            <p class="text-sm text-gray-500 mt-1">
                <strong>{{ inviter.name }}</strong> 邀請您加入
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Name (required) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    姓名 (Name) <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    autocomplete="name"
                    class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    placeholder="請輸入您的姓名"
                />
                <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
            </div>

            <!-- Mobile (required) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    手機號碼 (Mobile) <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.mobile"
                    type="tel"
                    inputmode="tel"
                    required
                    autocomplete="tel"
                    class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    placeholder="請輸入手機號碼"
                />
                <p v-if="form.errors.mobile" class="text-xs text-red-500 mt-1">{{ form.errors.mobile }}</p>
            </div>

            <!-- Email (optional) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    電郵 (Email)
                </label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    :readonly="!!prefilledEmail"
                    class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                    :class="prefilledEmail && 'bg-gray-100 cursor-not-allowed'"
                    placeholder="(選填) 請輸入電郵"
                />
                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
            </div>

            <!-- Password (required) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    密碼 (Password) <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                />
                <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</p>
            </div>

            <!-- Confirm Password (required) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    確認密碼 (Confirm Password) <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md disabled:opacity-50 transition-all"
            >
                <span v-if="form.processing">建立中...</span>
                <span v-else>建立帳號</span>
            </button>
        </form>
    </GuestLayout>
</template>

<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

export default {
    components: { GuestLayout, Head },
    props: {
        token:   { type: String, required: true },
        inviter: { type: Object, required: true },
        email:   { type: String, default: '' },
    },
    data() {
        const prefilledEmail = this.email || '';
        return {
            prefilledEmail,
            form: useForm({
                name: '',
                mobile: '',
                email: prefilledEmail,
                password: '',
                password_confirmation: '',
            }),
        };
    },
    methods: {
        submit() {
            this.form.post(route('invitations.register', this.token));
        },
    },
};
</script>