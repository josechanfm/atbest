<template>
    <OrganizerLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Certificates
            </h2>
        </template>
        <div class="flex-auto pb-3 text-right">
            <a-button type="primary" class="!rounded" @click="createRecord()">Create Certificate</a-button>
        </div>

        <a-table :dataSource="configs" :columns="columns">
            <template #headerCell="{ column }">
                {{ column.i18n ? $t(column.i18n) : column.title }}
            </template>
            <template #bodyCell="{ column, text, record, index }">
                <template v-if="column.dataIndex == 'operation'">
                    <a-button @click="editRecord(record)">{{ $t('edit') }}</a-button>
                </template>
                <template v-else-if="column.dataIndex == 'state'">
                    {{ teacherStateLabels[text] }}
                </template>
                <template v-else>
                    {{ record[column.dataIndex] }}
                </template>
            </template>
        </a-table>

        <!-- Modal Start-->
        <a-modal v-model:open="modal.isOpen" :title="modal.title" width="60%">
            <a-form ref="modalRef" :model="modal.data" name="Teacher" :label-col="{ span: 8 }"
                :wrapper-col="{ span: 16 }" autocomplete="off" :rules="rules" :validate-messages="validateMessages">
                <a-form-item label="姓名(中文)" name="name_zh">
                    <a-input type="input" v-model:value="modal.data.name_zh" />
                </a-form-item>
                <a-form-item label="姓名(外文)" name="name_zh">
                    <a-input type="input" v-model:value="modal.data.name_fn" />
                </a-form-item>
                <a-form-item label="別名" name="nickname">
                    <a-input type="input" v-model:value="modal.data.nickname" />
                </a-form-item>
                <a-form-item label="手機" name="mobile">
                    <a-input type="input" v-model:value="modal.data.mobile" />
                </a-form-item>
            </a-form>
            <template #footer>
                <a-button v-if="modal.mode == 'EDIT'" key="Update" type="primary"
                    @click="updateRecord()">{{ $t('update') }}</a-button>
                <a-button v-if="modal.mode == 'CREATE'" key="Store" type="primary"
                    @click="storeRecord()">{{ $t('add') }}</a-button>
            </template>
        </a-modal>
        <!-- Modal End-->
    </OrganizerLayout>

</template>

<script>
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue';
import { defineComponent, reactive } from 'vue';

export default {
    components: {
        OrganizerLayout,
    },
    props: ['configs'],
    data() {
        return {
            modal: {
                isOpen: false,
                data: {},
                title: "Modal",
                mode: ""
            },
            columns: [
                {
                    title: 'Key',
                    dataIndex: 'key',
                }, {
                    title: 'Value',
                    dataIndex: 'value',
                }, {
                    title: '操作',
                    dataIndex: 'operation',
                    key: 'operation',
                    i18n: 'operation'
                },
            ],
            rules: {
                name_zh: { required: true },
                mobile: { required: true },
                state: { required: true },
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
            labelCol: {
                style: {
                    width: '150px',
                },
            },
        }
    },
    created() {
    },
    methods: {
        createRecord() {
            this.modal.data = {};
            this.modal.mode = "CREATE";
            this.modal.title = "Create config item";
            this.modal.isOpen = true;
        },
        editRecord(record) {
            this.modal.data = { ...record };
            this.modal.mode = "EDIT";
            this.modal.title = "Edit config item";
            this.modal.isOpen = true;
        },
        storeRecord() {
            this.$refs.modalRef.validateFields().then(() => {
                this.$inertia.post('/admin/teachers/', this.modal.data, {
                    onSuccess: (page) => {
                        this.modal.data = {};
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
                this.$inertia.patch('/admin/teachers/' + this.modal.data.id, this.modal.data, {
                    onSuccess: (page) => {
                        this.modal.data = {};
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
    },
}
</script>