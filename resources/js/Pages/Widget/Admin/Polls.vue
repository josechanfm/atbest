<template>
    <WidgetLayout title="Widgets">
    <div class="py-5 font-sans text-gray-900 antialiased">
        <div class="container mx-auto">
            <div class="flex justify-between gap-6">
                <a-button type="primary" @click="createRecord()">Create</a-button>
            </div>
        </div>
        <div class="container mx-auto pt-5">
            <div class="bg-white relative shadow rounded-lg overflow-x-auto">
                <a-table :dataSource="polls" :columns="columns">
                    <template #headerCell="{ column }">
                        {{ column.i18n ? $t(column.i18n) : column.title }}
                    </template>
                    <template #bodyCell="{ column, text, record, index }">
                        <template v-if="column.dataIndex == 'operation'">
                            <a-button @click="editRecord(record)">Edit</a-button>
                            <a-button as="link" :href="route('widget.admin.polls.show',record)" target="PollDisplay">Show</a-button>
                            <a-popconfirm
                                title="Are you sure to delete all responses records?"
                                ok-text="Yes"
                                cancel="No" 
                                @confirm="clearResponses(record)"
                            >
                                <a-button>Clear Responses</a-button>
                            </a-popconfirm>
                            
                        </template>
                        <template v-if="column.dataIndex == 'question'">
                            <div> {{plaiText(text)}}</div>
                        </template>
                        <template v-else>
                            {{ record[column.dataIndex] }}
                        </template>
                    </template>
                </a-table>
            </div>
        </div>
    </div>
</WidgetLayout>
        <!-- Modal Start-->
        <a-modal v-model:open="modal.isOpen" :title="modal.title" width="60%">
            <a-form ref="modalRef" :model="modal.data" name="Teacher" :label-col="{ span: 4}"
                :wrapper-col="{ span: 18 }" autocomplete="off" :rules="rules" :validate-messages="validateMessages">
                <a-form-item label="Question" name="question">
                    <quill-editor v-model:value="modal.data.question" style="min-height:200px;"/>
                </a-form-item>
                <a-form-item label="Option A" name="option_a">
                    <a-textarea v-model:value="modal.data.option_a" />
                </a-form-item>
                <a-form-item label="Option B" name="option_b">
                    <a-textarea v-model:value="modal.data.option_b" />
                </a-form-item>
                <a-form-item label="Option C" name="option_c">
                    <a-textarea v-model:value="modal.data.option_c" />
                </a-form-item>
                <a-form-item label="Option D" name="option_d">
                    <a-textarea v-model:value="modal.data.option_d" />
                </a-form-item>
                <a-form-item label="Answer" name="answer">
                    <a-select v-model:value="modal.data.answer" :options="answerOptions"/>
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
</template>


<script>
import WidgetLayout from '@/Layouts/WidgetLayout.vue';
import { quillEditor } from 'vue3-quill';
import { message } from 'ant-design-vue';

export default {
    components: {
        WidgetLayout,
        quillEditor,
        message
    },
    props: ['polls', 'configs'],
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
                    title: "Question",
                    dataIndex: "question",
                }, {
                    title: "Status",
                    dataIndex: "status",
                }, {
                    title: "Count",
                    dataIndex: "responses_count",
                }, {
                    title: "Operation",
                    dataIndex: "operation",
                },
            ],
            rules: {
                question: { required: true },
                option_a: { required: true },
                option_b: { required: true },
                answer: { required: true },
            },
            validateMessages: {
                required: "${label} is required!",
                types: {
                    email: "${label} is not a valid email!",
                    number: "${label} is not a valid number!",
                },
                number: {
                range: "${label} must be between ${min} and ${max}",
                },
            },
            answerOptions:[
                {value:'A'},
                {value:'B'},
                {value:'C'},
                {value:'D'},
            ]

        }
    },
    created() {
    },
    methods: {
        createRecord() {
            this.modal.data = {};
            this.modal.mode = "CREATE";
            this.modal.title = "Create";
            this.modal.isOpen = true;
        },
        editRecord(record) {
            this.modal.data = { ...record };
            this.modal.mode = "EDIT";
            this.modal.title = "Edit";
            this.modal.isOpen = true;
        },
        storeRecord(){
            console.log(this.modal.data)
            this.$refs.modalRef
                .validateFields()
                .then(() => {
                this.$inertia.post(route("widget.admin.polls.store"), this.modal.data, {
                    onSuccess: (page) => {
                        this.modal.data = {};
                        this.modal.isOpen = false;
                    },
                    onError: (err) => {
                        console.log(err);
                    },
                });
            })
            .catch((err) => {
                console.log(err);
            });

        },
        updateRecord(){
            console.log(this.modal.data);
            this.$refs.modalRef
                .validateFields()
                .then(() => {
                this.$inertia.put(
                    route("widget.admin.polls.update", this.modal.data.id),
                    this.modal.data,
                    {
                    onSuccess: (page) => {
                        this.modal.data = {};
                        this.modal.isOpen = false;
                        console.log(page);
                    },
                    onError: (error) => {
                        console.log(error);
                    },
                    }
                );
            })
            .catch((err) => {
                console.log("error", err);
            });

        },
        plaiText(html){
            const reg = /<[^<>]+>/g
            return html.replace(reg, '').substr(0,200)
        },
        clearResponses(poll){
            axios.post(route("widget.poll.responseClear", poll))
            .then((resp) => {
                poll.responses_count=0
                message.success(resp.data.message)
            });
        }

    }
}

</script>