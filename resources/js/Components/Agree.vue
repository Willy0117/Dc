<template>
    <div>
        <!-- 誓約書本文 -->
        <div
            ref="termsBox"
            @scroll="onScroll"
            class="mt-6 p-4 border rounded bg-gray-50 max-h-96 overflow-y-scroll text-sm leading-relaxed"
        >
            <h2 class="font-bold text-lg mb-2">誓約書</h2>

            <p>書式 外技第Ⅰ－３号(１)　令和７年１０月１７日（改定）</p>
            <p>一般社団法人全国中小建設工事業団体連合会 会長 殿</p>

            <p class="mt-2">
                この度、貴会の外国人受入支援を利用するにあたり、以下の事項を遵守履行することを誓約いたします。
            </p>

            <p class="mt-4">
                １．私【当社】は、現在又は将来にわたって、以下の①から⑦までに掲げる者のいずれにも該当しないことを表明、関係しないことを確約いたします。
            </p>
            <ul class="border border-gray-300 rounded p-4 space-y-2 bg-gray-50">
            <li class="font-medium">① 暴力団</li>
            <li class="font-medium">② 暴力団員</li>
            <li class="font-medium">③ 暴力団準構成員</li>
            <li class="font-medium">④ 暴力団関係企業</li>
            <li class="font-medium">⑤ 総会屋等、社会運動等標榜ゴロ</li>
            <li class="font-medium">⑥ 暴力団員でなくなってから5年を経過していない者</li>
            <li class="font-medium">⑦ その他①から⑥までに準ずる者</li>
            </ul>

            <p class="mt-4">
                ２．一般社団法人建設技能人材機構の正会員（専門工事業団体等）に加入しておりません。
                虚偽の申込みが判明した時は貴会を退会することに異議ありません。
            </p>

            <p class="mt-2">
                ３．「特定技能外国人の適切かつ円滑な受入れの実現に向けた建設業界共通行動規範」を遵守履行すること。
            </p>

            <p class="mt-2">
                ４．１号特定技能外国人と特定技能雇用契約を締結する場合、
                建設特定技能受入計画について国土交通大臣の認定を受けること。
            </p>

            <p class="mt-2">
                ５．建設特定技能受入計画を適正に実施し、国土交通大臣又は適正就労監理機関により確認を受けること。
            </p>

            <p class="mt-2">
                ６．国土交通省が行う調査又は指導に対し必要な協力を行うこと。
            </p>

            <p class="mt-2">
                ７．会費及び１号特定技能外国人の受入負担金（12,500円／月）について、
                規約等に基づき退会まで誠実に納付すること。
            </p>

            <p class="mt-2">
                ８．会費を1ヶ月超滞納、または受入負担金を３ヶ月滞納した場合、
                自動退会となることに異議ありません。
                再入会には理事会の承認が必要であることを承知します。
            </p>

            <p class="mt-2">
                ９．年度内に退会する場合は退会届を提出し、会費返金は求めません。
            </p>

            <p class="mt-2">
                10．退会した際には、就労管理システムの計画取消申請を速やかに行います。
            </p>
        </div>
        <!-- 注意文言 -->
        <p v-if="!scrolledToBottom" class="text-xs text-red-500 mt-2">
            最後までスクロールするとチェックできます
        </p>

        <!-- チェックボックス -->
        <div class="mt-4 flex items-center">
            <input
                type="checkbox"
                :checked="modelValue"
                :disabled="!scrolledToBottom"
                @change="$emit('update:modelValue', $event.target.checked)"
                id="agree_terms"
                class="mr-2"
            />
            <label for="agree_terms" class="text-sm">
                上記の誓約事項を読み、内容を確認しました。
            </label>
        </div>

        <div v-if="error" class="text-red-600 text-sm mt-1">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
    modelValue: Boolean,
    error: String
});

defineEmits(["update:modelValue"])

const termsBox = ref(null)
const scrolledToBottom = ref(false)

const onScroll = () => {
    const el = termsBox.value
    if (!el) return

    // 最下部判定（誤差対策で -5）
    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 5) {
        scrolledToBottom.value = true
    }
}
</script>
