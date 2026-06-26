<template>
  <footer class="border-t border-gray-200 mt-12">
    <div class="max-w-3xl mx-auto px-6 py-6">

      <!-- リンク行 -->
      <div class="flex items-center gap-4 text-xs text-gray-400">

        <!-- 特定商取引法 → Dialog -->
        <button
          type="button"
          class="hover:text-gray-600 transition-colors"
          @click="showTokusho = true"
        >
          特定商取引に関する法律に基づく表示
        </button>

        <span class="text-gray-300">|</span>

        <!-- プライバシーポリシー → 外部リンク -->
        <a
          href="https://okuno-y-clinic.com/privacy"
          target="_blank"
          rel="noopener noreferrer"
          class="hover:text-gray-600 transition-colors"
        >
          プライバシーポリシー
        </a>

        <span class="text-gray-300">|</span>

        <!-- 利用規約 → Dialog -->
        <button
          type="button"
          class="hover:text-gray-600 transition-colors"
          @click="showTerms = true"
        >
          利用規約
        </button>

      </div>

      <p class="mt-4 text-xs text-gray-400 text-center">
        &copy; {{ year }} Alivio Japan Co., Ltd. All rights reserved.
      </p>
    </div>

    <!-- ===== 特定商取引法 Dialog ===== -->
    <Teleport to="body">
      <Transition name="dialog">
        <div
          v-if="showTokusho"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          @click.self="showTokusho = false"
        >
          <!-- オーバーレイ -->
          <div class="absolute inset-0 bg-black/40" @click="showTokusho = false" />

          <!-- パネル -->
          <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
              <h2 class="text-sm font-semibold text-gray-800">特定商取引に関する法律に基づく表示</h2>
              <button
                type="button"
                class="text-gray-400 hover:text-gray-600 transition-colors"
                @click="showTokusho = false"
              >
                <X class="w-5 h-5" />
              </button>
            </div>
            <!-- 本文 -->
            <div class="overflow-y-auto px-6 py-5">
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
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ===== 利用規約 Dialog ===== -->
    <Teleport to="body">
      <Transition name="dialog">
        <div
          v-if="showTerms"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          @click.self="showTerms = false"
        >
          <!-- オーバーレイ -->
          <div class="absolute inset-0 bg-black/40" @click="showTerms = false" />

          <!-- パネル -->
          <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
              <h2 class="text-sm font-semibold text-gray-800">利用規約</h2>
              <button
                type="button"
                class="text-gray-400 hover:text-gray-600 transition-colors"
                @click="showTerms = false"
              >
                <X class="w-5 h-5" />
              </button>
            </div>
            <!-- 本文 -->
            <div class="overflow-y-auto px-6 py-5 text-xs text-gray-600 leading-relaxed space-y-6">

              <p class="text-gray-500">
                この利用規約（以下，「本規約」といいます。）は，株式会社Alivio JAPAN（以下，「当社」といいます。）がこのウェブサイト上で提供するサービス（以下，「本サービス」といいます。）の利用条件を定めるものです。登録ユーザーの皆さま（以下，「ユーザー」といいます。）には，本規約に従って，本サービスをご利用いただきます。
              </p>

              <div v-for="article in articles" :key="article.title" class="space-y-2">
                <h3 class="font-semibold text-gray-800 border-l-2 border-blue-500 pl-3">{{ article.title }}</h3>
                <p v-if="article.lead" class="text-gray-500">{{ article.lead }}</p>
                <ul class="space-y-1">
                  <li v-for="(item, i) in article.items" :key="i" class="pl-4 relative text-gray-500">
                    <span class="absolute left-0 text-gray-300">・</span>
                    {{ item.text }}
                    <ul v-if="item.children" class="mt-1 space-y-1 pl-4">
                      <li v-for="(child, j) in item.children" :key="j" class="relative text-gray-400">
                        <span class="absolute left-0 text-gray-300">–</span>
                        <span class="pl-3">{{ child }}</span>
                      </li>
                    </ul>
                  </li>
                </ul>
              </div>

              <p class="text-right text-gray-400">以上</p>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </footer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { X } from 'lucide-vue-next'

const showTokusho = ref(false)
const showTerms = ref(false)
const year = computed(() => new Date().getFullYear())

const articles = [
  {
    title: '第1条（適用）',
    items: [
      { text: '本規約は，ユーザーと当社との間の本サービスの利用に関わる一切の関係に適用されるものとします。' },
      { text: '当社は本サービスに関し，本規約のほか，ご利用にあたってのルール等，各種の定め（以下，「個別規定」といいます。）をすることがあります。これら個別規定はその名称のいかんに関わらず，本規約の一部を構成するものとします。' },
      { text: '本規約の規定が前条の個別規定の規定と矛盾する場合には，個別規定において特段の定めなき限り，個別規定の規定が優先されるものとします。' },
    ],
  },
  {
    title: '第2条（利用登録）',
    items: [
      { text: '本サービスにおいては，登録希望者が本規約に同意の上，当社の定める方法によって利用登録を申請し，当社がこれを承認することによって，利用登録が完了するものとします。' },
      {
        text: '当社は，利用登録の申請者に以下の事由があると判断した場合，利用登録の申請を承認しないことがあり，その理由については一切の開示義務を負わないものとします。',
        children: [
          '利用登録の申請に際して虚偽の事項を届け出た場合',
          '本規約に違反したことがある者からの申請である場合',
          'その他，当社が利用登録を相当でないと判断した場合',
        ],
      },
    ],
  },
  {
    title: '第3条（ユーザーIDおよびパスワード等の管理）',
    items: [
      { text: 'ユーザーは，自己の責任において，本サービスのユーザーIDおよびパスワード，本サービスに接続するためにユーザーごとに割り当てたURL（以下「ユーザーIDおよびパスワード等」といいます。）を適切に管理するものとします。' },
      { text: 'ユーザーは，いかなる場合にも，ユーザーIDおよびパスワード等を第三者に譲渡または貸与し，もしくは第三者と共用することはできません。当社は，ユーザーIDとパスワードの組み合わせが登録情報と一致してログインされた場合，もしくは本サービスに接続するためにユーザーごとに割り当てたURLから本サービスに接続した場合には，そのユーザーIDもしくはそのURLを登録しているユーザー自身による利用とみなします。' },
      { text: 'ユーザーID及びパスワードが第三者によって使用されたことによって生じた損害は，当社に故意又は重大な過失がある場合を除き，当社は一切の責任を負わないものとします。' },
    ],
  },
  {
    title: '第4条（利用料金および支払方法）',
    items: [
      { text: 'ユーザーは，本サービスの有料部分の対価として当社が別途定め，本ウェブサイトに利用料金がある場合には，当社が指定する方法により支払うものとします。' },
      { text: 'ユーザーが利用料金の支払を遅滞した場合には，ユーザーは年14．6％の割合による遅延損害金を支払うものとします。' },
    ],
  },
  {
    title: '第5条（禁止事項）',
    lead: 'ユーザーは，本サービスの利用にあたり，以下の行為をしてはなりません。',
    items: [
      { text: '法令または公序良俗に違反する行為' },
      { text: '犯罪行為に関連する行為' },
      { text: '本サービスの内容等，本サービスに含まれる著作権，商標権ほか知的財産権を侵害する行為' },
      { text: '当社，ほかのユーザー，またはその他第三者のサーバーまたはネットワークの機能を破壊したり，妨害したりする行為' },
      { text: '本サービスによって得られた情報を商業的に利用する行為' },
      { text: '当社のサービスの運営を妨害するおそれのある行為' },
      { text: '不正アクセスをし，またはこれを試みる行為' },
      { text: '他のユーザーに関する個人情報等を収集または蓄積する行為' },
      { text: '不正な目的を持って本サービスを利用する行為' },
      { text: '本サービスの他のユーザーまたはその他の第三者に不利益，損害，不快感を与える行為' },
      { text: '他のユーザーに成りすます行為' },
      { text: '当社が許諾しない本サービス上での宣伝，広告，勧誘，または営業行為' },
      { text: '当社のサービスに関連して，反社会的勢力に対して直接または間接に利益を供与する行為' },
      { text: 'その他，当社が不適切と判断する行為' },
    ],
  },
  {
    title: '第6条（本サービスの提供の停止等）',
    items: [
      {
        text: '当社は，以下のいずれかの事由があると判断した場合，ユーザーに事前に通知することなく本サービスの全部または一部の提供を停止または中断することができるものとします。',
        children: [
          '本サービスにかかるコンピュータシステムの保守点検または更新を行う場合',
          '地震，落雷，火災，停電または天災などの不可抗力により，本サービスの提供が困難となった場合',
          'コンピュータまたは通信回線等が事故により停止した場合',
          'その他，当社が本サービスの提供が困難と判断した場合',
        ],
      },
      { text: '当社は，本サービスの提供の停止または中断により，ユーザーまたは第三者が被ったいかなる不利益または損害についても，一切の責任を負わないものとします。' },
    ],
  },
  {
    title: '第7条（利用制限および登録抹消）',
    items: [
      {
        text: '当社は，ユーザーが以下のいずれかに該当する場合には，事前の通知なく，ユーザーに対して，本サービスの全部もしくは一部の利用を制限し，またはユーザーとしての登録を抹消することができるものとします。',
        children: [
          '本規約のいずれかの条項に違反した場合',
          '登録事項に虚偽の事実があることが判明した場合',
          '料金等の支払債務の不履行があった場合',
          '当社からの連絡に対し，一定期間返答がない場合',
          '本サービスについて，最終の利用から一定期間利用がない場合',
          'その他，当社が本サービスの利用を適当でないと判断した場合',
        ],
      },
      { text: '当社は，本条に基づき当社が行った行為によりユーザーに生じた損害について，一切の責任を負いません。' },
    ],
  },
  {
    title: '第8条（退会）',
    items: [
      { text: 'ユーザーは，当社の定める退会手続により，本サービスから退会できるものとします。' },
    ],
  },
  {
    title: '第9条（保証の否認および免責事項）',
    items: [
      { text: '当社は，本サービスに事実上または法律上の瑕疵（安全性，信頼性，正確性，完全性，有効性，特定の目的への適合性，セキュリティなどに関する欠陥，エラーやバグ，権利侵害などを含みます。）がないことを明示的にも黙示的にも保証しておりません。' },
      { text: '当社は，本サービスに起因してユーザーに生じたあらゆる損害について，当社の故意又は重過失による場合を除き，一切の責任を負いません。ただし，本サービスに関する当社とユーザーとの間の契約（本規約を含みます。）が消費者契約法に定める消費者契約となる場合，この免責規定は適用されません。' },
      { text: '前項ただし書に定める場合であっても，当社は，当社の過失（重過失を除きます。）による債務不履行または不法行為によりユーザーに生じた損害のうち特別な事情から生じた損害（当社またはユーザーが損害発生につき予見し，または予見し得た場合を含みます。）について一切の責任を負いません。また，当社の過失（重過失を除きます。）による債務不履行または不法行為によりユーザーに生じた損害の賠償は，ユーザーから当該損害が発生した月に受領した利用料の額を上限とします。' },
      { text: '当社は，本サービスに関して，ユーザーと他のユーザーまたは第三者との間において生じた取引，連絡または紛争等について一切責任を負いません。' },
    ],
  },
  {
    title: '第10条（サービス内容の変更等）',
    items: [
      { text: '当社は，ユーザーへの事前の告知をもって，本サービスの内容を変更，追加または廃止することがあり，ユーザーはこれを承諾するものとします。' },
    ],
  },
  {
    title: '第11条（利用規約の変更）',
    items: [
      {
        text: '当社は以下の場合には，ユーザーの個別の同意を要せず，本規約を変更することができるものとします。',
        children: [
          '本規約の変更がユーザーの一般の利益に適合するとき。',
          '本規約の変更が本サービス利用契約の目的に反せず，かつ，変更の必要性，変更後の内容の相当性その他の変更に係る事情に照らして合理的なものであるとき。',
        ],
      },
      { text: '当社はユーザーに対し，前項による本規約の変更にあたり，事前に，本規約を変更する旨及び変更後の本規約の内容並びにその効力発生時期を通知します。' },
    ],
  },
  {
    title: '第12条（個人情報の取扱い）',
    items: [
      { text: '当社は，本サービスの利用によって取得する個人情報については，当社「プライバシーポリシー」に従い適切に取り扱うものとします。' },
    ],
  },
  {
    title: '第13条（通知または連絡）',
    items: [
      { text: 'ユーザーと当社との間の通知または連絡は，当社の定める方法によって行うものとします。当社は，ユーザーから，当社が別途定める方式に従った変更届け出がない限り，現在登録されている連絡先が有効なものとみなして当該連絡先へ通知または連絡を行い，これらは，発信時にユーザーへ到達したものとみなします。' },
    ],
  },
  {
    title: '第14条（権利義務の譲渡の禁止）',
    items: [
      { text: 'ユーザーは，当社の書面による事前の承諾なく，利用契約上の地位または本規約に基づく権利もしくは義務を第三者に譲渡し，または担保に供することはできません。' },
    ],
  },
  {
    title: '第15条（準拠法・裁判管轄）',
    items: [
      { text: '本規約の解釈にあたっては，日本法を準拠法とします。' },
      { text: '本サービスに関して紛争が生じた場合には，東京地方裁判所を第一審の専属的合意管轄裁判所とする。' },
    ],
  },
]
</script>

<style scoped>
/* Dialog フェードイン */
.dialog-enter-active,
.dialog-leave-active {
  transition: opacity 0.2s ease;
}
.dialog-enter-active .relative,
.dialog-leave-active .relative {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
.dialog-enter-from,
.dialog-leave-to {
  opacity: 0;
}
.dialog-enter-from .relative {
  transform: translateY(8px);
  opacity: 0;
}
.dialog-leave-to .relative {
  transform: translateY(8px);
  opacity: 0;
}
</style>