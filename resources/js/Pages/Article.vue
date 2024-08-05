<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';


export default {
    components: {
        DefaultLayout,
    },
    props:['article'],
    data() {
        return{
            pageHeader:'Article',
        }
    },
    created() {
        axios.get(route("api.config.item", { key: 'article_categories' })).then((resp) => {
            const articleCategory=resp.data.find(d=>d.value==this.article.category_code)
            this.pageHeader=articleCategory?articleCategoryp.label:'Article'
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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ pageHeader }}
            </h2>
        </template>

        <div class="container py-10 px-2 sm:px-6 lg:px-8">
            <h2 class="font-bold text-2xl text-gray-800">
                {{ article.title}}</h2>
            <div v-html="article.content"/>
            <div class="text-center pt-10">
            <a v-if="article.url" :href="article.url" target="_blank">Link</a>
            </div>
        </div>
        
    </DefaultLayout>

    
</template>

<style >
.ql-align-right {
	text-align: right;
}
.ql-align-center {
	text-align: center;
}
.ql-align-left {
	text-align: left;
}
</style>