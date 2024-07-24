<script setup>
import UserAvatar from "@/Components/UserAvatar.vue";

defineProps({
    username: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <UserAvatar :username="username">
        <slot />
    </UserAvatar>
</template>
