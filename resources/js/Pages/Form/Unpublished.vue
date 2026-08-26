<template>
    <div class="unpublished-container">
      <div class="unpublished-card">
        <h1 class="title">🔒 {{ title }}</h1>
        <p class="message" v-html="message"></p>
        <p class="sub-message" v-if="subMessage">{{ subMessage }}</p>
        <div class="actions">
          <a-button type="primary" @click="goBack">
            返回列表
          </a-button>
        </div>
      </div>
    </div>
</template>

<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';

export default {
  name: 'Unpublished',
  components: { DefaultLayout },
  layout: DefaultLayout,
  props: {
    reason: {
      type: String,
      default: 'unpublished',
      validator: (val) => ['unpublished', 'login_required', 'member_only'].includes(val),
    },
  },
  computed: {
    title() {
      switch (this.reason) {
        case 'login_required':
          return '需要登入';
        case 'member_only':
          return '僅限會員填寫';
        default:
          return '表單尚未發佈';
      }
    },
    message() {
      switch (this.reason) {
        case 'login_required':
          return '此表單需要<strong>登入</strong>後才能填寫。';
        case 'member_only':
          return '此表單僅限<strong>所屬組織的會員</strong>填寫。';
        default:
          return '此表單目前 <strong>尚未發佈</strong> 或已 <strong>停止收集</strong> 回應。';
      }
    },
    subMessage() {
      switch (this.reason) {
        case 'login_required':
          return '請先登入您的帳號，再重新嘗試。';
        case 'member_only':
          return '請確認您是否已登入並屬於正確的組織。';
        default:
          return '請聯絡表單管理員以獲取更多資訊。';
      }
    },
  },
  methods: {
    goBack() {
      Inertia.visit('/forms');
    },
  },
};
</script>

<style scoped>
/* Same styles as before – unchanged */
.unpublished-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f7fafc;
  padding: 20px;
}
.unpublished-card {
  max-width: 480px;
  width: 100%;
  background: white;
  border-radius: 16px;
  padding: 40px 30px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
  text-align: center;
}
.title {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 20px;
  color: #2d3748;
}
.message {
  font-size: 18px;
  color: #4a5568;
  margin-bottom: 8px;
}
.message strong {
  color: #e53e3e;
}
.sub-message {
  font-size: 16px;
  color: #718096;
  margin-top: 8px;
  margin-bottom: 30px;
}
.actions {
  display: flex;
  justify-content: center;
  gap: 12px;
}
</style>