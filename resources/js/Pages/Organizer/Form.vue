<template>
  <OrganizerLayout :title="form.id ? '表格修改' : '表格新增'" :breadcrumb="breadcrumb">
    <div class="bg-white relative shadow p-5 rounded-lg overflow-x-auto">
      <a-form ref="modalRef" :model="form" name="From" layout="vertical" autocomplete="off" :rules="rules"
        :validate-messages="validateMessages" @finish="onFinish" @finishFailed="onFinishFailed">
        
        <!-- 基本欄位 -->
        <a-form-item :label="$t('form_name')" name="name">
          <a-input type="input" v-model:value="form.name" />
        </a-form-item>
        
        <a-form-item :label="$t('title')" name="title">
          <a-input type="input" v-model:value="form.title" />
        </a-form-item>
        
        <!-- 歡迎訊息 -->
        <div class="text-right">
          <a @click="form.openWelcome = !form.openWelcome">{{ $t('form_welcome') }}</a>
        </div>
        <a-form-item :label="$t('form_welcome')" name="welcome" v-if="form.openWelcome">
          <RichTextEditor v-model="form.welcome"/>
        </a-form-item>
        
        <!-- 內容 -->
        <a-form-item :label="$t('content')" name="content">
          <RichTextEditor v-model="form.content"/>
        </a-form-item>
        
        <!-- 感謝訊息 -->
        <div class="text-right">
          <a @click="form.openThanks = !form.openThanks">{{ $t('form_thankyou') }}</a>
        </div>
        <a-form-item :label="$t('form_thankyou')" name="thanks" v-if="form.openThanks">
          <RichTextEditor v-model="form.thanks"/>
        </a-form-item>
        
        <!-- 日期設定 -->
        <a-form-item :label="$t('valid_at')" name="valid_at">
          <a-date-picker
            v-model:value="form.valid_at"
            :format="dateFormat"
            :valueFormat="dateFormat"
          />
        </a-form-item>
        
        <a-form-item :label="$t('expired_at')" name="expired_at">
          <a-date-picker v-model:value="form.expire_at" :valueFormat="dateFormat" :format="dateFormat"/>
        </a-form-item>
        
        <!-- 登入設定 -->
        <a-form-item :label="$t('require_login')" name="require_login">
          <a-switch v-model:checked="form.require_login" @change="onRequireLoginChange" />
          <span class="pl-3">{{ $t("require_login_note") }}</span>
        </a-form-item>
        
        <a-form-item :label="$t('for_member')" name="for_member" v-if="form.require_login">
          <a-switch v-model:checked="form.for_member"/>
          <span class="pl-3">{{ $t("for_member_note") }}</span>
        </a-form-item>
        
        <!-- 發佈設定 -->
        <a-form-item :label="$t('published')" name="published">
          <a-switch v-model:checked="form.published" :disabled="form.entries_count > 0" />
          <span class="pl-3">{{ $t("published_note") }}</span><br />
          <span v-if="form.entries_count > 0" class="text-yellow-600">
            {{ $t('form_has_responses_backup_warning') }}
          </span>
        </a-form-item>
        
        <a-form-item :label="$t('with_attendance')" name="with_attendance" v-if="form.published">
          <a-switch v-model:checked="form.with_attendance"/>
          <span class="pl-3">{{ $t("with_attendance_note") }}</span>
        </a-form-item>

        <!-- Banner 圖片上傳（延遲上傳） -->
        <a-form-item :label="$t('banner')" name="banner_image">
          <div class="image-upload-container">
            <!-- 顯示已存在的圖片 -->
            <div v-if="form.banner_url && !tempImages.banner" class="image-preview-card">
              <img :src="form.banner_url" class="preview-image" />
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
              v-if="!form.banner_url && !tempImages.banner"
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
            <div v-if="form.thumb_url && !tempImages.thumb" class="image-preview-card">
              <img :src="form.thumb_url" class="preview-image" />
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
              v-if="!form.thumb_url && !tempImages.thumb"
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

        <!-- 送出按鈕 -->
        <a-form-item :wrapper-col="{ offset: 12, span: 10 }">
          <a-button type="primary" html-type="submit" :loading="submitting">
            {{ $t('submit') }}
          </a-button>
        </a-form-item>
      </a-form>
      
      <!-- 表單連結 -->
      <div v-if="form.id" class="mt-4 pt-4 border-t">
        <div class="flex items-center gap-2">
          <a :href="formUrl" target="_blank" ref="formUrl">
            {{ formUrl }}
          </a>
          <a-button @click="copyUrl" size="small">{{ $t('copy_to_clipboard') }}</a-button>
        </div>
      </div>
    </div>
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { message } from "ant-design-vue";
import { PlusOutlined, DeleteOutlined, CloseOutlined, LoadingOutlined } from "@ant-design/icons-vue";
import RichTextEditor from '@/Components/RichTextEditor.vue';

export default {
  components: {
    OrganizerLayout,
    RichTextEditor,
    PlusOutlined,
    DeleteOutlined,
    CloseOutlined,
    LoadingOutlined,
    message,
  },
  props: {
    form: {
      type: Object,
      required: true,
      default: () => ({
        openWelcome: false,
        openThanks: false,
        require_login: false,
        for_member: false,
        published: false,
        with_attendance: false,
        banner_url: null,
        thumb_url: null,
      })
    }
  },
  data() {
    return {
      breadcrumb: [
        { label: "表格列表", url: route('organizer.forms.index') },
        { label: this.form.id ? '表格修改' : '表格新增', url: null }
      ],
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
        name: { required: true, message: '請輸入表單名稱' },
        title: { required: true, message: '請輸入表單標題' },
        valid_at: { required: true, message: '請選擇生效日期' },
      },
      validateMessages: {
        required: "${label} is required!",
      },
    };
  },
  computed: {
    formUrl() {
      return this.form.uuid ? route('form.item', { t: this.form.uuid }) : '';
    }
  },
  methods: {
    // 處理檔案選擇（在 beforeUpload 中直接處理）
    handleBeforeUpload(file, type) {
      // 驗證檔案
      const isImage = file.type.startsWith('image/');
      if (!isImage) {
        message.error(this.$t('only_images_allowed'));
        return false;
      }
      
      const isLt2M = file.size / 1024 / 1024 < 2;
      if (!isLt2M) {
        message.error(this.$t('image_size_limit'));
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
      message.success(this.$t('image_removed'));
    },
    
    // 移除已存在的圖片（標記為待刪除）
    removeExistingImage(type) {
      const imageField = type === 'banner' ? 'banner_url' : 'thumb_url';
      this.form[imageField] = null;
      this.imagesToDelete[type] = true;
      message.success(this.$t('image_will_be_deleted'));
    },
    
    // 建構 FormData（用於上傳檔案）
    buildFormData() {
      const formData = new FormData();
      
      // 加入所有表單欄位
      Object.keys(this.form).forEach(key => {
        // 跳過圖片相關的欄位和不需要提交的欄位
        const skipFields = ['banner_image', 'thumb_image', 'banner_url', 'thumb_url', 'openWelcome', 'openThanks', 'entries_count'];
        if (skipFields.includes(key)) {
          return;
        }
        
        if (this.form[key] !== null && this.form[key] !== undefined) {
          // 處理日期格式
          if (key === 'valid_at' || key === 'expire_at') {
            if (this.form[key]) {
              formData.append(key, this.form[key]);
            }
          } 
          // 處理布林值
          else if (typeof this.form[key] === 'boolean') {
            formData.append(key, this.form[key] ? '1' : '0');
          }
          // 處理一般欄位
          else {
            formData.append(key, this.form[key]);
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
      
      // 處理 _method 用於 PUT 請求
      if (this.form.id) {
        formData.append('_method', 'PUT');
      }
      
      return formData;
    },
    
    // 登入需求變更
    onRequireLoginChange(checked) {
      if (!checked) {
        this.form.for_member = false;
      }
    },
    
    // 表單送出（真正上傳圖片）
    onFinish() {
      this.submitting = true;
      
      const url = this.form.id 
        ? route("organizer.forms.update", this.form.id)
        : route("organizer.forms.store");
      
      // 使用 FormData 來支援檔案上傳
      const formData = this.buildFormData();
      
      // 使用 Inertia 的 post 方法
      this.$inertia.post(url, formData, {
        preserveState: true,
        onSuccess: (page) => {
          this.submitting = false;
          message.success(this.form.id ? this.$t('update_success') : this.$t('create_success'));
          
          // 清除暫存的圖片 URL
          if (this.tempImages.banner?.preview) {
            URL.revokeObjectURL(this.tempImages.banner.preview);
          }
          if (this.tempImages.thumb?.preview) {
            URL.revokeObjectURL(this.tempImages.thumb.preview);
          }
          
          // 如果有返回更新的資料，更新 form
          if (page.props.form) {
            this.form.banner_url = page.props.form.banner_url;
            this.form.thumb_url = page.props.form.thumb_url;
          }
          
          if (!this.form.id && page.props.form?.id) {
            // 新增成功後重新導向到編輯頁面
            this.$inertia.visit(route('organizer.forms.edit', page.props.form.id));
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
            message.error(this.$t('submit_failed'));
          }
        },
      });
    },
    
    onFinishFailed({ values, errorFields }) {
      const missingFields = errorFields.map(field => field.name[0]).join(', ');
      message.error(`${this.$t('missing_required_fields')}: ${missingFields}`);
    },
    
    copyUrl() {
      if (this.$refs.formUrl) {
        navigator.clipboard.writeText(this.$refs.formUrl.href);
        message.success(this.$t('copied_to_clipboard'));
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