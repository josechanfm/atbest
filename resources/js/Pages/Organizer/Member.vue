<template>
  <OrganizerLayout title="Dashboard" :breadcrumb="breadcrumb">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ $t('view_member_profile') }}
        </h2>
        <!-- 可选的额外操作按钮 -->
        <button 
          v-if="member" 
          @click="editMember" 
          class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200"
        >
          編輯資料
        </button>
      </div>
    </template>

    <div class="container mx-auto px-4 py-6">
      <!-- 加载状态 -->
      <div v-if="!member" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- 会员资料卡片 -->
      <div v-else class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <!-- 资料头部 -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
          <h3 class="text-white text-lg font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            會員基本資料
          </h3>
        </div>

        <!-- 资料内容 - 使用网格布局优化 -->
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div 
              v-for="(value, key) in member" 
              :key="key"
              class="border-b border-gray-100 pb-3 last:border-b-0 md:last:border-b md:even:border-b-0 hover:bg-gray-50 transition-colors duration-150 px-2"
            >
              <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">
                {{ formatFieldLabel($t(key)) }}
              </div>
              <div class="text-gray-800 font-medium break-words">
                <!-- 特殊处理 organization 字段 -->
                <template v-if="key === 'organization' && value && typeof value === 'object'">
                  <span v-if="value.name_zh">{{ value.name_zh }}</span>
                  <span v-else-if="value.name">{{ value.name }}</span>
                  <span v-else class="text-gray-400 italic">未提供</span>
                </template>
                <!-- 一般字段处理 -->
                <template v-else>
                  <span v-if="value !== null && value !== ''">{{ value }}</span>
                  <span v-else class="text-gray-400 italic">未提供</span>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- 底部操作区 -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
          <button 
            @click="goBack" 
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200"
          >
            返回列表
          </button>
        </div>
      </div>

      <!-- 原有的 Modal 组件（保留但未使用） -->
      <div v-if="false" class="hidden">
        <!-- 保留原始 modal 结构以避免破坏引用，但实际不渲染 -->
      </div>
    </div>
  </OrganizerLayout>
</template>

<script>
import OrganizerLayout from "@/Layouts/OrganizerLayout.vue";
import { defineComponent } from "vue";

export default {
  components: {
    OrganizerLayout,
  },
  props: ["organization", "member"],
  data() {
    return {
      breadcrumb: [
        { label: "會員列表", url: route('organizer.members.index') },
        { label: "會員資料", url: null },
      ],
      // 保留原始属性以兼容可能的扩展
      modal: {
        isOpen: false,
        data: {},
        title: "Modal",
        mode: "",
      },
      columns: [
        { title: "姓名(中文)", dataIndex: "first_name" },
        { title: "姓名(外文)", dataIndex: "last_name" },
        { title: "別名", dataIndex: "gender" },
        { title: "手機", dataIndex: "dob" },
        { title: "狀態", dataIndex: "state" },
        { title: "操作", dataIndex: "operation", key: "operation" },
      ],
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
  methods: {
    // 辅助方法：格式化字段标签（将英文下划线转为中文空格显示）
    formatFieldLabel(label) {
      if (!label) return '';
      // 如果已经是中文就直接返回，否则尝试转换
      if (/[\u4e00-\u9fa5]/.test(label)) return label;
      return label
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    },
    
    // 返回上一页
    goBack() {
      this.$inertia.visit(route('organizer.members.index'));
    },
    
    // 编辑会员
    editMember() {
      if (this.member && this.member.id) {
        this.$inertia.visit(route('organizer.members.edit', this.member.id));
      } else {
        console.warn('无法编辑：会员资料缺失或ID无效');
      }
    },
    
    // 保留原有的方法以兼容可能的调用
    createRecord() {
      this.modal.data = {};
      this.modal.mode = "CREATE";
      this.modal.title = "新增問卷";
      this.modal.isOpen = true;
    },
    editRecord(record) {
      this.modal.data = { ...record };
      this.modal.mode = "EDIT";
      this.modal.title = "修改";
      this.modal.isOpen = true;
    },
    storeRecord() {
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.post("/admin/teachers/", this.modal.data, {
            onSuccess: (page) => {
              this.modal.data = {};
              this.modal.isOpen = false;
            },
            onError: (err) => {
              console.log(err);
            },
          });
        })
        .catch((err) => {
          console.log(err);
        });
    },
    updateRecord() {
      console.log(this.modal.data);
      this.$refs.modalRef
        .validateFields()
        .then(() => {
          this.$inertia.patch("/admin/teachers/" + this.modal.data.id, this.modal.data, {
            onSuccess: (page) => {
              this.modal.data = {};
              this.modal.isOpen = false;
              console.log(page);
            },
            onError: (error) => {
              console.log(error);
            },
          });
        })
        .catch((err) => {
          console.log("error", err);
        });
    },
    deleteRecord(recordId) {
      console.log(recordId);
      if (!confirm("Are you sure want to remove?")) return;
      this.$inertia.delete("/admin/teachers/" + recordId, {
        onSuccess: (page) => {
          console.log(page);
        },
        onError: (error) => {
          console.log(error);
        },
      });
    },
    createLogin(recordId) {
      console.log("create login" + recordId);
    },
  },
};
</script>

<style scoped>
/* 优雅的动画和过渡效果 */
.container {
  max-width: 1280px;
}

/* 响应式断点优化 */
@media (max-width: 768px) {
  .grid {
    gap: 1rem;
  }
}

/* 滚动条样式优化（可选） */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>