<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
<div class="flex justify-center mb-6">
    <div class="w-20 h-20 flex items-center justify-center rounded-full bg-indigo-50 shadow-sm">
        <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 11c1.657 0 3 1.343 3 3v3H9v-3c0-1.657 1.343-3 3-3zm6 0V9a6 6 0 10-12 0v2" />
        </svg>
    </div>
</div>
</template>
