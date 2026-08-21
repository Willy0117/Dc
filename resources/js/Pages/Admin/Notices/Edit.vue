<template>
  <AppLayout>
    <template #header>
      <p class="text-xs text-muted-foreground">お知らせ管理</p>
      <h1 class="text-xl font-semibold">{{ notice ? 'お知らせ編集' : 'お知らせ新規登録' }}</h1>
    </template>

    <div class="p-6 max-w-4xl">
      <form @submit.prevent="submit" class="space-y-5">
        <div class="space-y-1.5">
          <Label>タイトル</Label>
          <Input v-model="form.title" required />
          <p v-if="form.errors.title" class="text-red-500 text-xs">{{ form.errors.title }}</p>
        </div>

        <div class="space-y-1.5">
          <Label>本文</Label>
          <Textarea v-model="form.body" rows="5" />
        </div>

        <div class="space-y-1.5">
          <Label>YouTube URL(任意)</Label>
          <Input v-model="form.youtube_url" placeholder="https://youtu.be/..." />
          <p v-if="form.errors.youtube_url" class="text-red-500 text-xs">{{ form.errors.youtube_url }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4" v-if="form.youtube_url">
          <div class="space-y-1.5">
            <Label>動画公開開始(任意)</Label>
            <Input v-model="form.video_available_from" type="datetime-local" />
          </div>
          <div class="space-y-1.5">
            <Label>動画公開終了(任意)</Label>
            <Input v-model="form.video_available_until" type="datetime-local" />
          </div>
        </div>

        <div class="space-y-3 border rounded-lg p-4">
          <Label>配信対象</Label>

          <Select v-model="form.target_type">
            <SelectTrigger class="w-64">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">全員(病院・先生)</SelectItem>
              <SelectItem value="organizations_all">病院のみ(全病院)</SelectItem>
              <SelectItem value="members_all">先生のみ(全先生)</SelectItem>
              <SelectItem value="organizations">個別病院を指定</SelectItem>
              <SelectItem value="members">個別先生を指定</SelectItem>
            </SelectContent>
          </Select>

          <div v-if="form.target_type === 'organizations'">
            <OrganizationMultiSelect v-model="form.organization_ids" :items="allOrganizations" />
          </div>

          <div v-if="form.target_type === 'members'">
            <MultiSelect v-model="form.member_ids" :items="allMembers" />
          </div>
        </div>

        <div class="space-y-1.5">
          <Label>公開日時(空欄なら下書き保存)</Label>
          <Input v-model="form.published_at" type="datetime-local" />
        </div>

        <div class="flex gap-2">
          <Button type="submit" :disabled="form.processing">保存</Button>
          <Button type="button" variant="outline" as-child>
            <Link :href="route('admin.notices.index')">キャンセル</Link>
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import OrganizationMultiSelect from '@/Components/Admin/OrganizationMultiSelect.vue'
import MultiSelect from '@/Components/Admin/MultiSelect.vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const props = defineProps({
  notice: Object,
  allOrganizations: { type: Array, default: () => [] },
  allMembers: { type: Array, default: () => [] },
})

function toDatetimeLocal(value) {
  return value ? value.slice(0, 16) : ''
}

const form = useForm({
  title: props.notice?.title ?? '',
  body: props.notice?.body ?? '',
  youtube_url: props.notice?.youtube_url ?? '',
  video_available_from: toDatetimeLocal(props.notice?.video_available_from),
  video_available_until: toDatetimeLocal(props.notice?.video_available_until),
  target_type: props.notice?.target_type ?? 'all',
  published_at: toDatetimeLocal(props.notice?.published_at),
  organization_ids: props.notice?.organizations?.map(o => o.id) ?? [],
  member_ids: props.notice?.members?.map(m => m.id) ?? [],
})

function submit() {
  if (props.notice) {
    form.put(route('admin.notices.update', props.notice.id))
  } else {
    form.post(route('admin.notices.store'))
  }
}
</script>
