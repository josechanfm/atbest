<template>
  <AdminLayout :title="$t('sms_messages')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t('sms_messages') }}
      </h2>
    </template>

    <div class="py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <!-- Toolbar -->
          <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center gap-4">
            <a-input
              v-model:value="filters.search"
              :placeholder="$t('search_by_mobile_or_content')"
              allow-clear
              style="width: 250px;"
              @change="applyFilters"
            />

            <a-select
              v-model:value="filters.status"
              :placeholder="$t('status')"
              allow-clear
              style="width: 150px;"
              @change="applyFilters"
            >
              <a-select-option value="pending">{{ $t('pending') }}</a-select-option>
              <a-select-option value="sent">{{ $t('sent') }}</a-select-option>
              <a-select-option value="failed">{{ $t('failed') }}</a-select-option>
            </a-select>

            <a-select
              v-model:value="filters.organization_id"
              :placeholder="$t('organization')"
              allow-clear
              style="width: 200px;"
              @change="applyFilters"
            >
              <a-select-option v-for="org in organizations" :key="org.id" :value="org.id">
                {{ org.abbr }} - {{ org.name_zh }}
              </a-select-option>
            </a-select>

            <a-button type="primary" @click="applyFilters">{{ $t('filter') }}</a-button>
            <a-button @click="resetFilters">{{ $t('reset') }}</a-button>

            <div class="ml-auto flex items-center gap-2">
              <a-button type="primary" @click="openModal">
                <PlusOutlined /> {{ $t('send_sms') }}
              </a-button>
              <span class="text-sm text-gray-500">{{ $t('per_page') }}</span>
              <a-select v-model:value="filters.per_page" style="width: 70px;" @change="applyFilters">
                <a-select-option :value="10">10</a-select-option>
                <a-select-option :value="15">15</a-select-option>
                <a-select-option :value="30">30</a-select-option>
                <a-select-option :value="50">50</a-select-option>
              </a-select>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <a-table
              :dataSource="smsMessages.data"
              :columns="columns"
              :pagination="{
                total: smsMessages.total,
                current: smsMessages.current_page,
                pageSize: smsMessages.per_page,
                showSizeChanger: false,
                showTotal: (total) => $t('total') + ' ' + total,
              }"
              rowKey="id"
              @change="onTableChange"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'status'">
                  <a-tag :color="statusColor(record.status)">
                    {{ record.status }}
                  </a-tag>
                </template>

                <template v-else-if="column.dataIndex === 'content'">
                  <span class="truncate block max-w-xs">{{ record.content }}</span>
                </template>

                <template v-else-if="column.dataIndex === 'organization'">
                  {{ record.organization?.abbr ?? '—' }}
                </template>

                <template v-else-if="column.dataIndex === 'user'">
                  {{ record.user?.name ?? '—' }}
                </template>

                <!-- ✅ Date columns using formatDate method -->
                <template v-else-if="column.dataIndex === 'sent_at'">
                  {{ formatDate(record.sent_at) }}
                </template>

                <template v-else-if="column.dataIndex === 'created_at'">
                  {{ formatDate(record.created_at) }}
                </template>

                <template v-else-if="column.dataIndex === 'actions'">
                  <a-popconfirm
                    :title="$t('confirm_delete')"
                    :ok-text="$t('yes')"
                    :cancel-text="$t('no')"
                    @confirm="deleteRecord(record)"
                  >
                    <a-button danger size="small">
                      <DeleteOutlined />
                    </a-button>
                  </a-popconfirm>
                </template>

                <template v-else>
                  {{ record[column.dataIndex] }}
                </template>
              </template>
            </a-table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <SmsMessageModal
      ref="modalRef"
      :organizations="organizations"
      @saved="refreshList"
    />
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DeleteOutlined, PlusOutlined } from '@ant-design/icons-vue';
import SmsMessageModal from './SmsMessageModal.vue';

export default {
  name: 'SmsIndex',
  components: {
    AdminLayout,
    DeleteOutlined,
    PlusOutlined,
    SmsMessageModal,
  },
  props: {
    smsMessages: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    organizations: { type: Array, default: () => [] },
  },
  data() {
    return {
      columns: [
        { title: this.$t('id'), dataIndex: 'id', key: 'id', sorter: true },
        { title: this.$t('organization'), dataIndex: 'organization', key: 'organization_id' },
        { title: this.$t('mobile'), dataIndex: 'mobile', key: 'mobile' },
        { title: this.$t('content'), dataIndex: 'content', key: 'content' },
        { title: this.$t('status'), dataIndex: 'status', key: 'status' },
        { title: this.$t('sent_by'), dataIndex: 'user', key: 'user_id' },
        { title: this.$t('sent_at'), dataIndex: 'sent_at', key: 'sent_at', sorter: true },
        { title: this.$t('created_at'), dataIndex: 'created_at', key: 'created_at', sorter: true },
        { title: this.$t('actions'), dataIndex: 'actions', key: 'actions', width: 100 },
      ],
    };
  },
  computed: {
    currentFilters() {
      return {
        search: this.filters.search ?? '',
        status: this.filters.status ?? undefined,
        organization_id: this.filters.organization_id ?? undefined,
        per_page: this.filters.per_page ?? 15,
        sort_field: this.filters.sort_field ?? 'created_at',
        sort_direction: this.filters.sort_direction ?? 'desc',
      };
    },
  },
  methods: {
    // ✅ Date formatter (replaces $filters)
    formatDate(value) {
      if (!value) return '—';
      const date = new Date(value);
      return date.toLocaleString(); // adjust format as needed
    },
    statusColor(status) {
      const map = {
        pending: 'orange',
        sent: 'green',
        failed: 'red',
        delivered: 'blue',
      };
      return map[status] || 'default';
    },
    applyFilters() {
      const params = { ...this.currentFilters };
      Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === null || params[key] === undefined) {
          delete params[key];
        }
      });
      this.$inertia.get(route('admin.sms-messages.index'), params, { preserveState: true });
    },
    resetFilters() {
      this.$inertia.get(route('admin.sms-messages.index'), {});
    },
    onTableChange(pagination, filters, sorter) {
      const params = { ...this.currentFilters };
      params.sort_field = sorter.field ?? 'created_at';
      params.sort_direction = sorter.order === 'ascend' ? 'asc' : 'desc';
      params.page = pagination.current;
      params.per_page = pagination.pageSize;
      this.$inertia.get(route('admin.sms-messages.index'), params, { preserveState: true });
    },
    deleteRecord(record) {
      this.$inertia.delete(route('admin.sms-messages.destroy', record.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.$message.success(this.$t('deleted_successfully'));
        },
      });
    },
    openModal() {
      this.$refs.modalRef.open();
    },
    refreshList() {
      // Re‑apply current filters to refresh the table
      this.applyFilters();
    },
  },
};
</script>