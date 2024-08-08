<template>
<WidgetLayout title="Dashboard">
    <div class="font-sans text-gray-900 antialiased">
        <div class="container mx-auto pt-5">
            <div class="bg-white relative shadow rounded-lg overflow-x-auto">
                <a-button as="link" type="primary" :href="route('widget.admin.polls.index')">Poll</a-button>
            </div>
        </div>
    </div>
</WidgetLayout>
</template>


<script>
import WidgetLayout from '@/Layouts/WidgetLayout.vue';

export default {
    components: {
        WidgetLayout,
    },
    props: ['polls', 'configs'],
    data() {
        return {

        }
    },
    created() {
    },
    methods: {
    }
}

</script>