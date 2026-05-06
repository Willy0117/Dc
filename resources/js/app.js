import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createI18n } from 'vue-i18n';

import en from '../lang/en.json';
import ja from '../lang/ja.json';

const messages = {
    // 完全に純粋な JavaScript オブジェクトに変換
    en: JSON.parse(JSON.stringify(en)),
    ja: JSON.parse(JSON.stringify(ja))
};
//const messages = { en, ja };

// Laravel セッションなどからデフォルト言語を取得（なければ 'en'）
const defaultLocale = document.documentElement.lang || 'en';

const i18n = createI18n({
    legacy: false, // Composition API対応
    locale: defaultLocale,
    fallbackLocale: 'en',
    messages,
    escapeParameterHtml: true, 
    warnHtmlMessage: false,
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .mount(el);
    },
    progress: false,
});
