<template>
  <footer class="border-t border-gray-200 mt-12">
    <div class="max-w-3xl mx-auto px-6 py-6">

      <!-- アコーディオン -->
      <button
        type="button"
        class="w-full flex items-center justify-between text-xs text-gray-400 hover:text-gray-600 transition-colors"
        @click="open = !open"
      >
        <span>特定商取引に関する法律に基づく表示</span>
        <ChevronDown
          class="w-4 h-4 transition-transform duration-200"
          :class="{ 'rotate-180': open }"
        />
      </button>

      <!-- 展開時 -->
      <div v-if="open" class="mt-4">
        <table class="w-full text-xs text-gray-600 border border-gray-200 rounded-lg overflow-hidden">
          <tbody class="divide-y divide-gray-200">
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 w-1/3 bg-gray-50">販売会社</th>
              <td class="px-4 py-2.5">株式会社Alivio JAPAN</td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">所在地</th>
              <td class="px-4 py-2.5">〒106-0047 東京都港区南麻布3-20-1 Daiwa麻布テラス5階</td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">電話番号</th>
              <td class="px-4 py-2.5">03-XXXX-XXXX（平日 10:00〜17:00）</td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">連絡先</th>
              <td class="px-4 py-2.5">
                <a href="mailto:info@alivio-japan.co.jp" class="text-blue-500 hover:underline">
                  info@alivio-japan.co.jp
                </a>
              </td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">代表者または業務責任者</th>
              <td class="px-4 py-2.5">代表取締役　奧山 新</td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">商品の説明</th>
              <td class="px-4 py-2.5">動注治療ライセンス（年間ライセンス）</td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">販売価格（税込）</th>
              <td class="px-4 py-2.5">
                132,000円/年（税抜120,000円）〜<br />
                <span class="text-gray-400">※4名目以降は11,000円/年（税込）追加</span>
              </td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">販売代金以外の必要料金</th>
              <td class="px-4 py-2.5">上記以外に特になし</td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">支払方法</th>
              <td class="px-4 py-2.5">クレジットカード、請求書による銀行振込</td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">支払時期</th>
              <td class="px-4 py-2.5">
                クレジットカード：各カード会社引き落とし日<br />
                銀行振込：請求書発行後、指定期日まで
              </td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">引渡し時期</th>
              <td class="px-4 py-2.5">契約締結・電子署名完了後、速やかに提供</td>
            </tr>
            <tr class="odd:bg-white even:bg-gray-50">
              <th class="px-4 py-2.5 text-left font-semibold text-gray-700 bg-gray-50">返品・キャンセル</th>
              <td class="px-4 py-2.5">契約締結後のキャンセル・返金は原則承っておりません。</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="mt-4 text-xs text-gray-400 text-center">
        &copy; {{ year }} Alivio Japan Co., Ltd. All rights reserved.
      </p>

    </div>
  </footer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

const open = ref(false)
const year = computed(() => new Date().getFullYear())
</script>