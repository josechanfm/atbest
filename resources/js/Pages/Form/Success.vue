<template>
<DefaultLayout title="Dashboard">
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">表格例表</h2>
    </template>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="mt-8 p-4 bg-white overflow-hidden shadow sm:rounded-lg">
            <div class="ant-table ant-table-bordered">
                <div class="ant-table-container">
                    <div class="ant-table-content">
                        <table id="applicationSuccess" class="w-full border-collapse rounded-lg overflow-hidden shadow-sm">
                            <tbody class="divide-y divide-gray-200">
                                <template v-for="record in entry_records" :key="record.id">
                                    <tr v-if="record.form_field.type == 'photo'" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-1/3">{{ record.form_field.field_label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <img :src="record.field_value" class="w-[120px] h-auto rounded-md border border-gray-200" />
                                        </td>
                                    </tr>
                                    <tr v-else-if="record.form_field.type == 'radio'" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-1/3">{{ record.form_field.field_label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{
                                                record.form_field.options.find(
                                                (x) => x.value == record.field_value
                                                ).label
                                            }}
                                        </td>
                                    </tr>
                                    <tr v-else-if="record.form_field.type == 'checkbox'" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-1/3">{{ record.form_field.field_label }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{
                                                JSON.parse(record.field_value)
                                                .map((x) => {
                                                    return record.form_field.options.find(
                                                    (y) => y.value == x
                                                    ).label;
                                                })
                                                .join(", ")
                                            }}
                                        </td>
                                    </tr>
                                    <tr v-else class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-1/3">{{ record.form_field.field_label }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.field_value }}</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="flex justify-between py-10">
                <div>
                    <a :href="route('host')">返回主頁</a>
                </div>
                <!-- <div>
            <inertia-link :href="route('form.receipt',entry)">打印表格</inertia-link>
          </div> -->
                <div>
                    <a :href="route('forms')">活動列表</a>
                </div>
            </div>
        </div>
    </div>
</DefaultLayout>
</template>

<script>
import DefaultLayout from "@/Layouts/DefaultLayout.vue";

export default {
    components: {
        DefaultLayout,
    },
    props: ["form", "entry_records", "entry"],
    data() {
        return {};
    },
    created() {},
    methods: {},
};
</script>
