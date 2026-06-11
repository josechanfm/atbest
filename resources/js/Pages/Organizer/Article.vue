<template>
  <OrganizerLayout :title="$t('article')" :breadcrumb="breadcrumb">
    <div class="flex justify-end pb-3 gap-3">
      <a-button @click="isDrawerVisible = !isDrawerVisible" type="primary">
        {{ $t("Images") }}
      </a-button>
    </div>
    <div class="bg-white relative shadow rounded-lg overflow-x-auto p-5">
      <a-form
        ref="formRef"
        :model="article"
        name="article"
        layout="vertical"
        autocomplete="off"
        :rules="rules"
        :validate-messages="validateMessages"
        @finish="onFinish"
        @finishFailed="onFinishFailed"
      >
        <a-form-item :label="$t('article_category')" name="category_code">
          <a-select
            v-model:value="article.category_code"
            :options="articleCategories"
          />
        </a-form-item>
        <a-form-item :label="$t('title')" name="title">
          <a-input type="input" v-model:value="article.title" />
        </a-form-item>
        <a-form-item :label="$t('intro')" name="intro">
          <a-textarea v-model:value="article.intro" :rows="5"/>
        </a-form-item>
        <a-form-item :label="$t('content')" name="content">
          <RichTextEditor v-model="article.content"/>
        </a-form-item>
        <a-form-item :label="$t('valid_at')" name="valid_at">
          <a-date-picker
            v-model:value="article.valid_at"
            :format="dateFormat"
            :valueFormat="dateFormat"
          />
        </a-form-item>
        <a-form-item :label="$t('expired_at')" name="expire_at">
          <a-date-picker v-model:value="article.expire_at" :valueFormat="dateFormat" :format="dateFormat"/>
        </a-form-item>
        <a-form-item :label="$t('url')" name="url">
          <a-input type="input" v-model:value="article.url" />
        </a-form-item>
        <a-row>
          <a-col>
            <a-form-item :label="$t('published')" name="published">
              <a-switch
                v-model:checked="article.published"
                @change="handlePublishedChange"
              />
            </a-form-item>
          </a-col>
          <a-col class="pl-10" v-if="article.published">
            <a-form-item :label="$t('for_member')" name="for_member">
              <a-switch
                v-model:checked="article.for_member"
              />
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item :label="$t('tag')">
          <a-select v-model:value="article.tags" mode="tags" style="width: 100%" placeholder="Tags Mode" :options="tagOptions"></a-select>
        </a-form-item>

        <!-- Banner 圖片上傳（延遲上傳） -->
        <a-form-item :label="$t('banner')" name="banner_image">
          <div class="image-upload-container">
            <!-- 顯示已存在的圖片 -->
            <div v-if="article.banner_url && !tempImages.banner" class="image-preview-card">
              <img :src="article.banner_url" class="preview-image" />
              <div class="image-overlay">
                <a-popconfirm
                  :title="$t('confirm_delete_image')"
                  :ok-text="$t('yes')"
                  :cancel-text="$t('no')"
                  @confirm="removeExistingImage('banner')"
                >
                  <button type="button" class="delete-btn">
                    <DeleteOutlined />
                    <span>{{ $t('delete') }}</span>
                  </button>
                </a-popconfirm>
              </div>
            </div>
            
            <!-- 顯示暫存的圖片（剛選擇但未送出） -->
            <div v-if="tempImages.banner" class="image-preview-card temp-image">
              <img :src="tempImages.banner.preview" class="preview-image" />
              <div class="image-overlay">
                <button type="button" class="delete-btn" @click="removeTempImage('banner')">
                  <CloseOutlined />
                  <span>{{ $t('remove') }}</span>
                </button>
              </div>
            </div>
            
            <!-- 上傳按鈕 -->
            <a-upload
              v-if="!article.banner_url && !tempImages.banner"
              :before-upload="(file) => handleBeforeUpload(file, 'banner')"
              :show-upload-list="false"
              :accept="'image/*'"
              :custom-request="() => {}"
            >
              <div class="upload-trigger">
                <PlusOutlined />
                <div class="ant-upload-text">{{ $t('upload_banner') }}</div>
              </div>
            </a-upload>
          </div>
        </a-form-item>

        <!-- 縮圖上傳（延遲上傳） -->
        <a-form-item :label="$t('thumbnail')" name="thumb_image">
          <div class="image-upload-container">
            <!-- 顯示已存在的圖片 -->
            <div v-if="article.thumb_url && !tempImages.thumb" class="image-preview-card">
              <img :src="article.thumb_url" class="preview-image" />
              <div class="image-overlay">
                <a-popconfirm
                  :title="$t('confirm_delete_image')"
                  :ok-text="$t('yes')"
                  :cancel-text="$t('no')"
                  @confirm="removeExistingImage('thumb')"
                >
                  <button type="button" class="delete-btn">
                    <DeleteOutlined />
                    <span>{{ $t('delete') }}</span>
                  </button>
                </a-popconfirm>
              </div>
            </div>
            
            <!-- 顯示暫存的圖片（剛選擇但未送出） -->
            <div v-if="tempImages.thumb" class="image-preview-card temp-image">
              <img :src="tempImages.thumb.preview" class="preview-image" />
              <div class="image-overlay">
                <button type="button" class="delete-btn" @click="removeTempImage('thumb')">
                  <CloseOutlined />
                  <span>{{ $t('remove') }}</span>
                </button>
              </div>
            </div>
            
            <!-- 上傳按鈕 -->
            <a-upload
              v-if="!article.thumb_url && !tempImages.thumb"
              :before-upload="(file) => handleBeforeUpload(file, 'thumb')"
              :show-upload-list="false"
              :accept="'image/*'"
              :custom-request="() => {}"
            >
              <div class="upload-trigger">
                <PlusOutlined />
                <div class="ant-upload-text">{{ $t('upload_thumbnail') }}</div>
              </div>
            </a-upload>
          </div>
        </a-form-item>

        <div class="flex flex-row item-center justify-center">
          <a-button type="primary" html-type="submit" :loading="submitting">{{ $t("submit") }}</a-button>
        </div>
      </a-form>
    </div>

    <div class="mt-4 pt-4 border-t" v-if="article.uuid">
      <div class="flex items-center gap-2">
        <a :href="articleUrl" target="_blank" ref="articleUrl">
          {{ articleUrl }}
        </a>
        <a-button @click="copyUrl" size="small">{{ $t('copy_to_clipboard') }}</a-button>
      </div>
    </div>
    <p v-if="article.published" class="text-yellow-600 mt-2">{{$t('article_can_not_be_delete_if_published')}}</p>
    
    <a-drawer
      v-model:open="isDrawerVisible"
      class="custom-class"
      :title="$t('media_library')"
      placement="right"
      @after-open-change="afterOpenChange"
    >
      <div class="h-max-48 overflow-auto">
        <div v-if="medias.length === 0" class="text-center text-gray-500 py-8">
          {{ $t('no_media_found') }}
        </div>
        <ul v-else>
          <li v-for="media in medias" :key="media.id" @click="selectMedia(media)" class="media-item">
            {{ media.file_name }}
          </li>
        </ul>
      </div>
      <div v-if="selectedMedia" class="mt-4 pt-4 border-t">
        <img
          v-if="selectedMedia.preview_url"
          :src="selectedMedia.preview_url"
          width="100"
        />
        <img v-else :src="selectedMedia.original_url" width="100" />
        <div class="mt-2">
          <a-button @click="addToArticle" type="primary">{{ $t("add") }}</a-button>
        </div>
      </div>
    </a-drawer>
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { message } from "ant-design-vue";
import { PlusOutlined, DeleteOutlined, CloseOutlined, LoadingOutlined } from '@ant-design/icons-vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'; 

export default {
  components: {
    OrganizerLayout,
    PlusOutlined, 
    DeleteOutlined, 
    CloseOutlined,
    LoadingOutlined,
    RichTextEditor
  },
  props: ["classifies", "articleCategories", "article"],
  data() {
    return {
      breadcrumb: [
        { label: "文章列表", url: route('organizer.articles.index')},
        { label: this.article.id ? '文章修改' : '文章新增', url: null }
      ],
      medias: [],
      selectedMedia: null,
      isDrawerVisible: false,
      tagOptions: [{ 'value': '學習' }, { 'value': '公佈' }, { 'value': '交流' }, { 'value': '分享' }],
      dateFormat: "YYYY-MM-DD",
      submitting: false,
      
      // 暫存使用者選擇的圖片（未送出）
      tempImages: {
        banner: null,  // { file: File, preview: string }
        thumb: null
      },
      
      // 記錄要刪除的圖片
      imagesToDelete: {
        banner: false,
        thumb: false
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
  computed: {
    articleUrl() {
      return this.article.uuid ? route('article.item', { t: this.article.uuid }) : '';
    }
  },
  mounted() {
    // 確保從後端取得的資料是正確的布林值
    if (this.article) {
      if (this.article.published !== undefined) {
        this.article.published = Boolean(this.article.published);
      }
      if (this.article.for_member !== undefined) {
        this.article.for_member = Boolean(this.article.for_member);
      }
    }
  },
  methods: {
    // 處理 published 狀態改變
    handlePublishedChange(checked) {
      // 當發佈狀態改變時，將 for_member 設為 false
      this.article.for_member = false;
    },
    
    // 處理檔案選擇（在 beforeUpload 中直接處理）
    handleBeforeUpload(file, type) {
      // 驗證檔案
      const isImage = file.type.startsWith('image/');
      if (!isImage) {
        message.error(this.$t('only_images_allowed') || '只能上傳圖片檔案');
        return false;
      }
      
      const isLt2M = file.size / 1024 / 1024 < 2;
      if (!isLt2M) {
        message.error(this.$t('image_size_limit') || '圖片大小不能超過 2MB');
        return false;
      }
      
      // 產生預覽用的 URL
      const previewUrl = URL.createObjectURL(file);
      
      // 暫存檔案
      this.tempImages[type] = {
        file: file,
        preview: previewUrl
      };
      
      // 回傳 false 阻止自動上傳
      return false;
    },
    
    // 移除暫存的圖片
    removeTempImage(type) {
      if (this.tempImages[type] && this.tempImages[type].preview) {
        URL.revokeObjectURL(this.tempImages[type].preview);
      }
      this.tempImages[type] = null;
      message.success(this.$t('image_removed') || '圖片已移除');
    },
    
    // 移除已存在的圖片（標記為待刪除）
    removeExistingImage(type) {
      const imageField = type === 'banner' ? 'banner_url' : 'thumb_url';
      this.article[imageField] = null;
      this.imagesToDelete[type] = true;
      message.success(this.$t('image_will_be_deleted') || '圖片將在送出時刪除');
    },
    
    // 建構 FormData（用於上傳檔案）
    buildFormData() {
      const formData = new FormData();
      
      // 加入所有表單欄位
      Object.keys(this.article).forEach(key => {
        // 跳過圖片相關的欄位和不需要提交的欄位
        const skipFields = ['banner_image', 'thumb_image', 'banner_url', 'thumb_url'];
        if (skipFields.includes(key)) {
          return;
        }
        
        if (this.article[key] !== null && this.article[key] !== undefined) {
          // 處理日期格式
          if (key === 'valid_at' || key === 'expire_at') {
            if (this.article[key]) {
              formData.append(key, this.article[key]);
            }
          } 
          // 處理 published 和 for_member 布林值轉換
          else if (key === 'published' || key === 'for_member') {
            // 將布林值轉換為 0 或 1（整數）
            formData.append(key, this.article[key] ? 1 : 0);
          }
          // 處理 tags 陣列
          else if (key === 'tags' && Array.isArray(this.article[key])) {
            this.article[key].forEach(tag => {
              if (tag && tag.trim()) {
                formData.append('tags[]', tag);
              }
            });
          }
          // 處理一般欄位
          else {
            formData.append(key, this.article[key]);
          }
        }
      });
      
      // 加入要上傳的新圖片
      if (this.tempImages.banner) {
        formData.append('banner_image', this.tempImages.banner.file);
      }
      
      if (this.tempImages.thumb) {
        formData.append('thumb_image', this.tempImages.thumb.file);
      }
      
      // 加入要刪除的圖片標記
      if (this.imagesToDelete.banner) {
        formData.append('delete_banner', '1');
      }
      
      if (this.imagesToDelete.thumb) {
        formData.append('delete_thumb', '1');
      }
      
      // 處理 _method 用於 PUT/PATCH 請求
      if (this.article.id) {
        formData.append('_method', 'PATCH');
      }
      
      return formData;
    },

    onFinish() {
      this.submitting = true;
      
      const url = this.article.id 
        ? route("organizer.articles.update", this.article.id)
        : route("organizer.articles.store");
      
      // 使用 FormData 來支援檔案上傳
      const formData = this.buildFormData();
      
      // 使用 Inertia 的 post 方法
      this.$inertia.post(url, formData, {
        preserveState: true,
        onSuccess: (page) => {
          this.submitting = false;
          message.success(this.article.id ? this.$t('update_success') || '更新成功' : this.$t('create_success') || '新增成功');
          
          // 清除暫存的圖片 URL
          if (this.tempImages.banner?.preview) {
            URL.revokeObjectURL(this.tempImages.banner.preview);
          }
          if (this.tempImages.thumb?.preview) {
            URL.revokeObjectURL(this.tempImages.thumb.preview);
          }
          
          // 重置圖片暫存
          this.tempImages = { banner: null, thumb: null };
          this.imagesToDelete = { banner: false, thumb: false };
          
          // 如果有返回更新的資料，更新 article
          if (page.props.article) {
            // 更新圖片 URL
            this.article.banner_url = page.props.article.banner_url;
            this.article.thumb_url = page.props.article.thumb_url;
            this.article.uuid = page.props.article.uuid;
            
            // 更新其他可能的欄位
            if (page.props.article.published !== undefined) {
              this.article.published = Boolean(page.props.article.published);
            }
            if (page.props.article.for_member !== undefined) {
              this.article.for_member = Boolean(page.props.article.for_member);
            }
          }
          
          if (!this.article.id && page.props.article?.id) {
            // 新增成功後重新導向到編輯頁面
            this.$inertia.visit(route('organizer.articles.edit', page.props.article.id));
          }
        },
        onError: (error) => {
          this.submitting = false;
          console.error('Submit error:', error);
          
          // 顯示錯誤訊息
          if (error.message) {
            message.error(error.message);
          } else if (error.errors) {
            const errorMessages = Object.values(error.errors).flat();
            message.error(errorMessages.join(', '));
          } else {
            message.error(this.$t('submit_failed') || '送出失敗，請稍後再試');
          }
        },
      });
    },
    
    onFinishFailed({ values, errorFields, outOfDate }){
      const missingFields = errorFields.map(field => field.name[0]).join(', ');
      message.error(`${this.$t('missing_required_fields') || '缺少必填欄位'}: ${missingFields}`);
    },
    
    afterOpenChange(bool) {
      if (bool) {
        axios.get(route("organizer.medias", 22)).then((response) => {
          this.medias = response.data;
        });
      }
    },
    
    selectMedia(media) {
      this.selectedMedia = media;
    },
    
    addToArticle() {
      // 處理從媒體庫選擇圖片加入編輯器
      if (this.selectedMedia && this.$refs.editor) {
        const imageUrl = this.selectedMedia.preview_url || this.selectedMedia.original_url;
        // 假設 RichTextEditor 有插入圖片的方法，需要根據實際元件調整
        message.info(this.$t('feature_coming_soon') || '功能開發中');
      }
    },

    copyUrl() {
      if (this.$refs.articleUrl) {
        navigator.clipboard.writeText(this.$refs.articleUrl.href);
        message.success(this.$t('copied_to_clipboard') || '已複製到剪貼簿');
      }
    },
  },
  
  // 元件銷毀時清除暫存圖片的 URL
  beforeUnmount() {
    if (this.tempImages.banner?.preview) {
      URL.revokeObjectURL(this.tempImages.banner.preview);
    }
    if (this.tempImages.thumb?.preview) {
      URL.revokeObjectURL(this.tempImages.thumb.preview);
    }
  },
};
</script>

<style scoped>
/* 圖片上傳容器 */
.image-upload-container {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

/* 圖片預覽卡片 */
.image-preview-card {
  position: relative;
  width: 150px;
  height: 150px;
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #f0f0f0;
  transition: all 0.3s ease;
  background: #fafafa;
  cursor: pointer;
}

.image-preview-card:hover {
  border-color: #4096ff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.image-preview-card.temp-image {
  border-color: #52c41a;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* 圖片覆蓋層 */
.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.image-preview-card:hover .image-overlay {
  opacity: 1;
}

/* 刪除按鈕 */
.delete-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(255, 77, 79, 0.9);
  border: none;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  backdrop-filter: blur(4px);
}

.delete-btn:hover {
  background: #ff4d4f;
  transform: scale(1.05);
}

.delete-btn:active {
  transform: scale(0.95);
}

.delete-btn .anticon {
  font-size: 20px;
}

.delete-btn span {
  font-size: 12px;
  font-weight: 500;
}

/* 上傳觸發器 */
.upload-trigger {
  width: 150px;
  height: 150px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 2px dashed #d9d9d9;
  border-radius: 12px;
  background-color: #fafafa;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upload-trigger:hover {
  border-color: #4096ff;
  background-color: #f5f5f5;
  transform: translateY(-2px);
}

.upload-trigger .anticon {
  font-size: 32px;
  color: #999;
  margin-bottom: 8px;
}

.upload-trigger .ant-upload-text {
  font-size: 12px;
  color: #666;
}

/* 媒體庫項目樣式 */
.media-item {
  padding: 8px 12px;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.media-item:hover {
  background-color: #f0f0f0;
}

/* 響應式設計 */
@media (max-width: 768px) {
  .image-preview-card,
  .upload-trigger {
    width: 120px;
    height: 120px;
  }
  
  .delete-btn {
    padding: 6px 12px;
  }
  
  .delete-btn .anticon {
    font-size: 16px;
  }
  
  .delete-btn span {
    font-size: 10px;
  }
}
</style>