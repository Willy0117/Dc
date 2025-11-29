<template>
  <MembersForm :member="member" />
</template>

<script setup>
import MembersForm from './MembersForm.vue'
const props = defineProps({ member: Object })
</script>