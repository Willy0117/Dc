<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const message = ref('Hello from Vite HMR!');

const changeMessage = () => {
    message.value = 'Message changed!';
};
</script>

<template>
    <AppLayout title="Test Page">
        <Head title="Test Page" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">{{ message }}</h2>
                    <p class="text-gray-600">This is a simple test page to check Vite dev server and HMR.</p>
                    <button @click="changeMessage" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Change Message
                    </button>
                    <p class="mt-4 text-sm text-gray-500">Edit this file and save to see HMR in action!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>