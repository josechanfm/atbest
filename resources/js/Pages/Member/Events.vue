<template>
    <MemberLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Events
            </h2>
        </template>

        <a-table :dataSource="events" :columns="columns">
                <template #headerCell="{column}">
                    {{ column.i18n?$t(column.i18n):column.title}}
                </template>
                <template #bodyCell="{column, text, record, index}">
                    <template v-if="column.dataIndex=='operation'">
                        <a-button @click="editRecord(record)">{{$t('edit')}}</a-button>
                        <a-button @click="deleteRecord(record.id)">{{$t('delete')}}</a-button>
                    </template>
                    <template v-else-if="column.dataIndex=='published'">
                        {{record.published?'Yes':'No'}}
                    </template>
                    <template v-else>
                        {{record[column.dataIndex]}}
                    </template>
                </template>
            </a-table>


    </MemberLayout>

</template>

<script>
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { NotificationTwoTone } from '@ant-design/icons-vue';
import { defineComponent, reactive } from 'vue';

export default {
    components: {
        MemberLayout,
    },
    props: ['events'],
    data() {
        return {
            targetKeys:[],
            columns:[
                {
                    title: 'Event title',
                    dataIndex: 'title_en',
                },{
                    title: 'Start date',
                    dataIndex: 'start_date',
                },{
                    title: 'End date',
                    dataIndex: 'end_date',
                },{
                    title: 'Operation',
                    dataIndex: 'operation',
                    key: 'operation',
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
    mounted(){
    },
    methods: {
        handleChange(keys,direction,moveKeys){
            console.log(keys,direction,moveKeys);
        },
        onSave(){
            console.log(this.targetKeys);
        }
    },
}
</script>