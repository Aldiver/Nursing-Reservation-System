<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import NavLink from "@/Components/NavLink.vue";
import { ChevronDownIcon } from "@heroicons/vue/24/solid";
import { computed } from "vue";

const props = defineProps({
    item: {
        type: Object,
        required: false,
    },
});

const hasActiveChild = computed(() => {
    function hasActiveItem(items) {
        return items.some(
            (item) => item.active || hasActiveItem(item.children)
        );
    }

    return hasActiveItem(props.item.children);
});
</script>
<template>
    <NavLink
        v-if="!item.children.length"
        :class="[
            'group flex w-full items-center rounded-md space-x-2 px-4 py-2 text-sm text-gray-700',
            'hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800',
            item.active
                ? 'font-semibold text-gray-800'
                : 'font-medium text-gray-600',
        ]"
        :active="route().current(item.href)"
        :href="route(item.href)"
    >
        <component
            :class="[
                'mr-2 h-6 w-6 shrink-0 group-hover:text-gray-600',
                item.active ? 'text-gray-600' : 'text-gray-400',
            ]"
            :is="item.icon"
            v-if="item.icon"
        ></component>
        <span>{{ item.label }}</span>
    </NavLink>

    <Disclosure v-else v-slot="{ open }" :default-open="hasActiveChild">
        <DisclosureButton
            :class="[
                'group flex w-full items-center rounded-md space-x-2 px-4 py-2 text-left text-sm font-medium',
                ' dark:text-gray-300 text-gray-600 hover:text-gray-800 focus:outline-none', // Base button styles
                open ? 'text-gray-600' : 'text-gray-400', // Set button color based on open state
            ]"
        >
            <component
                :class="[
                    'mr-2 h-6 w-6 shrink-0 group-hover:text-gray-600',
                    open ? 'text-gray-600' : 'text-gray-400',
                ]"
                :is="item.icon"
                v-if="item.icon"
            ></component>
            <span class="flex-1">{{ item.label }}</span>
            <ChevronDownIcon
                :class="[
                    'h-6 w-6 shrink-0',
                    open ? '-rotate-180 text-gray-600' : 'text-gray-400',
                ]"
            />
        </DisclosureButton>

        <DisclosurePanel class="ml-4 flex-col flex flex-1">
            <NavItem
                v-for="child in item.children"
                :key="child.label"
                :item="child"
                :parentIsActive="open"
                children
            />
        </DisclosurePanel>
    </Disclosure>
</template>
