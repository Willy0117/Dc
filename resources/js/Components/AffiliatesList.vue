<template>
  <div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">加盟団体</h2>

    <!-- トップ：建設技能人材機構 -->
    <div class="mb-6">
      <h3 class="font-semibold mb-2">● (一社）建設技能人材機構</h3>
      <div class="grid grid-cols-1 text-sm leading-relaxed">
        <div v-for="item in affiliates1" :key="item" class="flex items-start">
          <span class="mr-1">■</span>
          <span>{{ item }}</span>
        </div>
      </div>
    </div>

    <!-- 専門工事業団体 -->
    <div class="mb-6">
      <h3 class="font-semibold mb-2">● 専門工事業団体</h3>
      <div class="grid grid-cols-2 gap-2 text-sm leading-relaxed">
        <div v-for="item in affiliates2" :key="item" class="flex items-start">
          <span class="mr-1">■</span>
          <span>{{ item }}</span>
        </div>
      </div>
    </div>

    <!-- 元請けゼネコン他 -->
    <div class="mb-6">
      <h3 class="font-semibold mb-2">● 元請けゼネコン他</h3>
      <div class="grid grid-cols-2 gap-2 text-sm leading-relaxed">
        <div v-for="item in affiliates3" :key="item" class="flex items-start">
          <span class="mr-1">■</span>
          <span>{{ item }}</span>
        </div>
      </div>
    </div>

    <!-- チェック選択 -->
    <div class="mt-4 border-t pt-4">
        <p class="font-semibold mb-2">※ 下記より選択してください（必須）</p>

        <div class="space-y-2">
        <label class="flex items-start space-x-2">
            <input
            type="radio"
            :value="'yes'"
            :checked="modelValue === 'yes'"
            @change="$emit('update:modelValue', 'yes')"
            class="mt-1"
            />
            <span>
            上記のいずれかの団体に加入している<br>
            <small class="text-red-600">※加入している場合は入会できません。</small>
            </span>
        </label>

        <label class="flex items-start space-x-2">
            <input
            type="radio"
            :value="'no'"
            :checked="modelValue === 'no'"
            @change="$emit('update:modelValue', 'no')"
            class="mt-1"
            />
            <span>上記のいずれの団体にも加入していない</span>
        </label>
        </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  modelValue: String, // v-model で受け取る
});
const emit = defineEmits(['update:modelValue']);
//
// ① トップ：建設技能人材機構
//
const affiliates1 = [
  "（一社）建設技能人材機構"
];

//
// ② 専門工事業団体
//
const affiliates2 = [
  "（一社）JBN・全国工務店協会",
  "（一社）情報通信エンジニアリング協会",
  "全国圧接業協同組合連合会",
  "全国仮設安全事業協同組合",
  "全国管工事業協同組合連合会",
  "（一社）全国基礎工事業団体連合会",
  "（一社）全国建設室内工事業協会",
  "全国建設労働組合総連合",
  "（一社）全国建築測量協会",
  "（一社）全国コンクリート圧送事業団体連合会",
  "全国サイディング事業協同組合連合会",
  "（一社）全国住宅産業地域活性化協議会",
  "（一社）全国タイル業協会",
  "（一社）全国ダクト工業団体連合会",
  "（公社）全国鉄筋工事業協会",
  "（一社）全国特定法面保護協会",
  "（一社）全国防水工事業協会",
  "（一社）全日本瓦工事業連盟",
  "（一社）全日本漁港建設協会",
  "（一社）日本ウレタン断熱協会",
  "日本外壁仕上業協同組合連合会",
  "（一社）日本型枠工事業協会",
  "（一社）日本機械土工協会",
  "（一社）日本基礎建設協会",
  "（一社）日本金属屋根協会",
  "（一社）日本空調衛生工事業協会",
  "日本建設インテリア事業協同組合連合会",
  "（一社）日本建設あと施工アンカー協会",
  "（一社）日本建設機械レンタル協会",
  "（一社）日本建設軀体工事業団体連合会",
  "（一社）日本建築板金協会",
  "日本港湾空港建設協会連合会",
  "（一社）日本在来工法住宅協会",
  "（一社）日本左官業組合連合会",
  "日本室内装飾事業協同組合連合会",
  "日本住宅パネル工業協同組合",
  "（一社）日本パルスレーザー振興協会",
  "（公社）日本推進技術協会",
  "（一社）日本造園組合連合会",
  "（一社）日本タイル煉瓦工事工業会",
  "（一社）日本築炉人材育成協会",
  "（一社）日本ツーバイフォー建築協会",
  "（一社）日本電設工業協会",
  "（一社）日本道路建設業協会",
  "（一社）日本塗装工業会",
  "（一社）日本鳶工業連合会",
  "（一社）日本配管工事業団体連合会",
  "（一社）日本発破・破砕協会",
  "（一社）日本保温保冷工業協会",
  "（一社）日本冷凍空調設備工業連合会",
  "（一社）プレストレスト・コンクリート建設業協会",
  "（一社）プレストレスト・コンクリート工事業協会",
];

//
// ③ 元請けゼネコン他
//
const affiliates3 = [
  "（一社）SIEN",
  "（一社）全国建設業協会",
  "（一社）全国中小建設業協会",
  "（一社）日本建設業連合会",
  "（一社）マンション計画修繕施工協会",
];
</script>

