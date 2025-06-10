<template>
   <Dropdown align="right" width="20">
        <template #trigger>
            <a type="text" class="flex gap-2 justify-between items-center text-white text-base hover:text-gray-100 bg-transparent border-0 cursor-pointer" >
                <GlobalOutlined />
                <span v-if="page.props.lang" class="">{{$t(page.props.lang)}}</span>
            </a>
        </template>
        <template #content>
            <div class="w-20 text-center ">
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