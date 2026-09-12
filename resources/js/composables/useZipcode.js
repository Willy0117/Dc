import axios from 'axios'
import { watch } from 'vue'

export function useZipcode(zipRef, refs) {
  let timer = null

  watch(zipRef, (zip) => {
    clearTimeout(timer)

    timer = setTimeout(async () => {
      if (!zip) return

      const normalized = zip.replace('-', '')
      if (normalized.length !== 7) return

      try {
        const { data } = await axios.get(`/api/zipcode/${normalized}`)

        if (data.results?.length) {
          const r = data.results[0]
          // 変更点：郵便番号APIのaddress2(市区町村)・address3(町域)を
          // 結合してrefs.address2（フォームの「市区町村」欄）にまとめて入れる。
          // refs.address1（都道府県）はそのまま、
          // refs.address2は番地・建物名の入力用として空けておく。
          refs.prefecture.value = r.address1
          refs.address1.value   = [r.address2, r.address3].filter(Boolean).join('')
          // refs.address2（番地・建物名）は自動入力せず、ユーザー入力に委ねる
        }
      } catch (e) {
        console.error(e)
      }
    }, 400)
  })
}