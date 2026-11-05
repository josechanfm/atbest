<template>
    <GuestLayout>
        <Head title="邀請無效" />
        <div class="text-center py-8">
            <h1 class="text-xl font-bold text-gray-900 mb-2">邀請無效</h1>
            <p class="text-sm text-gray-500">
                {{ reason === 'used' ? '此邀請已被使用。' : '此邀請已過期。' }}
            </p>
            <Link :href="route('login')" class="inline-block mt-6 text-indigo-600 text-sm font-bold hover:underline">
                返回登入
            </Link>
        </div>
    </GuestLayout>
</template>

<script>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

export default {
    components: { GuestLayout, Head, Link },
    props: {
        reason: { type: String, default: 'expired' },
    },
};
</script>