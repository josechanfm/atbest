<template>
  <AdminLayout :title="$t('forums')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ forum.id ? $t("edit") : $t("create_forum") }}
      </h2>
    </template>

    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto p-5">
        <a-form
          :model="forum"
          layout="vertical"
          autocomplete="off"
          :rules="rules"
          :validate-messages="validateMessages"
          @finish="onFinish"
        >
          <a-form-item :label="$t('organization')" name="organization_id">
            <a-select
              v-model:value="forum.organization_id"
              :options="organizations"
              :fieldNames="{ value: 'id', label: 'name_zh' }"
              :placeholder="$t('please_select_organization')"
            />
          </a-form-item>

          <a-form-item :label="$t('title')" name="title">
            <a-input
              type="input"
              v-model:value="forum.title"
              :placeholder="$t('please_input_title')"
            />
          </a-form-item>

          <a-form-item :label="$t('description')" name="description">
            <a-textarea v-model:value="forum.description" :rows="6" />
          </a-form-item>

          <div class="flex flex-row items-center justify-center">
            <a-button type="primary" html-type="submit">{{ $t("submit") }}</a-button>
          </div>
        </a-form>
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
  props: ["organizations", "forum"],
  data() {
    return {
      rules: {
        organization_id: { required: true },
        title: { required: true },
      },
      validateMessages: {
        required: "${label} is required!",
      },
    };
  },
  methods: {
    onFinish() {
      if (this.forum.id) {
        this.$inertia.post(
          route("admin.forums.update", this.forum.id),
          {
            _method: "PATCH",
            organization_id: this.forum.organization_id,
            title: this.forum.title,
            description: this.forum.description,
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
      } else {
        this.$inertia.post(
          route("admin.forums.store"),
          {
            organization_id: this.forum.organization_id,
            title: this.forum.title,
            description: this.forum.description,
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
      }
    },
  },
};
</script>
