<template>
    <MemberLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Personal Profile
            </h2>
        </template>
        <div class="py-12">
                            
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5 h-64">
                    <p>Comming Soon...</p>
                    <p>Records of your personal experience and documents.</p>
                </div>
            </div>
            
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 invisible">
                <button @click="createRecord()"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">Create new record</button>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <a-table :dataSource="member.portfolios" :columns="columns">
                        <template #bodyCell="{column, text, record, index}">
                            <template v-if="column.dataIndex=='operation'">
                                <a-button @click="editRecord(record)">{{$t('edit')}}</a-button>
                                <a-button @click="deleteRecord(record.id)">{{$t('delete')}}</a-button>
                            </template>
                            <template v-else>
                                {{record[column.dataIndex]}}
                            </template>
                        </template>
                    </a-table>

                </div>
            </div>
        </div>

    
        <a-modal v-model:open="modal.isOpen" :title="modal.mode == 'CREATE' ? '新增' : '修改'" width="60%">
            <a-form ref="modalRef" :model="modal.data" name="From"  :label-col="{ span: 6 }"
                :wrapper-col="{ span: 18 }" autocomplete="off" :rules="rules" :validate-messages="validateMessages">
                <a-form-item label="Name" name="name">
                    <a-select v-model:value="modal.data.approbate_id" :options="approbates"
                        :fieldNames="{ value: 'id', label: 'name' }" />
                </a-form-item>
                <a-form-item label="Name" name="name">
                    <a-input type="input" v-model:value="modal.data.name" />
                </a-form-item>
                <a-form-item label="Title" name="title">
                    <a-input type="input" v-model:value="modal.data.title" />
                </a-form-item>
                <a-form-item label="Description" name="description">
                    <!-- <quill-editor v-model:value="modal.data.description" style="min-height:200px;" /> -->
                </a-form-item>
            </a-form>
            <template #footer>
                <a-button v-if="modal.mode == 'EDIT'" key="Update" type="primary" @click="updateRecord()">更新</a-button>
                <a-button v-if="modal.mode == 'CREATE'" key="Store" type="primary" @click="storeRecord()">新增</a-button>
            </template>
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
    props: ['member'],
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
                    title: 'Abbreviation',
                    dataIndex: 'abbreviation',
                },{
                    title: 'Title',
                    dataIndex: 'title',
                },{
                    title: 'validated at',
                    dataIndex: 'valid_at',
                },{
                    title: 'Expired at',
                    dataIndex: 'expired_at',
                },{
                    title: 'Accredited',
                    dataIndex: 'accredited',
                },{
                    title: '操作',
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
        createRecord(record) {
            this.modal.data = {};
            // this.modal.data.organization_id=this.organization.id;
            // this.modal.data.require_login=false;
            // this.modal.data.for_member=false;
            // this.modal.data.published=false;
            // this.modal.data.media=[];
            this.modal.mode = "CREATE";
            this.modal.isOpen = true;
        },
        editRecord(record) {
            console.log(record);
            this.modal.data = { ...record };
            this.modal.mode = "EDIT";
            this.modal.isOpen = true;
        },
        storeRecord() {
            console.log(this.modal.data);
            this.$refs.modalRef.validateFields().then(() => {
                this.$inertia.post(route('member.portfolios.store'), this.modal.data, {
                    onSuccess: (page) => {
                        this.modal.isOpen = false;
                    },
                    onError: (err) => {
                        console.log(err);
                    }
                });
            }).catch(err => {
                console.log(err);
            });
        },
        updateRecord() {
            console.log(this.modal.data);
            this.$refs.modalRef.validateFields().then(() => {
                this.modal.data._method = 'PATCH';
                this.$inertia.post(route('member.porfolios.update', this.modal.data.id), this.modal.data, {
                    onSuccess: (page) => {
                        this.modal.isOpen = false;
                        console.log(page);
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

    },
}
</script>