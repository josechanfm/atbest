<template>
<WidgetLayout title="Dashboard">

    <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Widgets
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div>我們正在努力中,</div>
                <div>耐心等待....馬上就來!</div>
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