<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Switch } from '@/components/ui/switch'

const form = useForm({
  name: '',
  description: '',
  price_jpy: 0,
  stripe_price_id: '',
  passing_score: 80,
  active: true,
})

function submit() {
  form.post(route('admin.video-sets.store'))
}
</script>

<template>
  <Head title="動画セット新規作成" />
  <AppLayout>
    <div class="p-6 max-w-xl space-y-6">
      <h1 class="text-xl font-semibold">動画セット新規作成</h1>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <Label for="name">名称</Label>
          <Input id="name" v-model="form.name" required />
          <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
        </div>

        <div>
          <Label for="description">説明</Label>
          <Textarea id="description" v-model="form.description" rows="5" />
        </div>

        <div>
          <Label for="price_jpy">価格（円・税込）</Label>
          <Input id="price_jpy" type="number" v-model.number="form.price_jpy" required />
        </div>

        <div>
          <Label for="stripe_price_id">Stripe Price ID</Label>
          <Input id="stripe_price_id" v-model="form.stripe_price_id" placeholder="price_xxxxxxxxxxxx" required />
          <p class="text-xs text-muted-foreground">Stripeダッシュボードで作成した商品のPrice IDを入力してください。</p>
        </div>

        <div>
          <Label for="passing_score">テスト合格ライン（%）</Label>
          <Input id="passing_score" type="number" v-model.number="form.passing_score" min="0" max="100" required />
        </div>

        <div class="flex items-center gap-2">
          <Switch id="active" v-model:checked="form.active" />
          <Label for="active">公開する</Label>
        </div>

        <Button type="submit" :disabled="form.processing">作成する</Button>
      </form>
    </div>
  </AppLayout>
</template>
