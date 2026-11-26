<template>
  <AdminLayout :title="$t('enquiry.title')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t('enquiry.title') }}
      </h2>
    </template>

    <div class="py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <!-- Toolbar -->
          <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center gap-4">
            <a-input
              v-model:value="filters.search"
              :placeholder="$t('enquiry.search_placeholder')"
              allow-clear
              style="width: 280px;"
              @change="applyFilters"
            />

            <a-select
              v-model:value="filters.status"
              :placeholder="$t('enquiry.status')"
              allow-clear
              style="width: 150px;"
              @change="applyFilters"
            >
              <a-select-option value="new">{{ $t('enquiry.new') }}</a-select-option>
              <a-select-option value="read">{{ $t('enquiry.read') }}</a-select-option>
            </a-select>

            <a-button type="primary" @click="applyFilters">{{ $t('enquiry.filter') }}</a-button>
            <a-button @click="resetFilters">{{ $t('enquiry.reset') }}</a-button>

            <div class="ml-auto flex items-center gap-2">
              <span class="text-sm text-gray-500">{{ $t('enquiry.per_page') }}</span>
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
              :dataSource="enquiries.data"
              :columns="columns"
              :pagination="{
                total: enquiries.total,
                current: enquiries.current_page,
                pageSize: enquiries.per_page,
                showSizeChanger: false,
                showTotal: (total) => $t('enquiry.total') + ' ' + total,
              }"
              rowKey="id"
              @change="onTableChange"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'status'">
                  <a-tag :color="statusColor(record.status)">
                    {{ record.status === 'new' ? $t('enquiry.new') : $t('enquiry.read') }}
                  </a-tag>
                </template>

                <template v-else-if="column.dataIndex === 'message'">
                  <span class="truncate block max-w-xs">{{ record.message }}</span>
                </template>

                <template v-else-if="column.dataIndex === 'created_at'">
                  {{ formatDate(record.created_at) }}
                </template>

                <template v-else-if="column.dataIndex === 'actions'">
                  <div class="flex items-center gap-1">
                    <a-popover placement="top" trigger="click">
                      <template #content>
                        <div class="max-w-xs whitespace-pre-wrap">{{ record.message }}</div>
                      </template>
                      <a-button size="small" type="link">
                        <EyeOutlined />
                      </a-button>
                    </a-popover>

                    <a-popconfirm
                      :title="$t('enquiry.confirm_delete')"
                      :ok-text="$t('enquiry.yes')"
                      :cancel-text="$t('enquiry.no')"
                      @confirm="deleteRecord(record)"
                    >
                      <a-button danger size="small">
                        <DeleteOutlined />
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
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { DeleteOutlined, EyeOutlined } from '@ant-design/icons-vue';

export default {
  name: 'EnquiryIndex',
  components: {
    AdminLayout,
    DeleteOutlined,
    EyeOutlined,
  },
  props: {
    enquiries: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      columns: [
        { title: this.$t('enquiry.id'), dataIndex: 'id', key: 'id', sorter: true, width: 70 },
        { title: this.$t('enquiry.name'), dataIndex: 'name', key: 'name' },
        { title: this.$t('enquiry.email'), dataIndex: 'email', key: 'email' },
        { title: this.$t('enquiry.phone'), dataIndex: 'phone', key: 'phone' },
        { title: this.$t('enquiry.subject'), dataIndex: 'subject', key: 'subject' },
        { title: this.$t('enquiry.message'), dataIndex: 'message', key: 'message' },
        { title: this.$t('enquiry.status'), dataIndex: 'status', key: 'status', width: 90 },
        { title: this.$t('enquiry.created_at'), dataIndex: 'created_at', key: 'created_at', sorter: true },
        { title: this.$t('enquiry.actions'), dataIndex: 'actions', key: 'actions', width: 110 },
      ],
    };
  },
  computed: {
    currentFilters() {
      return {
        search: this.filters.search ?? '',
        status: this.filters.status ?? undefined,
        per_page: this.filters.per_page ?? 15,
        sort_field: this.filters.sort_field ?? 'created_at',
        sort_direction: this.filters.sort_direction ?? 'desc',
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
        new: 'blue',
        read: 'green',
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
      this.$inertia.get(route('admin.enquiries.index'), params, { preserveState: true });
    },
    resetFilters() {
      this.$inertia.get(route('admin.enquiries.index'), {});
    },
    onTableChange(pagination, filters, sorter) {
      const params = { ...this.currentFilters };
      params.sort_field = sorter.field ?? 'created_at';
      params.sort_direction = sorter.order === 'ascend' ? 'asc' : 'desc';
      params.page = pagination.current;
      params.per_page = pagination.pageSize;
      this.$inertia.get(route('admin.enquiries.index'), params, { preserveState: true });
    },
    deleteRecord(record) {
      this.$inertia.delete(route('admin.enquiries.destroy', record.id), {
        preserveScroll: true,
        onSuccess: () => {
          this.$message.success(this.$t('enquiry.deleted_successfully'));
        },
      });
    },
  },
};
</script>
