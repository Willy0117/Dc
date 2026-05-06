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