import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

export const isLoading = ref(false)
let timer = null

router.on('start', () => {
  // 速すぎる通信でチラつかないように
  timer = setTimeout(() => {
    isLoading.value = true
  }, 200)
})

router.on('finish', () => {
  if (timer) clearTimeout(timer)
  isLoading.value = false
})
// --- 追記: 419(CSRFトークン切れ)時、Inertiaのエラー画面を出さずに再読み込み ---
router.on('invalid', (event) => {
  if (event.detail.response.status === 419) {
    event.preventDefault()
    window.location.reload()
  }
})
