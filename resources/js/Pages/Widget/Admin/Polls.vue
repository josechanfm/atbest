<template>
    <WidgetLayout title="Manage Polls">
        <div class="font-sans text-gray-900 antialiased p-5">
            <div class="container mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Polling Management</h1>
                    <a-button type="primary" size="large" @click="openCreateModal">
                        Create New Poll
                    </a-button>
                </div>

                <!-- POLLS TABLE -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <a-table :data-source="polls" :columns="columns" row-key="id">
                        <!-- Poll Type Column -->
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'type'">
                                <a-tag :color="record.type === 'checkbox' ? 'purple' : 'blue'">
                                    {{ record.type === 'checkbox' ? 'Multiple Choice' : 'Single Choice' }}
                                </a-tag>
                            </template>

                            <!-- Question Preview Column -->
                            <template v-if="column.key === 'question'">
                                <div class="line-clamp-2" v-html="record.question" />
                            </template>

                            <!-- Status Column -->
                            <template v-if="column.key === 'status'">
                                <a-badge :status="record.status ? 'success' : 'default'" 
                                         :text="record.status ? 'Active' : 'Inactive'" />
                            </template>

                            <!-- Actions Column -->
                            <template v-if="column.key === 'action'">
                                <div class="space-x-2">
                                    <a-button type="link" @click="viewPoll(record)">View Results</a-button>
                                    <a-button type="link" @click="openEditModal(record)">Edit</a-button>
                                    <a-popconfirm title="Are you sure you want to delete this poll?" 
                                                  ok-text="Yes" cancel-text="No" 
                                                  @confirm="deletePoll(record.id)">
                                        <a-button type="link" danger>Delete</a-button>
                                    </a-popconfirm>
                                </div>
                            </template>
                        </template>
                    </a-table>
                </div>
            </div>
        </div>

        <!-- EXTRACTED POLL FORM MODAL -->
        <PollForm 
            v-model:open="isModalOpen" 
            :poll="selectedPoll" 
        />
    </WidgetLayout>
</template>

<script>
import WidgetLayout from '@/Layouts/WidgetLayout.vue';
import PollForm from './PollForm.vue';
import { message } from 'ant-design-vue';

export default {
    components: {
        WidgetLayout,
        PollForm
    },
    props: {
        polls: Array
    },
    data() {
        return {
            isModalOpen: false,
            selectedPoll: null,
            columns: [
                { title: 'Question', dataIndex: 'question', key: 'question' },
                { title: 'Type', dataIndex: 'type', key: 'type', width: 150 },
                { title: 'Responses', dataIndex: 'responses_count', key: 'responses_count', width: 120 },
                { title: 'Status', dataIndex: 'status', key: 'status', width: 120 },
                { title: 'Actions', key: 'action', width: 250 }
            ]
        };
    },
    methods: {
        openCreateModal() {
            this.selectedPoll = null;
            this.isModalOpen = true;
        },
        openEditModal(record) {
            this.selectedPoll = record;
            this.isModalOpen = true;
        },
        viewPoll(record) {
            this.$inertia.get(route('widget.admin.polls.show', record.id));
        },
        deletePoll(id) {
            this.$inertia.delete(route('widget.admin.polls.destroy', id), {
                onSuccess: () => message.success('Poll deleted successfully')
            });
        }
    }
}
</script>