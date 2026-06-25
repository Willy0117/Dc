import axios from 'axios'
import { watch } from 'vue'

export function useZipcode(zipRef, refs) {
  let timer = null

  watch(zipRef, (zip) => {
    console.log('zipRef changed:', zip)  // ← watchが発火しているか
    clearTimeout(timer)

    timer = setTimeout(async () => {
      if (!zip) return

      const normalized = zip.replace('-', '')
      if (normalized.length !== 7) return

      try {
        const { data } = await axios.get(`/api/zipcode/${normalized}`)

        if (data.results?.length) {
          const r = data.results[0]
          // 新形式（分離）
          refs.prefecture.value = r.address1
          refs.address1.value   = r.address2
          refs.address2.value   = r.address3
        }
      } catch (e) {
        console.error(e)
      }
    }, 400)
  })
}
