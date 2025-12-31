import axios from 'axios'
import { watch } from 'vue'

export function useZipcode(zipRef, addressRef) {
  let timer = null

  watch(zipRef, (zip) => {
    clearTimeout(timer)

    timer = setTimeout(async () => {
      if (!zip) return

      const normalized = zip.replace('-', '')
      if (normalized.length !== 7) return

      try {
        // ★ Laravel 経由のみ
        const { data } = await axios.get(
          `/api/zipcode/${normalized}`
        )

        if (data.results?.length) {
          const r = data.results[0]
          addressRef.value =
            r.address1 + r.address2 + r.address3
        }
      } catch (e) {
        console.error(e)
      }
    }, 400)
  })
}
