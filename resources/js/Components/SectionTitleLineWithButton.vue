<script setup>
import { useSlots, computed } from "vue";

defineProps({
    icon: {
        default: null,
    },
    title: {
        type: String,
        required: true,
    },
    main: Boolean,
});

const hasSlot = computed(() => useSlots().default);
</script>

<template>
    <section
        :class="{ 'pt-6': !main }"
        class="mb-6 flex items-center justify-between"
    >
        <div class="flex items-center justify-start">
            <div
                v-if="icon && main"
                class="mr-3 rounded-full h-16 w-16 bg-white dark:bg-gray-900 flex items-center justify-center"
            >
                <component
                    :is="icon"
                    class="h-8 w-8 text-gray-800 dark:text-gray-200"
                />
            </div>
            <component
                v-else-if="icon"
                :is="icon"
                class="mr-2 h-6 w-6 text-gray-800 dark:text-gray-200"
            />
            <h1
                :class="main ? 'text-3xl' : 'text-2xl'"
                class="leading-tight text-gray-800 font-semibold dark:text-gray-200"
            >
                {{ title }}
            </h1>
        </div>
        <slot v-if="hasSlot" />
    </section>
</template>
