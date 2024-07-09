<script setup>
import { ref, watch, computed, onMounted } from "vue";

const props = defineProps({
    type: {
        type: String,
        default: "td",
    },
    check: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["checked"]);

const checked = ref(false);

onMounted(() => {
    if (props.check) {
        checked.value = props.check;
        emit("checked", checked.value);
    }
});

watch(checked, (newVal) => {
    emit("checked", newVal);
});

const isChecked = computed(() => props.check);

watch(isChecked, (newVal) => {
    checked.value = newVal;
});
</script>

<template>
    <component :is="type" class="mr-6 mb-3">
        <label class="checkbox">
            <input v-model="checked" type="checkbox" />
            <span class="check" />
            <span class="pl-2">{{ label }}</span>
        </label>
    </component>
</template>
