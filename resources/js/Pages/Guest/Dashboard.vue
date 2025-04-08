<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
</script>

<template>
    <WebsiteLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    This is the page for those who has account but not yet member of any organizations.
                </div>
            </div>
        </div>
        
    </WebsiteLayout>
</template>
