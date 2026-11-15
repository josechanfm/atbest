<template>
    <OrganizerLayout :title="$t('organization_list')">
        <template #header>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ $t('organization_list') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $t('organization_management') }}
                </p>
            </div>
        </template>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
            <div class="overflow-x-auto">
                <a-table
                    :dataSource="organizations"
                    :columns="columns"
                    :pagination="false"
                    :row-class-name="(_record, index) => (index % 2 === 0 ? 'bg-gray-50' : 'bg-white')"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'abbr_en'">
                            <div class="flex items-center">
                                <div
                                    v-if="record.avatar"
                                    class="mr-3 h-8 w-8 overflow-hidden rounded-full"
                                >
                                    <img
                                        :src="record.avatar"
                                        :alt="record.abbr_en"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-600"
                                >
                                    {{ (record.abbr_en || '?').slice(0, 2).toUpperCase() }}
                                </div>
                                <span class="font-medium text-gray-900">
                                    {{ record.abbr_en }}
                                </span>
                            </div>
                        </template>

                        <template v-else-if="column.dataIndex === 'name_zh'">
                            <div class="text-gray-900">{{ record.name_zh }}</div>
                            <div class="text-xs text-gray-500">{{ record.name_en }}</div>
                        </template>

                        <template v-else-if="column.dataIndex === 'email'">
                            <div v-if="record.email" class="flex items-center text-gray-600">
                                <MailOutlined class="mr-2" />
                                {{ record.email }}
                            </div>
                            <span v-else class="text-gray-400">—</span>
                        </template>

                        <template v-else-if="column.dataIndex === 'phone'">
                            <div v-if="record.phone" class="flex items-center text-gray-600">
                                <PhoneOutlined class="mr-2" />
                                {{ record.phone }}
                            </div>
                            <span v-else class="text-gray-400">—</span>
                        </template>

                        <template v-else-if="column.dataIndex === 'operation'">
                            <div class="flex items-center space-x-2">
                                <a-tooltip :title="$t('edit')">
                                    <a-button
                                        :href="route('organizer.organizations.edit', record.id)"
                                        size="small"
                                        class="!border-green-200 !text-green-600 hover:!border-green-300 hover:!bg-green-50"
                                    >
                                        <EditOutlined />
                                    </a-button>
                                </a-tooltip>

                                <a-tooltip :title="$t('organization_switch')">
                                    <a-button
                                        @click="switchOrganization(record)"
                                        size="small"
                                        class="!border-purple-200 !text-purple-600 hover:!border-purple-300 hover:!bg-purple-50"
                                    >
                                        <UserSwitchOutlined />
                                    </a-button>
                                </a-tooltip>
                            </div>
                        </template>

                        <template v-else>
                            <span v-if="record[column.dataIndex]">{{ record[column.dataIndex] }}</span>
                            <span v-else class="text-gray-400">—</span>
                        </template>
                    </template>
                </a-table>
            </div>
        </div>
    </OrganizerLayout>
</template>

<script>
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue';
import {
    EditOutlined,
    UserSwitchOutlined,
    MailOutlined,
    PhoneOutlined,
} from '@ant-design/icons-vue';

export default {
    components: {
        OrganizerLayout,
        EditOutlined,
        UserSwitchOutlined,
        MailOutlined,
        PhoneOutlined,
    },
    props: ['organizations'],
    data() {
        return {
            modal:{
                isOpen:false,
                data:{},
                title:"Modal",
                mode:""
            },
            teacherStateLabels:{},
            columns:[
                {
                    title: 'Abbr.',
                    dataIndex: 'abbr_en',
                    width: 180,
                },{
                    title: 'Full Name',
                    dataIndex: 'name_zh',
                    width: 250,
                },{
                    title: 'Country',
                    dataIndex: 'country',
                    width: 120,
                },{
                    title: 'Phone',
                    dataIndex: 'phone',
                    width: 150,
                },{
                    title: 'Email',
                    dataIndex: 'email',
                    width: 220,
                },{
                    title: 'Action',
                    dataIndex: 'operation',
                    key: 'operation',
                    width: 120,
                    fixed: 'right',
                },
            ],
            rules:{
                name_zh:{required:true},
                mobile:{required:true},
                state:{required:true},
            },
            validateMessages:{
                required: '${label} is required!',
                types: {
                    email: '${label} is not a valid email!',
                    number: '${label} is not a valid number!',
                },
                number: {
                    range: '${label} must be between ${min} and ${max}',
                },
            },
            labelCol: {
                style: {
                width: '150px',
                },
            },
        }
    },
    created(){
    },
    methods: {
        deleteRecord(recordId){
            console.log(recordId);
            if (!confirm('Are you sure want to remove?')) return;
            this.$inertia.delete('/admin/teachers/' + recordId,{
                onSuccess: (page)=>{
                    console.log(page);
                },
                onError: (error)=>{
                    console.log(error);
                }
            });
        },
        createLogin(recordId){
            console.log('create login'+recordId);

        },
        switchOrganization(organization){
            this.$inertia.post(route('organizer.organization.switch',{organization:organization.id}), null, {
                onStart: () => {
                    this.$message.loading(this.$t('organization_switch'), 0);
                },
                onSuccess: () => {
                    this.$message.destroy();
                },
                onError: () => {
                    this.$message.destroy();
                    this.$message.error(this.$t('submit_failed'));
                },
            });
        }
    },
}
</script>