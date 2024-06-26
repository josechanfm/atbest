<template>
  <OrganizationLayout :title="$t('article')">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $t("article") }}
      </h2>
    </template>
    <!-- <a-button @click="getCursor()">{{ $t("cursor") }}</a-button> -->
    
      <div class="flex-auto pb-3 text-right pb-3">
        <a-button @click="isDrawerVisible = !isDrawerVisible" type="primary">{{
          $t("Images")
        }}</a-button>
      </div>
      <div class="bg-white relative shadow rounded-lg overflow-x-auto p-5">
        <a-form
          :model="article"
          name="Teacher"
          layout="vertical"
          autocomplete="off"
          :rules="rules"
          :validate-messages="validateMessages"
          @finish="onFinish"
          @finishFailed="onFinishFailed"
          enctype="multipart/form-data"
        >
          <a-form-item :label="$t('article_category')" name="category_code">
            <a-select
              v-model:value="article.category_code"
              :options="articleCategories"
            />
          </a-form-item>
          <a-form-item :label="$t('title')" name="title">
            <a-input v-model:value="article.title" />
          </a-form-item>
          <a-form-item :label="$t('intro')" name="intro">
            <a-textarea v-model:value="article.intro" :rows="5"/>
          </a-form-item>

          <a-form-item :label="$t('content')" name="content">

            <quill-editor 
              v-model:value="article.content" 
              @paste="handleImagePaste"
            />

          </a-form-item>
          <a-form-item :label="$t('valid_at')" name="valid_at">
            <a-date-picker
              v-model:value="article.valid_at"
              :format="dateFormat"
              :valueFormat="dateFormat"
            />
          </a-form-item>
          <a-form-item :label="$t('expired_at')" name="expired_at">
            <a-date-picker v-model:value="article.expire_at" :valueFormat="dateFormat" :format="dateFormat"/>
          </a-form-item>
          <a-form-item :label="$t('url')" name="url">
            <a-input v-model:value="article.url" />
          </a-form-item>
          <a-row>
            <a-col>
              <a-form-item :label="$t('published')" name="published">
                <a-switch
                  v-model:checked="article.published"
                  @change="article.public = 0" 
                />
              </a-form-item>
            </a-col>
            <a-col class="pl-10" v-if="article.published">
              <a-form-item :label="$t('public')" name="public">
                <a-switch
                  v-model:checked="article.public"
                />
              </a-form-item>
            </a-col>
          </a-row>
          <a-form-item :label="$t('tag')">
            <a-select v-model:value="article.tags" mode="tags" style="width: 100%" placeholder="Tags Mode"
              :options="tagOptions"></a-select>
          </a-form-item>


              <FileUploader
                :file="article.thumbnail"
                :maxSize="5120"
                :maxWidth="1024"
                :maxHeight="768"
                :allowedTypes="['image/jpeg', 'image/png']"
                @upload="handleFileUpload"
                @delete="handleFileDelete"
              />

          <div class="flex flex-row item-center justify-center">
            <a-button type="primary" html-type="submit">{{ $t("submit") }}</a-button>
          </div>
        </a-form>
      </div>
      <a :href="route('article.item',{t:article.uuid})" target="_blank" ref="articleUrl">{{ route('article.item',{t:article.uuid}) }}</a>
      <a-button @click="copyUrl">{{ $t('copy_to_clipboard') }}</a-button>
      <p>Article CAN NOT be delete if published.</p>
    

    <a-drawer
      v-model:visible="isDrawerVisible"
      class="custom-class"
      title="Basic Drawer"
      placement="right"
      @after-visible-change="afterVisibleChange"
    >
      <div class="h-max-48 overflow-auto">
        Design for files and images 
        with media library<br>
        comming soon...
        <ul>
          <li v-for="media in medias" @click="selectMedia(media)">
            {{ media.file_name }}
          </li>
        </ul>
      </div>
      <div>
        <div v-if="selectedMedia">
          <img
            v-if="selectedMedia.preview_url"
            :src="selectedMedia.preview_url"
            width="100"
          />
          <img v-else :src="selectedMedia.original_url" width="100" />
          <a-button @click="addToArticle">{{ $t("add") }}</a-button>
        </div>
      </div>
    </a-drawer>
  </OrganizationLayout>
</template>

<script>
import OrganizationLayout from "@/Layouts/OrganizationLayout.vue";
import { defineComponent, reactive } from "vue";
//import Editor from 'ckeditor5-custom-build/build/ckeditor';
//import CKEditor from "@ckeditor/ckeditor5-vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import UploadAdapter from "@/Components/ImageUploadAdapter.vue";
import { message } from "ant-design-vue";
import { ConsoleSqlOutlined, UploadOutlined } from '@ant-design/icons-vue'
import FileUploader from '@/Components/FileUploader.vue'
import { quillEditor } from 'vue3-quill';

export default {
  components: {
    OrganizationLayout,
    UploadAdapter,
    UploadOutlined,
    FileUploader,
    quillEditor,
    //UploadAdapter
  },
  props: ["classifies", "articleCategories", "article"],
  data() {
    return {
      medias: [],
      file:null,
      thumbnailUpload:null,
      previewImage: null,
      selectedMedia: null,
      isDrawerVisible: false,
      tagOptions: [{ 'value': '學習' }, { 'value': '公佈' }, { 'value': '交流' }, { 'value': '分享' }],
      dateFormat: "YYYY-MM-DD",

      editorOptions: {
        modules: {
          // ... other modules
          imageResize: {
            displaySize: true, // Show the image size
            customHandler: (img, src, width, height) => {
              // Check if the image size is larger than 1MB
              if (src.length > 1024 * 1024) {
                // Resize the image to a maximum width of 1024 pixels
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = 1024;
                canvas.height = (height / width) * 1024;
                context.drawImage(img, 0, 0, canvas.width, canvas.height);
                return canvas.toDataURL('image/jpeg', 0.7);
              }
              return src;
            },
          },
        },
      },
      editor: ClassicEditor,
      editorData: "<p>Content of the editor.</p>",
      editorConfig: {
        extraPlugins: [
          function (editor) {
            editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
              return new UploadAdapter(loader);
            };
          },
        ],
        // The configuration of the editor.
      },
      rules: {
        category_code: { required: true },
        title: { required: true },
        valid_at: { required: true },
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

  },
  methods: {
    uploader(editor) {
      editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
        return new UploadAdapter(loader);
      };
    },
    onFinish(event) {
     this.article.thumbnail_upload=this.thumbnailUpload;
      if (this.article.id) {
        this.article._method='PATCH';
        this.$inertia.post(route("manage.articles.update", this.article.id), this.article, {
          onSuccess: (page) => {
            console.log(page);
          },
          onError: (error) => {
            console.log(error);
          },
        });
      } else {
        this.$inertia.post(route("manage.articles.store"), this.article, {
          onSuccess: (page) => {
          },
          onError: (err) => {
            console.log(err);
          },
        });
      }
    },
    onFinishFailed({ values, errorFields, outOfDate }){
      message.error("Some required fields are missing!");
    },
    afterVisibleChange(bool) {
      if (bool) {
        axios.get(route("manage.medias", 22)).then((response) => {
          this.medias = response.data;
        });
      }
    },
    selectMedia(media) {
      this.selectedMedia = media;
    },
    addToArticle(meida) {
      var selection = this.editor.view.state.selection;
    },
    ckEditorFocusOut(event) {
    },

    copyUrl(){
      navigator.clipboard.writeText(this.$refs.articleUrl.href)
    },
    handleFileUpload(file) {
      this.file = file;
      this.thumbnailUpload= file
    },  
    handleFileDelete(file){
      this.$inertia.post(route('manage.article.deleteImage', this.article), {
        onSuccess: (page) => {
          console.log(page)
        },
        onError: (err) => {
          console.log(err);
        },
      })
    },
    handleImagePaste(event){
      const clipboardItems = event.clipboardData.items;
      
      for (let i = 0; i < clipboardItems.length; i++) {
        const item = clipboardItems[i];
        if (item.type.indexOf('image') !== -1) {
          const blob = item.getAsFile();
          // const dimensionResult = await imageSize(blob);
          // const { width, height } = dimensionResult;
          // if (blob.size > 1048576) { // 1 MB
          //   const compressedBlob = await imageCompression(blob, {
          //     maxSizeMB: 1,
          //     maxWidthOrHeight: 1920,
          //     useWebWorker: true,
          //   });
          //   const reader = new FileReader();
          //   reader.onload = () => {
          //     const range = this.quill.getSelection(true);
          //     this.quill.insertEmbed(range.index, 'image', reader.result);
          //     this.quill.setSelection(range.index + 1, 0);
          //   };
          //   reader.readAsDataURL(compressedBlob);
          // } else {
          //   const reader = new FileReader();
          //   reader.onload = () => {
          //     const range = this.quill.getSelection(true);
          //     this.quill.insertEmbed(range.index, 'image', reader.result);
          //     this.quill.setSelection(range.index + 1, 0);
          //   };
          //   reader.readAsDataURL(blob);
          // }
        }
      }
    }
  },

};
</script>

<style scope>
.ck-editor .ck-editor__main .ck-content {
  min-height: 500px;
}
</style>
