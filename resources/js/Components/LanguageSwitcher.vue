<template>
   <Dropdown align="right" width="20">
    <template #trigger>
    <a-button type="text" class="text-lg flex justify-between items-center">
        <GlobalOutlined />
        <span v-if="page.props.lang" class="text-sm">{{$t(page.props.lang)}}</span>
    </a-button>
    </template>
    <template #content>
        <div class="w-20 text-center">
            <DropdownLink :href="route('language', 'zh')">
                {{ $t("chinese") }}
            </DropdownLink>
            <DropdownLink :href="route('language', 'en')">
                {{ $t("english") }}
            </DropdownLink>
        </div>
    </template>
    </Dropdown>
</template>

<script setup>
import { ref } from 'vue'
import { GlobalOutlined } from '@ant-design/icons-vue'
import Dropdown from "@/Components/Dropdown.vue";
import { usePage } from '@inertiajs/vue3';
import DropdownLink from "@/Components/DropdownLink.vue";
import { getActiveLanguage,loadLanguageAsync } from "laravel-vue-i18n";

const locale = getActiveLanguage()
const locales = ref([{code:'zh' , name:'zh'},{code:'en',name:'en'}])
const page = usePage();
loadLanguageAsync(page.props.lang);
// 获取可用语言列表

</script>