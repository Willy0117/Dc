<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle,
} from '@/components/ui/dialog'
import ProductApplyDialog from '@/Components/ProductApplyDialog.vue'

const props = defineProps({
  videoSets: Array, // [{ id, name, theme, overview, category, price_jpy, lecture_count, total_minutes, lectures: [...] }]
})

// カテゴリごとにグループ化（デザイン案の「医療安全の基本的知識」「安全管理体制の構築」の見出し用）
const groupedSets = computed(() => {
  const groups = []
  for (const set of props.videoSets) {
    let group = groups.find(g => g.category === set.category)
    if (!group) {
      group = { category: set.category, sets: [] }
      groups.push(group)
    }
    group.sets.push(set)
  }
  return groups
})

// 「詳細を見る」→ 該当セクションまでスムーズスクロール
function scrollToSet(setId) {
  const el = document.getElementById(`set-${setId}`)
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

// 申込ダイアログの状態（Index.vueが状態を持ち、コンポーネントに渡す）
const dialogOpen = ref(false)
const selectedSet = ref(null)

function openApplyDialog(set) {
  selectedSet.value = set
  dialogOpen.value = true
}

// 特定商取引法に基づく表記のポップアップ状態
const legalDialogOpen = ref(false)
</script>

<template>
  <Head title="オンデマンドセミナー申込" />

  <div class="min-h-screen bg-gradient-to-b from-sky-50 to-white">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10 space-y-10 md:space-y-14">

      <!-- ヒーロー -->
      <section class="text-center bg-white border rounded-2xl shadow-sm p-6 md:p-12 space-y-4">
        <img :src="'/images/logo.png'" alt="一般社団法人医療の質・安全学会 ロゴ" class="h-16 md:h-20 mx-auto mb-2">
        <Badge class="mx-auto">これから医療安全管理に携わる方へ</Badge>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-sky-900 leading-snug md:leading-relaxed">
          これから医療安全管理に携わる方のための<br class="hidden md:block">
          オンデマンドセミナー
        </h1>
        <p class="text-slate-600 max-w-3xl mx-auto text-sm md:text-base">
          医療安全管理に必要な基本的知識を、テーマ別に学べるオンデマンドセミナーです。<br>
          <br>
          医療安全の基礎知識、安全管理体制の構築をテーマとした講義を各2セットご用意しています。<br>
          各セットは配信期間内であれば繰り返し視聴可能です。<br>

        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 pt-4 md:pt-6 max-w-4xl mx-auto">
          <div class="bg-white border rounded-xl p-3 md:p-4">
            <p class="text-sky-700 font-bold text-sm md:text-base">各セット 9,900円</p>
            <p class="text-xs md:text-sm text-slate-500">税込価格</p>
          </div>
          <div class="bg-white border rounded-xl p-3 md:p-4">
            <p class="text-sky-700 font-bold text-sm md:text-base">学会員以外も受講可能</p>
            <p class="text-xs md:text-sm text-slate-500">どなたでも申込可能</p>
          </div>
          <div class="bg-white border rounded-xl p-3 md:p-4">
            <p class="text-sky-700 font-bold text-sm md:text-base">受講証明書を発行</p>
          </div>
          <div class="bg-white border rounded-xl p-3 md:p-4">
            <p class="text-sky-700 font-bold text-sm md:text-base">繰り返し視聴可能</p>
            <p class="text-xs md:text-sm text-slate-500">配信期間内のみ</p>
          </div>
        </div>
      </section>

      <!-- 受講セットを選択してください（簡易サマリー、クリックで下にスクロール） -->
      <!-- section class="space-y-4 md:space-y-6">
        <h2 class="text-lg md:text-2xl font-bold text-sky-900 border-l-4 border-emerald-400 pl-3 md:pl-4">
          受講セットを選択してください
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <button
            v-for="set in videoSets"
            :key="set.id"
            type="button"
            @click="scrollToSet(set.id)"
            class="text-left bg-white border rounded-2xl p-4 md:p-5 hover:shadow-md transition-shadow"
          >
            <Badge class="w-fit mb-2">{{ set.name }}</Badge>
            <p class="font-bold text-sky-900 text-sm md:text-base mb-1">{{ set.category }}</p>
            <p class="text-xs md:text-sm text-slate-500">{{ set.theme }}</p>
            <p class="text-xs md:text-sm text-sky-600 font-semibold mt-3">詳細を見る ↓</p>
          </button>
        </div>
      </section -->

      <!-- お申込みの流れ -->
      <section class="space-y-4 md:space-y-6">
        <h2 class="text-lg md:text-2xl font-bold text-sky-900 border-l-4 border-emerald-400 pl-3 md:pl-4">
          ご利用の流れ
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 md:gap-5">
          <div class="bg-white border rounded-2xl p-5 md:p-6">
            <div class="w-9 h-9 rounded-full bg-emerald-400 text-white font-bold flex items-center justify-center mb-3">1</div>
            <h3 class="font-bold text-sky-900 mb-1 text-base md:text-lg">セット選択</h3>
            <p class="text-sm text-slate-600">ご希望のセット（A～Ｄ）を選択します。</p>
          </div>
          <div class="bg-white border rounded-2xl p-5 md:p-6">
            <div class="w-9 h-9 rounded-full bg-emerald-400 text-white font-bold flex items-center justify-center mb-3">2</div>
            <h3 class="font-bold text-sky-900 mb-1 text-base md:text-lg">受講者情報入力</h3>
            <p class="text-sm text-slate-600">氏名、所属先、TEL、E-mailを入力します。</p>
          </div>
          <div class="bg-white border rounded-2xl p-5 md:p-6">
            <div class="w-9 h-9 rounded-full bg-emerald-400 text-white font-bold flex items-center justify-center mb-3">3</div>
            <h3 class="font-bold text-sky-900 mb-1 text-base md:text-lg">クレジットカード決済</h3>
            <p class="text-sm text-slate-600">申込内容をご確認のうえ、クレジットカードで決済を行います。領収書はご登録のメールアドレス宛にお送りいたします。</p>
          </div>
          <div class="bg-white border rounded-2xl p-5 md:p-6">
            <div class="w-9 h-9 rounded-full bg-emerald-400 text-white font-bold flex items-center justify-center mb-3">4</div>
            <h3 class="font-bold text-sky-900 mb-1 text-base md:text-lg">動画を視聴</h3>
            <p class="text-sm text-slate-600">決済完了画面に表示される「視聴サイトへすすむ」ボタンから動画をご視聴いただけます。また、ご登録のメールアドレス宛にも視聴サイトのURLをお送りしますので、メールからもアクセスいただけます。</p>
          </div>
          <div class="bg-white border rounded-2xl p-5 md:p-6">
            <div class="w-9 h-9 rounded-full bg-emerald-400 text-white font-bold flex items-center justify-center mb-3">5</div>
            <h3 class="font-bold text-sky-900 mb-1 text-base md:text-lg">受講証明書を発行</h3>
            <p class="text-sm text-slate-600">動画をすべてご視聴のうえ、各動画の確認テスト（2問）に合格すると、受講証明書をダウンロードいただけます。また、ご登録のメールアドレス宛にも受講証明書のダウンロードURLをお送りしますので、メールからもダウンロードいただけます。受講証明書は期限内にダウンロードをお願いします。<br>ダウンロード期限：2027年1月31日</p>
          </div>
        </div>
      </section>

      <!-- セット詳細（カテゴリごとにグループ表示、ページ内スクロール先） -->
      <section
        v-for="group in groupedSets"
        :key="group.category"
        class="space-y-4 md:space-y-6"
      >
        <h2 class="text-lg md:text-2xl font-bold text-sky-900 border-l-4 border-emerald-400 pl-3 md:pl-4">
          {{ group.category }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
          <div
            v-for="set in group.sets"
            :key="set.id"
            :id="`set-${set.id}`"
            class="scroll-mt-20 bg-white border rounded-2xl p-5 md:p-6 flex flex-col"
          >
            <Badge class="w-fit mb-2">セット{{ set.name }}</Badge>
            <h3 class="text-lg font-bold text-sky-900">{{ set.category }}</h3>
            <p class="text-sm text-slate-500 font-medium mb-1">{{ set.theme }}</p>
            <p v-if="set.overview" class="text-sm text-slate-600 leading-relaxed mb-3">{{ set.overview }}</p>

            <ul class="space-y-2 text-sm text-slate-700 mb-4">
              <li v-for="(lecture, i) in set.lectures" :key="i" class="border-b border-slate-100 pb-2 last:border-b-0">
                <span class="inline-block text-xs text-sky-700 font-semibold mr-1">{{ lecture.duration_minutes }}分</span>
                {{ lecture.title }}
                <span class="block text-xs text-slate-500 mt-0.5">{{ lecture.speaker_name }}</span>
              </li>
            </ul>

            <div class="mt-auto space-y-3">
              <div class="bg-sky-50 text-sky-800 rounded-lg px-3 py-2 text-sm font-semibold">
                全{{ set.lecture_count }}講義 / 合計{{ set.total_minutes }}分 / ¥{{ set.price_jpy.toLocaleString() }}
              </div>
              <Button
                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white"
                @click="openApplyDialog(set)"
              >
                このセットを申し込む
              </Button>
            </div>
          </div>
        </div>
      </section>



      <!-- 注意書き -->
      <section class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 md:p-5 text-xs md:text-sm font-semibold">
        【ご注意】
        <ul>
          <li>本研修を受講しても、診療報酬「医療安全対策加算」の施設基準に必要な医療安全管理者養成研修を受講したことにはなりません。また、同研修受講の一部に充てることもできません。</li>
          <li>動画コンテンツという商品の性質上、決済完了後のお客様都合によるキャンセル・返品・返金はお受けしておりません。</li>
          <li>動画の録画、録音、転載は禁止しております。</li>
        </ul>
      </section>


      <section class="text-center text-xs md:text-sm text-slate-500 pt-4 pb-2 border-t border-slate-200 space-y-2">
               <button
          type="button"
          class="underline text-sky-700 hover:text-sky-900"
          @click="legalDialogOpen = true"
        >
          特定商取引法に基づく表記
        </button>
      </section>

      <!-- フッター -->

      <footer class="text-center text-xs md:text-sm text-slate-500 pt-4 pb-2 border-t border-slate-200 space-y-2">
        <p>一般社団法人医療の質・安全学会 事務局</p>
        <p>〒113-0033 東京都文京区本郷2-29-1 渡辺ビル201</p>
      </footer>

    </div>

    <!-- 特定商取引法に基づく表記（ポップアップ） -->
    <Dialog v-model:open="legalDialogOpen">
      <DialogContent class="max-w-lg max-h-[80vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>特定商取引法に基づく表記</DialogTitle>
        </DialogHeader>

        <table class="w-full text-sm text-slate-700">
          <tbody>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 w-36 font-medium text-slate-500">販売事業者</th>
              <td class="py-2">一般社団法人医療の質・安全学会</td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">代表者・業務責任者</th>
              <td class="py-2">理事長 中島 和江</td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">所在地</th>
              <td class="py-2">〒113-0033 東京都文京区本郷2-29-1 渡辺ビル201</td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">販売URL</th>
              <td class="py-2">
                <a href="https://jsqshondemand.vision-bridge.org/" target="_blank" class="text-sky-700 underline">
                  https://jsqshondemand.vision-bridge.org/
                </a>
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">電話番号</th>
              <td class="py-2">03-5803-7828</td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">連絡先</th>
              <td class="py-2">
                <a href="https://jsqsh.jp/faq/consultation" target="_blank" class="text-sky-700 underline">
                  お問合せフォーム
                </a>                
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">商品の説明</th>
              <td class="py-2">動画（オンデマンドセミナー）</td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">販売価格</th>
              <td class="py-2">商品ページに税込価格で表示しています。</td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">商品代金以外の必要料金</th>
              <td class="py-2">
                インターネット接続に伴う通信料、パケット通信料等はお客様のご負担となります。
                送料は発生しません。
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">お支払い方法</th>
              <td class="py-2">クレジットカード決済（利用可能なカードブランドは決済画面でご確認ください）</td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">お支払い時期</th>
              <td class="py-2">
                ご注文確定時に決済処理が行われます。実際の引き落とし時期はご利用のクレジットカード会社の締日・支払日により異なります。
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">お引渡し時期</th>
              <td class="py-2">
                決済完了後、原則として直ちに視聴用URLを表示またはメールにてご案内いたします。
                システム処理・通信状況・決済確認等により、反映までお時間をいただく場合があります。
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">動画の視聴方法</th>
              <td class="py-2">
                決済完了後、当サイトが案内する視聴ページより動画をご視聴いただけます。視聴期間・視聴回数等の制限は各商品ページに記載します。
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">動作環境</th>
              <td class="py-2">
                動画の視聴にはインターネットに接続されたPC・スマートフォン・タブレット等が必要です。推奨ブラウザはGoogle Chrome、Microsoft Edge、Safari、Firefoxの各最新版です。
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">キャンセル・返品・返金</th>
              <td class="py-2">
                動画コンテンツという商品の性質上、決済完了後のお客様都合によるキャンセル・返品・返金はお受けしておりません。
                当学会の責めに帰すべき事由により視聴できない場合、または誤課金等が確認された場合は、状況を確認のうえ代替視聴方法の提供・再案内・返金等の対応を行います。
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">不良・不具合への対応</th>
              <td class="py-2">
                動画が再生できない、視聴用URLが届かない等の不具合が生じた場合は、お問合せフォームよりご連絡ください。
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">表現・商品に関する注意事項</th>
              <td class="py-2">
                本動画コンテンツは教育・学習を目的として提供するものです。学習効果・資格取得・試験合格・業務上の成果等を保証するものではありません。
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">申込みの有効期限</th>
              <td class="py-2">
                各商品ページに販売期間・申込期限・視聴期限の記載がある場合はそれに従います。定めがない場合、販売終了までお申込みいただけます。
              </td>
            </tr>
            <tr>
              <th class="text-left align-top py-2 pr-4 font-medium text-slate-500">個人情報の取扱い</th>
              <td class="py-2">
                お客様の個人情報は当学会のプライバシーポリシーに従って適切に取り扱います。
                <a href="https://qsh.jp/wp/wp-content/uploads/2026/07/policy.pdf" target="_blank" class="text-sky-700 underline block mt-1">
                  プライバシーポリシー（PDF）
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </DialogContent>
    </Dialog>

    <!-- 申込ダイアログ（独立コンポーネント） -->
    <ProductApplyDialog v-model:open="dialogOpen" :selected-set="selectedSet" />
  </div>
</template>