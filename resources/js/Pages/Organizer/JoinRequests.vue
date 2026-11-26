<template>
  <OrganizerLayout :title="$t('join_request.title')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t('join_request.title') }}
      </h2>
    </template>

    <div class="py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a-alert
          v-if="page.props.status"
          :message="$t(page.props.status)"
          type="success"
          show-icon
          class="mb-4"
        />
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <!-- Toolbar -->
          <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center gap-4">
            <a-select
              v-model:value="filters.status"
              :placeholder="$t('join_request.status')"
              allow-clear
              style="width: 160px;"
              @change="applyFilters"
            >
              <a-select-option value="pending">{{ $t('join_request.pending') }}</a-select-option>
              <a-select-option value="approved">{{ $t('join_request.approved') }}</a-select-option>
              <a-select-option value="rejected">{{ $t('join_request.rejected') }}</a-select-option>
            </a-select>

            <a-button type="primary" @click="applyFilters">{{ $t('join_request.filter') }}</a-button>
            <a-button @click="resetFilters">{{ $t('join_request.reset') }}</a-button>

            <div class="ml-auto flex items-center gap-2">
              <span class="text-sm text-gray-500">{{ $t('join_request.per_page') }}</span>
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
              :dataSource="requests.data"
              :columns="columns"
              :pagination="{
                total: requests.total,
                current: requests.current_page,
                pageSize: requests.per_page,
                showSizeChanger: false,
                showTotal: (total) => $t('join_request.total') + ' ' + total,
              }"
              rowKey="id"
              @change="onTableChange"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'status'">
                  <a-tag :color="statusColor(record.status)">
                    {{ statusLabel(record.status) }}
                  </a-tag>
                </template>

                <template v-else-if="column.dataIndex === 'reason'">
                  <a-popover placement="top" trigger="click">
                    <template #content>
                      <div class="max-w-xs whitespace-pre-wrap">{{ record.reason || '—' }}</div>
                    </template>
                    <span class="truncate block max-w-xs cursor-pointer">{{ record.reason || '—' }}</span>
                  </a-popover>
                </template>

                <template v-else-if="column.dataIndex === 'created_at'">
                  {{ formatDate(record.created_at) }}
                </template>

                <template v-else-if="column.dataIndex === 'actions'">
                  <div class="flex items-center gap-1">
                    <a-popconfirm
                      v-if="record.status === 'pending'"
                      :title="$t('join_request.confirm_approve')"
                      :ok-text="$t('join_request.yes')"
                      :cancel-text="$t('join_request.no')"
                      @confirm="approveRecord(record)"
                    >
                      <a-button size="small" type="primary">
                        <CheckOutlined />
                      </a-button>
                    </a-popconfirm>

                    <a-popconfirm
                      v-if="record.status === 'pending'"
                      :title="$t('join_request.confirm_reject')"
                      :ok-text="$t('join_request.yes')"
                      :cancel-text="$t('join_request.no')"
                      @confirm="rejectRecord(record)"
                    >
                      <a-button size="small" danger>
                        <CloseOutlined />
                      </a-button>
                    </a-popconfirm>
                  </div>
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
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from '@/Layouts/OrganizerLayout.vue';
import { CheckOutlined, CloseOutlined } from '@ant-design/icons-vue';
import { usePage } from '@inertiajs/vue3';

export default {
  name: 'OrganizerJoinRequests',
  components: {
    OrganizerLayout,
    CheckOutlined,
    CloseOutlined,
  },
  props: {
    requests: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
  },
  setup() {
    return { page: usePage() };
  },
  data() {
    return {
      columns: [
        { title: this.$t('join_request.id'), dataIndex: 'id', key: 'id', width: 70 },
        { title: this.$t('join_request.name'), dataIndex: 'name', key: 'name' },
        { title: this.$t('join_request.email'), dataIndex: 'email', key: 'email' },
        { title: this.$t('join_request.phone'), dataIndex: 'phone', key: 'phone' },
        { title: this.$t('join_request.reason'), dataIndex: 'reason', key: 'reason' },
        { title: this.$t('join_request.status'), dataIndex: 'status', key: 'status', width: 100 },
        { title: this.$t('join_request.created_at'), dataIndex: 'created_at', key: 'created_at', width: 170 },
        { title: this.$t('join_request.actions'), dataIndex: 'actions', key: 'actions', width: 110 },
      ],
    };
  },
  computed: {
    currentFilters() {
      return {
        status: this.filters.status ?? undefined,
        per_page: this.filters.per_page ?? 15,
      };
    },
  },
  methods: {
    formatDate(value) {
      if (!value) return '—';
      return new Date(value).toLocaleString();
    },
    statusColor(status) {
      const map = {
        pending: 'blue',
        approved: 'green',
        rejected: 'red',
      };
      return map[status] || 'default';
    },
    statusLabel(status) {
      const map = {
        pending: this.$t('join_request.pending'),
        approved: this.$t('join_request.approved'),
        rejected: this.$t('join_request.rejected'),
      };
      return map[status] || status;
    },
    applyFilters() {
      const params = { ...this.currentFilters };
      Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === null || params[key] === undefined) {
          delete params[key];
        }
      });
      this.$inertia.get(route('organizer.joinRequests.index'), params, { preserveState: true });
    },
    resetFilters() {
      this.$inertia.get(route('organizer.joinRequests.index'), {});
    },
    onTableChange(pagination) {
      const params = { ...this.currentFilters };
      params.page = pagination.current;
      params.per_page = pagination.pageSize;
      this.$inertia.get(route('organizer.joinRequests.index'), params, { preserveState: true });
    },
    approveRecord(record) {
      this.$inertia.post(route('organizer.joinRequests.approve', record.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
          this.$message.success(this.$t('join_request.approved'));
        },
      });
    },
    rejectRecord(record) {
      this.$inertia.post(route('organizer.joinRequests.reject', record.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
          this.$message.success(this.$t('join_request.rejected'));
        },
      });
    },
  },
};
</script>
