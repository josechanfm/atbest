<template>
  <AdminLayout :title="$t('forums')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("forums") }}
      </h2>
    </template>

    <div class="flex justify-end pb-3 gap-3">
      <a-button :href="route('admin.forums.create')" as="link" type="primary">
        {{ $t("create_forum") }}
      </a-button>
    </div>

    <div class="container mx-auto">
      <div class="flex flex-auto gap-2">
        <a-select
          class="w-64"
          :placeholder="$t('please_select_organization')"
          v-model:value="search.organization_id"
          allowClear
          :options="organizations"
          :fieldNames="{ value: 'id', label: 'name_zh' }"
        ></a-select>
        <a-input
          type="input"
          v-model:value="search.title"
          :placeholder="$t('please_input_title')"
          class="w-64"
        ></a-input>
        <a-button type="primary" @click="searchData">{{ $t("search") }}</a-button>
        <a-button type="primary" as="link" :href="route('admin.forums.index')">{{ $t("clear") }}</a-button>
      </div>
    </div>

    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table
          :dataSource="dataModel"
          :columns="columns"
          :pagination="pagination"
          @change="onPaginationChange"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'organization_id'">
              {{ getOrganizationName(record.organization_id) }}
            </template>
            <template v-else-if="column.dataIndex === 'operation'">
              <a-button :href="route('admin.forums.edit', record.id)">{{ $t("edit") }}</a-button>
              <a-button @click="deleteRecord(record)">{{ $t("delete") }}</a-button>
            </template>
          </template>
        </a-table>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";

export default {
  components: {
    AdminLayout,
  },
  props: ["organizations", "forums"],
  data() {
    return {
      dataModel: this.forums.data,
      search: {},
      pagination: {
        total: this.forums.total,
        current: this.forums.current_page,
        pageSize: this.forums.per_page,
      },
      columns: [
        { title: this.$t("organization"), dataIndex: "organization_id" },
        { title: this.$t("title"), dataIndex: "title" },
        { title: this.$t("description"), dataIndex: "description", ellipsis: true },
        { title: this.$t("created_at"), dataIndex: "created_at" },
        { title: this.$t("operation"), dataIndex: "operation", key: "operation" },
      ],
    };
  },
  mounted() {
    this.search = this.route().params.search ?? {};
  },
  methods: {
    getOrganizationName(id) {
      const org = this.organizations.find((o) => o.id === id);
      return org ? org.name_zh : id;
    },
    deleteRecord(record) {
      if (!confirm(this.$t("confirm_delete_record"))) return;
      this.$inertia.delete(route("admin.forums.destroy", record.id), {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          console.log(error);
        },
      });
    },
    searchData() {
      this.$inertia.get(
        route("admin.forums.index"),
        { search: this.search, pagination: this.pagination },
        {
          onSuccess: (page) => {
            console.log(page);
          },
        }
      );
    },
    onPaginationChange(page) {
      this.$inertia.get(
        route("admin.forums.index"),
        {
          page: page.current,
          per_page: page.pageSize,
          search: this.search,
        },
        {
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (error) => {
            console.log(error);
          },
        }
      );
    },
  },
};
</script>
