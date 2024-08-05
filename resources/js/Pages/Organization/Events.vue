<template>
  <OrganizationLayout :title="$t('events')" :breadcrumb="breadcrumb">
    <div class="flex justify-end pb-3 gap-3">
      <a-button
          :href="route('manage.events.create')"
          as="link"
          type="primary"
      >
        {{ $t("create_event") }}
      </a-button>
    </div>
    <div class="container mx-auto">
      <div class="flex flex-col md:flex-row justify-between gap-6">
        <a-input v-model:value="search.title_en" :placeholder="$t('please_input_title')"></a-input>
        <a-button type="primary" @click="searchData">{{ $t("search") }}</a-button>
      </div>
    </div>
    <div class="container mx-auto py-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="events.data" :columns="columns">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <inertia-link
                :href="route('manage.events.edit', record.id)"
                class="ant-btn"
                >{{ $t("edit") }}</inertia-link
              >
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>
      </div>
    </div>
  </OrganizationLayout>
</template>

<script>
import OrganizationLayout from "@/Layouts/OrganizationLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { defineComponent, reactive } from "vue";

export default {
  components: {
    OrganizationLayout,
    Pagination,
  },
  props: ["events", "categories"],
  data() {
    return {
      breadcrumb: [{ label: "活動列表", url: null }],
      columns: [
        {
          title: "Event title",
          i18n: "event_title",
          dataIndex: "title",
        },
        {
          title: "Start date",
          i18n: "start_date",
          dataIndex: "start_date",
        },
        {
          title: "End date",
          i18n: "end_date",
          dataIndex: "end_date",
        },
        {
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      search: {},
      pagination: {
        total: this.events.total,
        current: this.events.current_page,
        pageSize: this.events.per_page,
      },
      rules: {
        name_zh: { required: true },
        mobile: { required: true },
        state: { required: true },
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
      labelCol: {
        style: {
          width: "150px",
        },
      },
    };
  },
  created() {},
  mounted() {
    this.pagination = {
      currentPage: this.route().params.currentPage ?? 1,
      pageSize: this.route().params.pageSize ?? 10,
    };
    this.search = this.route().params.search ?? {};
  },
  methods: {
    searchData() {
      this.$inertia.get(
        route("manage.events.index"),
        { search: this.search, pagination: this.pagination },
        {
          onSuccess: (page) => {
            console.log(page);
          },
          preserveState: true,
        }
      );
    },
  },
};
</script>
