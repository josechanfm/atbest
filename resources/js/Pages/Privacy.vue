<template>
    <DefaultLayout title="私隱政策及服務條款">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">私隱政策及服務條款</h1>
                <p class="text-base text-gray-500 mt-2">Privacy Policy &amp; Terms of Service</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-10">
                <PolicyPrivacy />
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import PolicyPrivacy from '@/Components/PolicyPrivacy.vue';

export default {
    name: 'Privacy',
    components: {
        DefaultLayout,
        PolicyPrivacy,
    },
};
</script>
