<script setup>
import { computed, ref } from "vue";
import BaseIcon from "./BaseIcon.vue";
import CardBox from "./CardBox.vue";

const props = defineProps({
    path: {
        type: String,
        required: true,
    },
    w: {
        type: String,
        default: "w-6",
    },
    h: {
        type: String,
        default: "h-6",
    },
    size: {
        type: [String, Number],
        default: null,
    },
    notifications: {
        type: Array,
        default: () => [],
    },
});

const badgeCount = computed(() => {
    return props.notifications.length;
});
</script>

<template>
    <div class="relative flex">
        <BaseIcon :size="size" :path="path" :w="w" :h="h">
            <slot />
        </BaseIcon>
        <span
            v-if="badgeCount > 0"
            class="absolute top-0 right-0 bg-red-500 text-white rounded-full px-1 text-xs"
        >
            {{ badgeCount }}
        </span>
    </div>
</template>
