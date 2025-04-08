<template>
  <AdminLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("feature") }}
      </h2>
    </template>
    <div class="flex justify-end pb-3 gap-3">
      <a-button :href="route('admin.features.create')" as="link" type="primary">
        {{ $t("create_feature") }}
      </a-button>
    </div>

    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto">
        <a-table :dataSource="features" :columns="columns">
          <template #headerCell="{ column }">
            {{ column.i18n ? $t(column.i18n) : column.title }}
          </template>
          <template #bodyCell="{ column, text, record, index }">
            <template v-if="column.dataIndex == 'operation'">
              <inertia-link :href="route('admin.features.edit',record.id)" class="ant-btn">{{ $t('edit') }}</inertia-link>
            </template>
            <template v-else-if="column.dataIndex == 'organization_id'">
              {{ record[column.dataIndex] }}
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </a-table>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { defineComponent, reactive } from "vue";

export default {
  components: {
    AdminLayout,
  },
  props: ["organizations","features"],
  data() {
    return {
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      columns: [
        {
          title: "Organization",
          i18n: "organization",
          dataIndex: "organization_abbr",
        },{
          title: "Title",
          i18n: "title",
          dataIndex: "title_zh",
        },{
          title: "Operation",
          i18n: "operation",
          dataIndex: "operation",
          key: "operation",
        },
      ],
      rules: {
        title_zh: { required: true },
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
  created() {
    this.organizations.unshift({ id: 0, full_name: "General Config Item" });
  },
  methods: {
  },
};
</script>
