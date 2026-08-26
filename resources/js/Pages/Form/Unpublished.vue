<template>
  <!-- Use a public layout if available (e.g., DefaultLayout) -->
  <DefaultLayout v-if="layout">
    <div class="unpublished-container">
      <div class="unpublished-card">
        <h1 class="title">🔒 表單尚未發佈</h1>
        <p class="message">
          此表單目前 <strong>尚未發佈</strong> 或已 <strong>停止收集</strong> 回應。
        </p>
        <p class="sub-message">
          請聯絡表單管理員以獲取更多資訊。
        </p>
        <div class="actions">
          <a-button type="primary" @click="goBack">
            返回
          </a-button>
        </div>
      </div>
    </div>
  </DefaultLayout>

  <!-- Fallback: standalone (no layout) -->
  <div v-else class="unpublished-container standalone">
    <div class="unpublished-card">
      <h1 class="title">🔒 表單尚未發佈</h1>
      <p class="message">
        此表單目前 <strong>尚未發佈</strong> 或已 <strong>停止收集</strong> 回應。
      </p>
      <p class="sub-message">
        請聯絡表單管理員以獲取更多資訊。
      </p>
      <div class="actions">
        <a-button type="primary" :href="'/forms'">
          返回
        </a-button>
      </div>
    </div>
  </div>
</template>

<script>
// If you have a DefaultLayout, import it.
// Otherwise, set layout = null to render standalone.
import DefaultLayout from '@/Layouts/DefaultLayout.vue';

export default {
  name: 'Unpublished',
  components: {
    DefaultLayout,
  },
  // If you want to use the layout, keep this.
  // If you don't have DefaultLayout, set to null.
  layout: DefaultLayout,
  methods: {
    goBack() {
      // Go back to previous page or home
      window.history.back();
    },
  },
};
</script>

<style scoped>
.unpublished-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f7fafc;
  padding: 20px;
}

/* When using a layout, ensure the container is centered inside it */
.unpublished-container.standalone {
  /* Already full viewport */
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