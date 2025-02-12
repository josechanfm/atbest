<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';

export default {
    components: {
        DefaultLayout,
    },
    props: ['article'],
    data() {
        return {
            pageHeader: 'Article',
        }
    },
    created() {
        axios.get(route("api.config.item", {
            key: 'article_categories'
        })).then((resp) => {
            const articleCategory = resp.data.find(d => d.value == this.article.category_code)
            this.pageHeader = articleCategory ? articleCategoryp.label : 'Article'
            // this.articleCategories = resp.data.reduce((acc, obj) => {
            //     acc[obj.value] = obj;
            //     return acc;
            // }, {});
        })
    },
}
</script>

<template>
<DefaultLayout title="Dashboard">
    <template #header>
        <div class="font-semibold text-xl text-gray-800 leading-tight">
            {{ pageHeader }}
        </div>
    </template>
    <div class="flex flex-col gap-10">

        <div class="ml-5 lg:ml-20 lg:w-4/5 py-5">
            <h2 class="font-bold text-2xl text-gray-800">
                {{ article.title }}
            </h2>
            <img :src="article.banner_url" height="200" />
            <div v-html="article.content" class="article" />
            <div class="text-center pt-10">
                <a v-if="article.url" :href="article.url" target="_blank">Link</a>
            </div>
        </div>
    </div>
</DefaultLayout>
</template>

<style>
.ql-align-right {
    text-align: right;
}

.ql-align-center {
    text-align: center;
}

.ql-align-left {
    text-align: left;
}
.article img{
    width:100%;
}
</style>
