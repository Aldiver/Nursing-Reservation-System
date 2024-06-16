<script setup>
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavItem from "@/Components/NavItem.vue";
import { Link } from "@inertiajs/vue3";
import {
    BellIcon,
    SunIcon,
    MoonIcon,
    HomeIcon,
} from "@heroicons/vue/24/outline";

const isSidebarOpen = ref(false);
const isDarkMode = ref(false);

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add("dark");
    } else {
        document.documentElement.classList.remove("dark");
    }
};

const navItems = [
    {
        href: "dashboard",
        active: false,
        label: "Dashboard",
        children: [],
        icon: HomeIcon,
    },
    {
        href: "#",
        active: false,
        label: "Admin",
        children: [
            {
                href: "admin.users.index",
                active: false,
                label: "Users",
                children: [],
                icon: null,
            },
            {
                href: "admin.departments.index",
                active: false,
                label: "Departments",
                children: [],
                icon: null,
            },
            {
                href: "admin.venues.index",
                active: false,
                label: "Venues",
                children: [],
                icon: null,
            },
        ],
        icon: HomeIcon,
    },
    {
        href: "reservations.index",
        active: false,
        label: "Reservations",
        children: [],
        icon: HomeIcon,
    },
    {
        href: "admin.schedules.index",
        active: false,
        label: "Schedules",
        children: [],
        icon: HomeIcon,
    },
];
</script>

<template>
    <div
        :class="{ dark: isDarkMode }"
        class="min-h-screen flex overflow-hidden bg-gray-100 dark:bg-gray-800"
    >
        <!-- Sidebar -->
        <aside
            :class="{ block: isSidebarOpen, hidden: !isSidebarOpen }"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg lg:block lg:relative dark:bg-gray-900"
        >
            <div class="flex flex-col h-full">
                <div
                    class="flex items-center justify-between p-4 border-gray-100 dark:border-gray-700"
                >
                    <Link :href="route('dashboard')" class="flex items-center">
                        <ApplicationLogo
                            class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                        />
                    </Link>
                    <button @click="isSidebarOpen = false" class="lg:hidden">
                        <svg
                            class="w-6 h-6 text-gray-500 dark:text-gray-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <nav class="mt-4 space-y-1 flex flex-col flex-1 px-2">
                        <NavItem
                            :item="item"
                            v-for="item in navItems"
                            :key="item.label"
                        />
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Navigation -->
            <nav
                class="bg-white border-b border-gray-100 flex-shrink-0 dark:bg-gray-900 dark:border-gray-700"
            >
                <div
                    class="flex justify-between items-center h-16 px-4 sm:px-6 lg:px-8"
                >
                    <div class="flex items-center">
                        <!-- Hamburger for smaller screens -->
                        <div class="flex lg:hidden ml-4">
                            <button
                                @click="isSidebarOpen = true"
                                class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700"
                            >
                                <svg
                                    class="w-6 h-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Right-aligned buttons -->
                    <div class="flex items-center space-x-4">
                        <!-- Toggle Dark Mode Button -->
                        <button
                            @click="toggleDarkMode"
                            class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none focus:text-gray-700 dark:focus:text-gray-100"
                        >
                            <span v-if="!isDarkMode">
                                <SunIcon class="w-6 h-6" />
                            </span>
                            <span v-else>
                                <MoonIcon class="w-6 h-6" />
                            </span>
                        </button>
                        <!-- Notification Bell -->
                        <button
                            class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none focus:text-gray-700 dark:focus:text-gray-100"
                        >
                            <BellIcon class="w-6 h-6" />
                        </button>
                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <Dropdown width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:text-gray-100 focus:outline-none transition ease-in-out duration-150"
                                        >
                                            {{ $page.props.auth.user.name }}
                                            <svg
                                                class="ml-2 -mr-0.5 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"
                                        >Profile</DropdownLink
                                    >
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        >Log Out</DropdownLink
                                    >
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
@media (min-width: 1024px) {
    aside {
        display: block !important;
    }
}
</style>
