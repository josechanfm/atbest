<template>
  <div>
    <a-menu
      v-model:openKeys="openKeys"
      v-model:selectedKeys="selectedKeys"
      mode="inline"
      theme="light"
      :inline-collapsed="collapsed"
    >
      <a-menu-item key="1">
        <template #icon>
          <PieChartOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.dashboard')">
            {{ $t("organization_management") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="members">
        <template #icon>
          <TeamOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.members.index')">
            {{ $t("member") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="certificates">
        <template #icon>
          <FileProtectOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.certificates.index')">
            {{ $t("certificates") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="forms">
        <template #icon>
          <FormOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.forms.index')">
            {{ $t("forms") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="events">
        <template #icon>
          <CalendarOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.events.index')">
            {{ $t("events") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="articles">
        <template #icon>
          <CopyOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.articles.index')">
            {{ $t("articles") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="messages">
        <template #icon>
          <MailOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.messages.index')">
            {{ $t("messages") }}
          </inertia-link>
        </span>
      </a-menu-item>
      <a-menu-item key="emails">
        <template #icon>
          <MailOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.emails.index')">
            {{ $t("email.management") }}
          </inertia-link>
        </span>
      </a-menu-item>

      <a-menu-item key="10">
        <template #icon>
          <ProjectOutlined />
        </template>
        <span>
          <inertia-link :href="route('organizer.blogs.index')">
            {{ $t("blogs.management") }}
          </inertia-link>
        </span>
      </a-menu-item>

      <a-menu-item key="widget">
        <template #icon>
          <DesktopOutlined />
        </template>
        <span>
          <inertia-link :href="route('widget.admin.dashboard')">
            {{ $t("widget") }}
          </inertia-link>
        </span>
      </a-menu-item>
      
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
      </a-sub-menu>
    </a-menu>
  </div>
</template>
<script>
import { defineComponent, reactive, toRefs, watch, onMounted } from "vue";
import {
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  PieChartOutlined,
  TeamOutlined,
  FileProtectOutlined,
  FormOutlined,
  MergeCellsOutlined,
  CalendarOutlined,
  NotificationOutlined,
  MessageOutlined,
  CopyOutlined,
  ProjectOutlined,
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
    TeamOutlined,
    FileProtectOutlined,
    FormOutlined,
    MergeCellsOutlined,
    ProjectOutlined,
    CalendarOutlined,
    NotificationOutlined,
    MessageOutlined,
    CopyOutlined,
    MailOutlined,
    DesktopOutlined,
    InboxOutlined,
    AppstoreOutlined,
  },
  props: ["organization"],
    setup() {
    const state = reactive({
      collapsed: false,
      selectedKeys: ["1"],
      openKeys: [],
      preOpenKeys: ["sub1"],
    });
    onMounted(()=>{
      const r=route().current().split('.')
      if(r[1]){
        state.selectedKeys=[r[1]]
      }else{
        state.selectedKeys=["1"]
      }
      
      
    })
    watch(
      () => state.openKeys,
      (_val, oldVal) => {
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
});
</script>
