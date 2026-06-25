<template>
    <div class="min-h-screen bg-gray-50">

        <!-- Header -->
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="max-w-6xl mx-auto">
                <h1 class="text-xl font-bold">
                    動注治療ライセンス申込
                </h1>
            </div>
        </header>

        <StepIndicator :current-step="3" />

        <div class="max-w-6xl mx-auto px-6 py-8">

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    契約書の確認
                </h2>
                <p class="mt-2 text-gray-500">
                    契約書をご確認いただき、内容に同意の上、電子署名へ進んでください。
                </p>
            </div>

            <!-- 契約書PDF -->
            <div class="mb-4">
                <h3 v-if="agreement_pdf_url" class="text-sm font-semibold text-gray-700 mb-2">
                    ライセンス契約書
                </h3>
                <div
                    id="pdf-container"
                    class="space-y-6 overflow-y-auto max-h-[900px] border rounded-xl p-4 bg-gray-50"
                ></div>
            </div>

            <!-- 合意書PDF（再契約の場合のみ） -->
            <div v-if="agreement_pdf_url" class="mb-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    合意書
                </h3>
                <div
                    id="agreement-pdf-container"
                    class="space-y-6 overflow-y-auto max-h-[900px] border rounded-xl p-4 bg-gray-50"
                ></div>
            </div>

            <!-- 同意 -->
            <div class="mt-6 bg-white rounded-xl border p-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input
                        v-model="agreed"
                        type="checkbox"
                        class="mt-1"
                    />
                    <span>
                        契約書{{ agreement_pdf_url ? '・合意書' : '' }}の内容を確認し、同意します。
                    </span>
                </label>
            </div>

            <!-- ボタン -->
            <div class="flex justify-between mt-8">

                <Link
                    :href="route('applications.register') + '?token=' + data.token"
                    class="text-sm flex items-center gap-2 px-6 py-2.5 border border-gray-200 text-gray-600 font-semibold rounded-xl hover:bg-gray-50 transition-colors"
                >
                    <ArrowLeft class="w-4 h-4" />戻る
                </Link>

                <button
                    :disabled="!agreed || signing"
                    @click="handleSign"
                    class="text-sm flex items-center gap-2 px-8 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                    {{ signing ? '処理中...' : '電子署名へ進む' }}<ArrowRight class="w-4 h-4" />
                </button>

            </div>

        </div>

        <ApplicationFooter />

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ArrowLeft, ArrowRight } from 'lucide-vue-next'

import StepIndicator from '@/Components/StepIndicator.vue'
import ApplicationFooter from '@/Components/ApplicationFooter.vue'

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    pdf_url: {
        type: String,
        required: true,
    },
    agreement_pdf_url: {
        type: String,
        default: null,
    },
})

const agreed  = ref(false)
const signing = ref(false)

const handleSign = () => {
    if (!agreed.value) return
    signing.value = true
    router.post(route('applications.sign'))
}

// PDF.jsでPDFをcanvasに描画
const renderPdf = async (url, containerId) => {
    const pdfjsLib = window.pdfjsLib

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'

    const pdf = await pdfjsLib.getDocument({
        url,
        cMapUrl: '/cmaps/',
        cMapPacked: true,
    }).promise

    const container = document.getElementById(containerId)

    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page     = await pdf.getPage(pageNum)
        const viewport = page.getViewport({ scale: 1.5 })
        const canvas   = document.createElement('canvas')
        const context  = canvas.getContext('2d')

        canvas.width  = viewport.width
        canvas.height = viewport.height
        canvas.classList.add('shadow', 'mx-auto', 'bg-white')

        container.appendChild(canvas)

        await page.render({
            canvasContext: context,
            viewport,
            renderInteractiveForms: true,
        }).promise
    }
}

onMounted(async () => {
    // 契約書PDF表示
    await renderPdf(props.pdf_url, 'pdf-container')

    // 合意書PDF表示（再契約の場合のみ）
    if (props.agreement_pdf_url) {
        await renderPdf(props.agreement_pdf_url, 'agreement-pdf-container')
    }
})
</script>