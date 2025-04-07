<template>
    <MemberLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('forms')}}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5 h-64">
                    <a-table :dataSource="entries" :columns="columns">
                        <template #headerCell="{ column }">
                            {{ column.i18n ? $t(column.i18n) : column.title }}
                        </template>
                        <template #bodyCell="{column, text, record, index}">
                            <template v-if="column.dataIndex=='operation'">
                                <a-button @click="viewRecord(record)">{{$t('view')}}</a-button>
                                <!-- <a-button @click="deleteRecord(record.id)">{{$t('delete')}}</a-button> -->
                            </template>
                            <template v-else-if="column.dataIndex=='form_id'">
                                {{record.form.title}}
                            </template>
                            <template v-else-if="column.dataIndex=='record'">
                                <ul class="list-none p-0">
                                    <li v-for="field in record.form.fields.filter(f=>f.in_column==true)">
                                        {{ field.field_name }}: 
                                        {{ getFieldData(record.records,field.id)}}
                                    </li>
                                </ul>
                            </template>
                            <template v-else>
                                {{record[column.dataIndex]}}
                            </template>
                        </template>
                    </a-table>
                </div>
            </div>
        </div>
    
        <a-modal v-model:open="modal.isOpen" title="我的表格" width="60%" cancelText="關閉" :ok-button-props="{ hidden: true }">
            <div v-for="field in modal.data.form.fields">
                {{ field.field_name }}: {{ getFieldData(modal.data.records,field.id)}}
            </div>
        </a-modal>
   
    </MemberLayout>
</template>


<script>
import MemberLayout from '@/Layouts/MemberLayout.vue';
// import { quillEditor } from 'vue3-quill';

export default {
    components: {
        MemberLayout,
        // quillEditor
    },
    props: ['entries'],
    data() {
        return {
            modal: {
                isOpen: false,
                data: {},
                title: "Modal",
                mode: ""
            },
            columns:[
                {
                    title: 'Form',
                    i18n:'form',
                    dataIndex: 'form_id',
                },{
                    title: 'Date',
                    i18n:'date',
                    dataIndex: 'created_at',
                },{
                    title: 'Records',
                    i18n:'data_fields',
                    dataIndex: 'record',
                },{
                    title: 'Operation',
                    i18n:'operation',
                    dataIndex: 'operation',
                    key: 'operation',
                },
            ],
            rules: {
                field: { required: true },
                label: { required: true },
            },
            validateMessages: {
                required: '${label} is required!',
                types: {
                    email: '${label} is not a valid email!',
                    number: '${label} is not a valid number!',
                },
                number: {
                    range: '${label} must be between ${min} and ${max}',
                },
            },
            virticalStyle: {
                display: "flex",
                height: "30px",
                lineHeight: "30px",
                marginLeft: "8px",
            },

        }
    },
    created() {
    },
    methods: {
        viewRecord(record) {
            this.modal.data = { ...record };
            this.modal.mode = "VIEW";
            this.modal.isOpen = true;
        },
        updateRecord() {
            this.$refs.modalRef.validateFields().then(() => {
                this.modal.data._method = 'PATCH';
                this.$inertia.post(route('member.porfolios.update', this.modal.data.id), this.modal.data, {
                    onSuccess: (page) => {
                        this.modal.isOpen = false;
                    },
                    onError: (error) => {
                        console.log(error);
                    }
                });
            }).catch(err => {
                console.log("error", err);
            });
        },

        deleteRecord(record) {
            if (!confirm('Are you sure want to remove?')) return;
            this.$inertia.delete(route('member.porfolios.destroy', { form: record.id }), {
                onSuccess: (page) => {
                    console.log(page);
                },
                onError: (error) => {
                    alert(error.message);
                }

            });
        },
        getFieldData(records,fieldId){
            const record=records.find(r=>r.form_field_id==fieldId)
            return record.field_value;
            
        }

    },
}
</script>