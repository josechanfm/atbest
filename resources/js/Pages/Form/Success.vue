<template>
  <WebsiteLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">表格例表</h2>
    </template>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
      <div
        class="mt-8 p-4 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg"
      >
        <div class="ant-table ant-table-bordered">
          <div class="ant-table-container">
            <div class="ant-table-content">
              <table id="applicationSuccess" style="table-layout: auto">
                <tbody class="ant-table-tbody">
                  <template v-for="record in entry_records" :key="record.id">
                    <tr v-if="record.form_field.type == 'photo'">
                      <td>{{ record.form_field.field_label }}</td>
                      <td><img :src="record.field_value" class="w-[120px]" /></td>
                    </tr>
                    <tr v-else-if="record.form_field.type == 'radio'">
                      <td>{{ record.form_field.field_label }}</td>
                      <td>
                        {{
                          record.form_field.options.find(
                            (x) => x.value == record.field_value
                          ).label
                        }}
                      </td>
                    </tr>
                    <tr v-else-if="record.form_field.type == 'checkbox'">
                      <td>{{ record.form_field.field_label }}</td>
                      <td>
                        {{
                          JSON.parse(record.field_value)
                            .map((x) => {
                              return record.form_field.options.find(
                                (y) => y.value == x
                              ).label;
                            })
                            .join(",")
                        }}
                      </td>
                    </tr>
                    <tr v-else>
                      <td>{{ record.form_field.field_label }}</td>
                      <td>{{ record.field_value }}</td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="flex justify-between py-10">
          <div>
            <a :href="route('host')">返回主頁</a>
          </div>
          <!-- <div>
            <inertia-link :href="route('form.receipt',entry)">打印表格</inertia-link>
          </div> -->
          <div>
            <a :href="route('forms')">活動列表</a>
          </div>
        </div>
      </div>
    </div>
  </WebsiteLayout>
</template>

<script>
import WebsiteLayout from "@/Layouts/WebsiteLayout.vue";

export default {
  components: {
    WebsiteLayout,
  },
  props: ["form", "entry_records", "entry"],
  data() {
    return {};
  },
  created() {},
  methods: {},
};
</script>
