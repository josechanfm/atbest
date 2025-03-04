import './bootstrap';
import '../css/app.css';
//import '../css/admin.css'

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import Antd from 'ant-design-vue';
import { i18nVue } from 'laravel-vue-i18n'
// import 'ant-design-vue/dist/reset.css';

import  RolePermission  from './Directives/RolePermission.js';


// import { createI18n } from 'vue-i18n';
// import messages from '@/lang/messages.js'
// const i18n = createI18n({
//     locale: 'nl',
//     fallbackLocale: 'en',
//     messages,
// });

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(Antd)
            .use(RolePermission)
            .use(i18nVue, {
                resolve: async lang => {
                    const langs = import.meta.glob('../../lang/*.json');
                    return await langs[`../../lang/${lang}.json`]();

                },
            })
            
            .component('inertia-head', Head)
            .component('inertia-link', Link)
            .mount(el);
    },
});

