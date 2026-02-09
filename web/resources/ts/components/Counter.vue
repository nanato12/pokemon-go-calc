<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    initialCount?: number;
}>();

const count = ref(props.initialCount ?? 0);

function increment(): void {
    count.value++;
}

function decrement(): void {
    count.value--;
}
</script>

<template>
    <div class="flex items-center gap-4">
        <button
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            @click="decrement"
        >
            -
        </button>
        <span class="text-2xl font-bold">{{ count }}</span>
        <button
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            @click="increment"
        >
            +
        </button>
    </div>
</template>
