<template>
    <!-- pt-6 -> pt-24 -->
    <!--div class="min-h-screen flex flex-col sm:justify-center items-center pt-24 sm:pt-0 bg-gray-100" -->
    <div class="min-h-screen flex flex-col justify-start items-center pt-8 bg-gray-100 text-sm">
        <div>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
