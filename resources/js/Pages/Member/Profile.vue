<template>
  <MemberLayout title="Dashboard">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ $t("profile") }}
        </h2>
        <div class="text-sm text-gray-500">
          {{ $t("last_updated") }}: {{ formatDate(member.updated_at) }}
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- 个人资料卡片 -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <!-- 侧边栏 + 主要内容区 -->
          <div class="flex flex-col lg:flex-row">
            <!-- 左侧：头像区域 -->
            <div class="lg:w-80 bg-gradient-to-br from-indigo-50 to-white p-6 border-b lg:border-b-0 lg:border-r border-gray-100">
              <div class="text-center">
                <!-- 头像显示 -->
                <div class="relative inline-block">
                  <div class="w-32 h-32 mx-auto rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center overflow-hidden ring-4 ring-white shadow-lg">
                    <img 
                      v-if="avatarPreview || member.avatar" 
                      :src="avatarPreview || member.avatar" 
                      class="w-full h-full object-cover"
                      alt="Avatar"
                    />
                    <svg v-else class="w-16 h-16 text-indigo-300" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                  </div>
                  <button 
                    @click="showCropModal = true"
                    class="absolute bottom-0 right-0 bg-indigo-600 text-white p-1.5 rounded-full shadow-lg hover:bg-indigo-700 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                  </button>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-800">
                  {{ member.display_name || member.given_name }}
                </h3>
                <p class="text-sm text-gray-500">{{ member.email }}</p>
              </div>

              <!-- 快速信息 -->
              <div class="mt-6 pt-6 border-t border-gray-100">
                <div class="space-y-3">
                  <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-gray-600">{{ $t('member_id') }}:</span>
                    <span class="ml-auto font-mono text-gray-800">{{ member.member_no || member.id }}</span>
                  </div>
                  <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-gray-600">{{ $t('member_since') }}:</span>
                    <span class="ml-auto text-gray-800">{{ formatDate(member.created_at, 'YYYY/MM') }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- 右侧：表单区域 -->
            <div class="flex-1 p-6 lg:p-8">
              <a-form
                ref="formRef"
                name="profileForm"
                autocomplete="off"
                :model="member"
                layout="vertical"
                :rules="rules"
                :validate-messages="validateMessages"
              >
                <!-- 基本信息 -->
                <div class="mb-8">
                  <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $t('profile_title') }}</h3>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <a-form-item :label="$t('family_name')" name="family_name" required>
                      <a-input v-model:value="member.family_name" placeholder="請輸入姓氏" class="rounded-lg" />
                    </a-form-item>
                    <a-form-item :label="$t('given_name')" name="given_name" required>
                      <a-input v-model:value="member.given_name" placeholder="請輸入名字" class="rounded-lg" />
                    </a-form-item>
                    <a-form-item :label="$t('middle_name')" name="middle_name">
                      <a-input v-model:value="member.middle_name" placeholder="請輸入中間名（選填）" class="rounded-lg" />
                    </a-form-item>
                    <a-form-item :label="$t('display_name')" name="display_name" required>
                      <a-input v-model:value="member.display_name" placeholder="請輸入顯示名稱" class="rounded-lg" />
                    </a-form-item>
                  </div>
                </div>

                <!-- 个人资料 -->
                <div class="mb-8">
                  <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $t('personal_title') }}</h3>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <a-form-item :label="$t('dob')" name="dob">
                      <a-date-picker
                        v-model:value="member.dob"
                        :format="dateFormat"
                        :valueFormat="dateFormat"
                        placeholder="選擇出生日期"
                        class="w-full rounded-lg"
                      />
                    </a-form-item>
                    <a-form-item :label="$t('gender')" name="gender">
                      <a-radio-group v-model:value="member.gender" button-style="solid" class="flex gap-2">
                        <a-radio-button value="M" class="flex-1 text-center">{{ $t('male') }}</a-radio-button>
                        <a-radio-button value="F" class="flex-1 text-center">{{ $t('female') }}</a-radio-button>
                      </a-radio-group>
                    </a-form-item>
                    <a-form-item :label="$t('country')" name="country">
                      <a-input v-model:value="member.country" placeholder="請輸入國家/地區" class="rounded-lg" />
                    </a-form-item>
                    <a-form-item :label="$t('nationality')" name="nationality">
                      <a-input v-model:value="member.nationality" placeholder="請輸入國籍" class="rounded-lg" />
                    </a-form-item>
                    <a-form-item :label="$t('city')" name="city">
                      <a-input v-model:value="member.city" placeholder="請輸入城市" class="rounded-lg" />
                    </a-form-item>
                    <a-form-item :label="$t('address')" name="address">
                      <a-input v-model:value="member.address" placeholder="請輸入地址" class="rounded-lg" />
                    </a-form-item>
                    <a-form-item :label="$t('mobile_number')" name="mobile">
                      <a-input v-model:value="member.mobile" placeholder="請輸入手機號碼" class="rounded-lg" />
                    </a-form-item>
                    <a-form-item :label="$t('email')" name="email" required>
                      <a-input v-model:value="member.email" placeholder="請輸入電子郵件" class="rounded-lg" disabled />
                    </a-form-item>
                  </div>
                </div>

                <!-- 修改密码区域 - 修正版 -->
                <div class="mb-8">
                  <!-- 可点击的标题栏 -->
                  <div 
                    @click="togglePasswordSection"
                    class="flex items-center justify-between gap-2 mb-4 pb-2 border-b border-gray-200 cursor-pointer group hover:border-indigo-300 transition-colors duration-200"
                  >
                    <div class="flex items-center gap-2">
                      <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                      </svg>
                      <h3 class="text-lg font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors duration-200">
                        {{ $t('change_password') }}
                      </h3>
                    </div>
                    <div class="text-gray-400 group-hover:text-indigo-500 transition-all duration-200">
                      <svg 
                        class="w-5 h-5 transition-transform duration-300"
                        :class="{ 'rotate-180': isPasswordSectionOpen }"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                      </svg>
                    </div>
                  </div>

                  <!-- 使用 v-show + transition 实现平滑动画 -->
                  <transition name="slide-fade">
                    <div v-show="isPasswordSectionOpen" class="bg-gray-50 rounded-xl p-6">
                      <a-form
                        ref="passwordFormRef"
                        name="passwordForm"
                        :model="password"
                        layout="vertical"
                        :rules="passwordRules"
                      >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                          <a-form-item :label="$t('old_password')" name="old">
                            <a-input-password v-model:value="password.old" placeholder="請輸入當前密碼" class="rounded-lg" />
                          </a-form-item>
                          <div></div>
                          <a-form-item :label="$t('new_password')" name="new">
                            <a-input-password v-model:value="password.new" placeholder="請輸入新密碼" class="rounded-lg" />
                          </a-form-item>
                          <a-form-item :label="$t('confirm_password')" name="confirm">
                            <a-input-password v-model:value="password.confirm" placeholder="請確認新密碼" class="rounded-lg" />
                          </a-form-item>
                        </div>
                        <div class="flex justify-end mt-4">
                          <a-button danger @click="onFinish" class="rounded-lg px-6">
                            {{ $t('change_password') }}
                          </a-button>
                        </div>
                      </a-form>
                    </div>
                  </transition>
                </div>

                <!-- 提交按钮 -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                  <a-button @click="resetForm" class="rounded-lg px-6">
                    {{ $t('cancel') }}
                  </a-button>
                  <a-button @click="onSubmit" type="primary" :loading="loading" class="rounded-lg px-8 bg-indigo-600 hover:bg-indigo-700">
                    {{ $t('submit') }}
                  </a-button>
                </div>
              </a-form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 头像裁剪模态框 -->
    <CropperModal
      v-if="showCropModal"
      :minAspectRatioProp="{ width: 1, height: 1 }"
      :maxAspectRatioProp="{ width: 1, height: 1 }"
      @croppedImageData="setCroppedImageData"
      @showModal="showCropModal = false"
    />
  </MemberLayout>
</template>

<script>
import MemberLayout from "@/Layouts/MemberLayout.vue";
import { PlusOutlined, LoadingOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import { UploadOutlined } from "@ant-design/icons-vue";
import CropperModal from "@/Components/Member/CropperModal.vue";
import dayjs from "dayjs";

export default {
  components: {
    MemberLayout,
    PlusOutlined,
    LoadingOutlined,
    UploadOutlined,
    CropperModal,
  },
  props: ["member", "positions"],
  data() {
    return {
      dateFormat: "YYYY-MM-DD",
      showCropModal: false,
      avatarPreview: null,
      avatarData: null,
      loading: false,
      isPasswordSectionOpen: false,
      password: {
        old: '',
        new: '',
        confirm: ''
      },
      passwordRules: {
        old: [
          { required: true, message: "請輸入當前密碼" }
        ],
        new: [
          { required: true, message: "請輸入新密碼" },
          { min: 6, message: "密碼長度至少6位" }
        ],
        confirm: [
          { required: true, message: "請確認新密碼" },
          {
            validator: (rule, value) => {
              if (value && value !== this.password.new) {
                return Promise.reject("兩次輸入的密碼不一致");
              }
              return Promise.resolve();
            },
          },
        ],
      },
      rules: {
        given_name: { required: true, message: "請輸入名字" },
        family_name: { required: true, message: "請輸入姓氏" },
        display_name: { required: true, message: "請輸入顯示名稱" },
        email: { required: true, type: "email", message: "請輸入有效的電子郵件" },
      },
      validateMessages: {
        required: "${label} 是必填欄位",
        types: {
          email: "${label} 不是有效的電子郵件格式",
        },
      },
    };
  },
  methods: {
    formatDate(date, format = "YYYY-MM-DD") {
      if (!date) return "—";
      return dayjs(date).format(format);
    },

    setCroppedImageData(data) {
      this.avatarPreview = data.imageUrl;
      this.avatarData = data;
    },

    resetForm() {
      this.$inertia.visit(route("member.profile.edit", this.member.id), {
        replace: true,
      });
    },

    togglePasswordSection() {
      this.isPasswordSectionOpen = !this.isPasswordSectionOpen;
    },

    onSubmit() {
      this.loading = true;
      if (this.avatarData) {
        this.member.avatar = this.avatarData.blob;
      }
      this.member._method = "PATCH";
      this.$inertia.post(route("member.profile.update", this.member.id), this.member, {
        onSuccess: (page) => {
          this.loading = false;
          message.success("個人資料已更新");
          this.avatarPreview = null;
          this.avatarData = null;
        },
        onError: (err) => {
          this.loading = false;
          message.error("更新失敗，請稍後再試");
          console.error(err);
        },
      });
    },

    onFinish() {
      this.$refs.passwordFormRef.validate()
        .then(() => {
          this.password.id = this.member.id;
          this.$inertia.post(route('member.profile.changePassword'), this.password, {
            onSuccess: (page) => {
              message.success("密碼已更新");
              this.password = { old: '', new: '', confirm: '' };
              this.$refs.passwordFormRef.resetFields();
            },
            onError: (err) => {
              const errorMsg = err.response?.data?.message || "密碼更新失敗";
              message.error(errorMsg);
              if (errorMsg.includes('當前密碼') || errorMsg.includes('old')) {
                this.password.old = '';
              }
            },
          });
        })
        .catch(() => {
          message.warning("請檢查密碼欄位是否填寫正確");
        });
    },
  },
};
</script>

<style scoped>
/* 平滑过渡效果 */
.rounded-lg,
.rounded-xl,
.rounded-2xl {
  transition: all 0.2s ease;
}

/* 滑入滑出动画 - 更流畅的效果 */
.slide-fade-enter-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-leave-active {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-enter-from {
  transform: translateY(-10px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* 表单样式优化 */
:deep(.ant-form-item) {
  margin-bottom: 0;
}

:deep(.ant-form-item-label) {
  padding-bottom: 6px;
  font-weight: 500;
  color: #374151;
}

:deep(.ant-input),
:deep(.ant-input-password),
:deep(.ant-picker) {
  border-radius: 0.5rem;
  border-color: #e5e7eb;
  transition: all 0.2s;
}

:deep(.ant-input:hover),
:deep(.ant-input-password:hover),
:deep(.ant-picker:hover) {
  border-color: #818cf8;
}

:deep(.ant-input:focus),
:deep(.ant-input-password:focus),
:deep(.ant-picker-focused) {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

:deep(.ant-radio-button-wrapper) {
  border-radius: 0.5rem !important;
  border-color: #e5e7eb;
}

:deep(.ant-radio-button-wrapper-checked) {
  background-color: #6366f1 !important;
  border-color: #6366f1 !important;
}

:deep(.ant-btn-primary) {
  background-color: #6366f1;
  border-color: #6366f1;
}

:deep(.ant-btn-primary:hover) {
  background-color: #4f46e5;
  border-color: #4f46e5;
}

/* 旋转动画 */
.rotate-180 {
  transform: rotate(180deg);
}

/* 响应式调整 */
@media (max-width: 768px) {
  .lg\:w-80 {
    width: 100%;
  }
}
</style>