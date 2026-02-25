<template>
  <div>
    <a-menu v-model:openKeys="openKeys" v-model:selectedKeys="selectedKeys" mode="inline" theme="light"
      :inline-collapsed="collapsed">
      <a-menu-item key="dashboard">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('admin.dashboard')">
            {{ $t("dashboard") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="organizations.index">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('admin.organizations.index')">
            {{ $t("organizations") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="features.index">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('admin.features.index')">
            {{ $t("feature") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="articles.index">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('admin.articles.index')">
            {{ $t("articles") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="members.index">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('admin.members.index')">
            {{ $t("members") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="users.index">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('admin.users.index')">
            {{ $t("user") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="configs.index">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('admin.configs.index')">
            {{ $t("configs") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="issues.index">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('admin.issues.index')">
            {{ $t("issues") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="widget">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('widget.admin.dashboard')">
            {{ $t("widget") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <!-- <a-sub-menu key="sub1">
        <template #icon>
          <MailOutlined />
        </template>
        <template #title>Navigation One</template>
        <a-menu-item key="101">Option 5</a-menu-item>
        <a-menu-item key="122">Option 6</a-menu-item>
        <a-menu-item key="103">Option 7</a-menu-item>
        <a-menu-item key="104">Option 8</a-menu-item>
      </a-sub-menu>
      <a-sub-menu key="sub2">
        <template #icon>
          <AppstoreOutlined />
        </template>
        <template #title>Navigation Two</template>
        <a-menu-item key="111">Option 9</a-menu-item>
        <a-menu-item key="112">Option 10</a-menu-item>
        <a-sub-menu key="sub3" title="Submenu">
          <a-menu-item key="1121">Option 11</a-menu-item>
          <a-menu-item key="1122">Option 12</a-menu-item>
        </a-sub-menu>
      </a-sub-menu> -->
    </a-menu>
  </div>
</template>
<script>
import { defineComponent, reactive, toRefs, watch } from "vue";
import {
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  PieChartOutlined,
  MailOutlined,
  DesktopOutlined,
  InboxOutlined,
  AppstoreOutlined,
} from "@ant-design/icons-vue";
export default defineComponent({
  components: {
    MenuFoldOutlined,
    MenuUnfoldOutlined,
    PieChartOutlined,
    MailOutlined,
    DesktopOutlined,
    InboxOutlined,
    AppstoreOutlined,
  },
  props: ["organization"],
  setup() {
    const state = reactive({
      collapsed: false,
      selectedKeys: [],
      openKeys: ["sub1"],
      preOpenKeys: ["sub1"],
    });
    watch(() => state.openKeys, (_val, oldVal) => {
      state.preOpenKeys = oldVal;
    }
    );
    const toggleCollapsed = () => {
      state.collapsed = !state.collapsed;
      state.openKeys = state.collapsed ? [] : state.preOpenKeys;
      console.log(state.collapsed.value);
    };
    return {
      ...toRefs(state),
      toggleCollapsed,
    };
  },
  mounted() {
    console.log(route().current().split(".").slice(1).join("."));
    this.selectedKeys.push(route().current().split(".").slice(1).join("."));
  },
});
</script>
