<template>
  <AdminLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("feature") }}
      </h2>
    </template>
    <div class="container mx-auto pt-5">
      <div class="bg-white relative shadow rounded-lg overflow-x-auto p-5">
        <a-form ref="modalRef" :model="feature" name="Teacher" :label-col="{ span: 4 }" :wrapper-col="{ span: 20 }"
          autocomplete="off" :rules="rules" :validate-messages="validateMessages" @finish="onFinish"
          enctype="multipart/form-data">
          <a-form-item :label="$t('organization')" name="organization_id">
            <a-select v-model:value="feature.organization_id" :options="organizations"
              :fieldNames="{ value: 'id', label: 'name_zh' }" />
          </a-form-item>
          <!-- Hide other languages-->
          <!-- 
          <a-row>
            <a-col :span="4">
            </a-col>
            <a-col>
              <a-tabs v-model:activeKey="selectedLang">
                <a-tab-pane key="zh" :tab="$t('chinese')" />
                <a-tab-pane key="en" :tab="$t('english')" />
                <a-tab-pane key="pt" :tab="$t('portuguese')" />
              </a-tabs>
            </a-col>
          </a-row>
           -->
          <template v-if="selectedLang == 'zh'">
            <a-form-item :label="$t('title')" name="title_zh">
              <a-input v-model:value="feature.title_zh" />
            </a-form-item>
            <a-form-item :label="$t('content')" name="content_zh">
              <a-textarea v-model:value="feature.content_zh" :rows="5" />
            </a-form-item>
            <a-form-item :label="$t('tag')">
              <a-select v-model:value="feature.tags_zh" mode="tags" style="width: 100%" placeholder="Tags Mode"
                :options="tagOptions"></a-select>
            </a-form-item>
          </template>
          <!-- Hide other languages-->
          <template v-if="selectedLang == 'en'">
            <a-form-item :label="$t('title')">
              <a-input v-model:value="feature.title_en" />
            </a-form-item>
            <a-form-item :label="$t('content')">
              <a-textarea v-model:value="feature.content_en" :rows="5" />
            </a-form-item>
            <a-form-item :label="$t('tag')">
              <a-input v-model:value="feature.tags_en" />
            </a-form-item>
          </template>

          <template v-if="selectedLang == 'pt'">
            <a-form-item :label="$t('title')">
              <a-input v-model:value="feature.title_pt" />
            </a-form-item>
            <a-form-item :label="$t('content')">
              <a-textarea v-model:value="feature.content_pt" :rows="5" />
            </a-form-item>
            <a-form-item :label="$t('tag')">
              <a-input v-model:value="feature.tags_pt" />
            </a-form-item>
          </template>
          <a-form-item :label="$t('url')">
            <a-input v-model:value="feature.url" />
          </a-form-item>
          <a-form-item :label="$t('image')">
            <template v-if="feature.image">
              <img :src="feature.image" width="300px" />
              <a @click="onDeleteImage(feature)">Delete</a>
            </template>
            <template v-else>
              <template v-if="previewImage">
                <img :src="previewImage" class="w-64"/>
                <a @click="onRemoveImage">Remove</a>
              </template>
              <template v-else>
                <div class="flex items-center justify-center w-64">
                  <label for="dropzone-file"
                    class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                      </svg>
                      <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                        <div v-html="$t('upload_drag_drop')"/>
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ $t('image_size_note') }}</p>
                    </div>
                    <input id="dropzone-file" type="file" @change="onSelectFile" accept="image/png, image/gif, image/jpeg" style="display:none" />
                  </label>
                </div>

              </template>
            </template>
          </a-form-item>
          <div class="flex flex-row item-center justify-center">
            <a-button type="primary" html-type="submit">{{ $t('submit') }}</a-button>
          </div>
        </a-form>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { PlusOutlined, LoadingOutlined } from '@ant-design/icons-vue';

export default {
  components: {
    AdminLayout,
    PlusOutlined,
    LoadingOutlined
  },
  props: ["organizations", "feature"],
  data() {
    return {
      selectedLang: 'zh',
      previewImage: null,
      tagOptions: [{ 'value': '學習' }, { 'value': '公佈' }, { 'value': '交流' }, { 'value': '分享' }],
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
    //this.organizations.unshift({ id: 0, full_name: "General Config Item" });
  },
  methods: {
    onSelectFile(event) {
      const file =event.target.files[0]
      console.log(file.size);
      console.log(1024*1024);

      if(file.size > 1024*1024*1){
        alert('oversize')
        return false
      }
      //this.variffyUpload(file)
      this.feature.image_upload = file
      this.previewImage = URL.createObjectURL(file)

    },
    onRemoveImage() {
      this.feature.image_upload = null
      this.previewImage = null
    },
    onDeleteImage(feature) {
      this.$inertia.post(route('admin.feature.deleteImage', this.feature), {
        onSuccess: (page) => {
          console.log(page)
        },
        onError: (err) => {
          console.log(err);
        },
      })

    },
    onFinish() {
      if (this.feature.id) {
        this.feature._method = "PATCH"
        this.$inertia.post(route('admin.features.update', this.feature.id), this.feature, {
          onSuccess: (page) => {
            console.log(page)
          },
          onError: (err) => {
            console.log(err);
          },
        })
      } else {
        this.$inertia.post(route('admin.features.store'), this.feature, {
          onSuccess: (page) => {
            console.log(page)
          },
          onError: (err) => {
            console.log(err);
          },
        })
      }
    },
  },
};
</script>
