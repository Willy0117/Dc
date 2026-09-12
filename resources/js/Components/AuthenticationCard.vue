<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <div class="min-h-screen flex items-start justify-center bg-[#F7F5F0] px-4 py-10 pt-20 sm:pt-28">
        <div class="w-full max-w-3xl flex flex-col sm:flex-row rounded-xl overflow-hidden border border-black/5 shadow-sm">

            <!-- スマホ幅用：簡易ブランディングバー（sm以上では非表示） -->
            <div class="flex sm:hidden items-center gap-3 bg-[#1A2E2B] text-[#F7F5F0] px-5 py-4">
                <div class="w-9 h-9 shrink-0 rounded-full border border-[#C9A227] flex items-center justify-center">
                    <div class="w-[24px] h-[24px] rounded-full border border-[#C9A227]"></div>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] tracking-widest text-[#8FAAA3] leading-tight">
                        LICENSE MANAGEMENT SYSTEM
                    </p>
                    <p class="font-serif text-sm leading-tight truncate">
                        {{ page.props.appName }}
                    </p>
                </div>
            </div>

            <!-- ブランドパネル（sm以上でのみ表示） -->
            <div class="hidden sm:flex flex-col justify-between flex-none w-[42%] bg-[#1A2E2B] text-[#F7F5F0] px-8 py-10">
                <div>
                    <div class="w-12 h-12 rounded-full border border-[#C9A227] flex items-center justify-center mb-6">
                        <div class="w-[34px] h-[34px] rounded-full border border-[#C9A227]"></div>
                    </div>
                    <p class="text-[11px] tracking-widest text-[#8FAAA3] mb-1">
                        LICENSE MANAGEMENT SYSTEM
                    </p>
                    <p class="font-serif text-lg leading-relaxed">
                        {{ page.props.appName }}
                    </p>
                </div>
                <div class="border-t border-white/20 pt-4">
                    <p class="text-[11px] text-[#8FAAA3] mb-1">運営</p>
                    <p class="text-sm mb-0.5">株式会社Alivio JAPAN</p>
                    <p class="text-xs text-[#8FAAA3]">医療法人社団祐優会</p>
                </div>
            </div>

            <!-- フォームパネル -->
            <div class="flex-1 bg-white px-8 py-10 sm:px-9">
                <slot />
            </div>
        </div>
    </div>
</template>