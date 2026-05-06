<script setup>
import { useI18n } from 'vue-i18n'; // ★ useI18n() をインポート ★
import { Head, router } from '@inertiajs/vue3'; // router もインポートして言語切り替えをシンプルに

// ★ useI18n() を呼び出し ★
const { t, locale } = useI18n();

// デバッグ用に現在の locale をコンソールに出力
console.log('Current locale in TestI18n.vue:', locale.value);

// 言語を切り替える関数
const switchLanguage = (newLocale) => {
    locale.value = newLocale; // vue-i18n の locale を更新
    // Inertia でページの再ロードをトリガーし、Laravel側で言語セッションを更新
    router.get(window.location.href, { locale: newLocale }, { preserveState: false, preserveScroll: true });
};

</script>

<template>
    <Head title="Test i18n" />

    <div style="padding: 20px; font-family: sans-serif;">
        <h1>Vue I18n Test Page</h1>
        
        <p>Translated text (Dashboard): <strong>{{ t('Dashboard') }}</strong></p>
        <p>Current Locale: <strong>{{ locale }}</strong></p>

        <button @click="() => switchLanguage(locale === 'en' ? 'ja' : 'en')" 
                style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Toggle Locale ({{ locale === 'en' ? '日本語' : 'English' }})
        </button>

        <p style="margin-top: 20px; color: gray;">
            このページがエラーなく表示され、ボタンクリックでロケールが切り替われば、
            vue-i18n の基本的な設定と使い方は正しく機能しています。
        </p>
    </div>
</template>