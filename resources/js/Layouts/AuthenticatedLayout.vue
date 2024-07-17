<script setup>
import { ref, onMounted } from "vue";
import { useStyleStore } from "@/stores/style.js";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavItem from "@/Components/NavItem.vue";
import { Link } from "@inertiajs/vue3";
import { BellIcon, SunIcon, MoonIcon } from "@heroicons/vue/24/outline";
import {
    mdiViewDashboardOutline,
    mdiCardAccountDetails,
    mdiSecurity,
    mdiListBox,
    mdiInvoiceTextClock,
    mdiClipboardList,
    mdiOfficeBuildingMarkerOutline,
    mdiBellOutline,
} from "@mdi/js";
import BaseIconWithBadge from "@/Components/BaseIconWithBadge.vue";
import CardBoxNotifications from "@/Components/CardBoxNotifications.vue";
import CardBox from "@/Components/CardBox.vue";
import CardBoxComponentEmpty from "@/Components/CardBoxComponentEmpty.vue";
import notify from "@/scripts/notification_helpers.js";
import { router } from '@inertiajs/vue3'

const isSidebarOpen = ref(false);
const isDarkMode = ref(false);

const styleStore = useStyleStore();
const notifications = ref([]);
const unreadNotificationCount = ref(0);

const unreadNotifsCount = () => {
    notify.fetchUnreadNotifications().then((data) => {
        unreadNotificationCount.value = data.length;
    });
};

const loadData = () => {
    notify.fetchNotifications().then((data) => {
        unreadNotifsCount();
        console.log("Notifications: ", data);
        notifications.value = data;
    });
};

const markNotifAll = () => {
    console.log("closed papers");
    notify.markAllNotificationsAsRead().then(() => {
        loadData();
    });
};

const markNotif = (id) => {
    notify.markNotificationAsRead(id).then(() => {
        loadData();
    });
};

const deleteNotif = (id) => {
    notify.deleteNotification(id).then(() => {
        loadData();
    });
};

const deleteAllNotif = () => {
    notify.deleteAllNotification().then(() => {
        loadData();
    });
};

const handleNotificationClick = (notifId, reservationId) => {
    markNotif(notifId);
    //inertia get route()
    console.log(reservationId)
    router.get(route('reservations.show', reservationId));
}

onMounted(() => {
    loadData();
});

const toggleDarkMode = () => {
    styleStore.setDarkMode();
};

const navItems = [
    {
        href: "dashboard.index",
        active: false,
        label: "Dashboard",
        children: [],
        icon: mdiViewDashboardOutline,
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
                icon: mdiCardAccountDetails,
            },
            {
                href: "admin.departments.index",
                active: false,
                label: "Departments",
                children: [],
                icon: mdiClipboardList,
            },
            {
                href: "admin.venues.index",
                active: false,
                label: "Venues",
                children: [],
                icon: mdiOfficeBuildingMarkerOutline,
            },
        ],
        icon: mdiSecurity,
    },
    {
        href: "reservations.index",
        active: false,
        label: "Reservations",
        children: [],
        icon: mdiListBox,
    },
    {
        href: "admin.schedules.index",
        active: false,
        label: "Schedules",
        children: [],
        icon: mdiInvoiceTextClock,
    },
];
</script>

<template>
    <div
        :class="{
            dark: styleStore.darkMode,
        }"
        class="flex flex-row min-h-screen overflow-hidden"
    >
        <div
            class="flex min-h-screen transition-position lg:w-auto bg-gray-200 dark:bg-slate-800 dark:text-slate-100"
        >
            <aside
                id="aside"
                :class="{ block: isSidebarOpen, hidden: !isSidebarOpen }"
                class="flex-none fixed lg:py-2 lg:pl-2 inset-y-0 left-0 z-50 w-64 shadow-lg lg:block lg:relative"
            >
                <div class="aside lg:rounded-2xl flex-none h-full bg-gray-900">
                    <div
                        class="flex flex-row h-28 items-center justify-between p-2 border-b-2 border-gray-800"
                    >
                        <div
                            class="items-center flex-1 lg:text-left xl:text-center xl:pl-0"
                        >
                            <Link
                                :href="route('dashboard.index')"
                                class="flex items-center justify-center w-full"
                            >
                                <ApplicationLogo
                                    class="block h-24 w-auto fill-current"
                                />
                            </Link>
                        </div>
                    </div>

                    <div class="flex-none">
                        <nav class="mt-4 space-y-1 flex-none px-2">
                            <div v-for="item in navItems" :key="item.label">
                                <NavItem
                                    v-if="
                                        !$page.props.auth.roles.includes(
                                            'Admin'
                                        )
                                            ? item.label === 'Reservations' ||
                                              item.label === 'Dashboard' ||
                                              item.label === 'Schedules'
                                            : true
                                    "
                                    :item="item"
                                    :icon="item.icon"
                                />
                            </div>
                        </nav>
                    </div>
                </div>
            </aside>
        </div>
        <!-- Main Content -->
        <div
            class="flex-1 flex flex-col bg-gray-200 dark:bg-slate-800 dark:text-slate-100"
        >
            <!-- Top Navigation -->
            <nav
                class="top-0 z-30 left-0 right-0 fixed bg-gray-200 h-14 w-full transition-position xl:pl-60 lg:w-auto dark:bg-slate-800"
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
                        <div class="relative">
                            <CardBoxNotifications @closed="markNotifAll">
                                <template #trigger>
                                    <BaseIconWithBadge
                                        :size="24"
                                        :path="mdiBellOutline"
                                        :notifications="unreadNotificationCount"
                                    />
                                </template>
                                <template #content>
                                    <div v-if="notifications.length > 0">
                                        <div
                                            id="toast-interactive"
                                            class="w-full right-4 top-10 z-50 max-w-xs p-4 text-gray-500 shadow dark:text-gray-400"
                                            role="alert"
                                        >
                                            <div
                                                id="toast-interactive"
                                                class="w-full right-4 top-10 z-50 max-w-xs p-2 text-gray-500 shadow dark:text-gray-400"
                                                role="alert"
                                            >
                                                <div
                                                    v-for="notification in notifications"
                                                    :key="notification.id"
                                                    :class="[
                                                        'mb-2 pr-2 rounded-lg flex items-center hover:dark:bg-slate-700',
                                                        {
                                                            'bg-gray-100 dark:bg-gray-700':
                                                                !notification.read_at,
                                                        },
                                                    ]"
                                                >
                                                    <button
                                                        type="button"
                                                        @click="
                                                            handleNotificationClick(
                                                                notification.id, notification.data.reservation
                                                            )
                                                        "
                                                        class="flex-grow text-left"
                                                    >
                                                        <div
                                                            class="ms-3 text-sm font-normal"
                                                        >
                                                            <div
                                                                class="flex items-center mb-1"
                                                            >
                                                                <span
                                                                    class="font-semibold text-gray-900 dark:text-white"
                                                                    >{{
                                                                        notification
                                                                            .data
                                                                            .title
                                                                    }}</span
                                                                >
                                                                <span
                                                                    v-if="
                                                                        !notification.read_at
                                                                    "
                                                                    class="ml-2 h-2 w-2 bg-blue-500 rounded-full"
                                                                ></span>
                                                            </div>
                                                            <div
                                                                class="text-sm font-normal"
                                                            >
                                                                {{
                                                                    notification
                                                                        .data
                                                                        .body
                                                                }}
                                                            </div>
                                                        </div>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="ms-auto -my-1.5 bg-white items-center justify-center flex-shrink-0 text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
                                                        data-dismiss-target="#toast-interactive"
                                                        aria-label="Close"
                                                        @click.prevent="
                                                            deleteNotif(
                                                                notification.id
                                                            )
                                                        "
                                                    >
                                                        <span class="sr-only"
                                                            >Close</span
                                                        >
                                                        <svg
                                                            class="w-3 h-3"
                                                            aria-hidden="true"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 14 14"
                                                        >
                                                            <path
                                                                stroke="currentColor"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                                                            />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 space-x-4">
                                            <span class="text-slate-500">
                                                {{
                                                    notifications.length > 0
                                                        ? "Clear Notifications"
                                                        : "You have 0 notifications"
                                                }}
                                            </span>
                                            <button
                                                type="button"
                                                class="ms-auto -my-1.5 bg-white items-center justify-center flex-shrink-0 text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
                                                data-dismiss-target="#toast-interactive"
                                                aria-label="Close"
                                                @click.prevent="
                                                    deleteAllNotif()
                                                "
                                            >
                                                <span class="sr-only"
                                                    >Close</span
                                                >
                                                <svg
                                                    class="w-3 h-3"
                                                    aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 14 14"
                                                >
                                                    <path
                                                        stroke="currentColor"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        v-else
                                        id="toast-interactive"
                                        class="w-full right-4 top-10 z-50 max-w-xs p-4 text-gray-500 shadow dark:text-gray-400"
                                        role="alert"
                                    >
                                        <CardBox class="w-full">
                                            <CardBoxComponentEmpty />
                                        </CardBox>
                                    </div>
                                </template>
                            </CardBoxNotifications>
                        </div>

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
            <main class="w-full py-6 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<!-- <style>
@media (min-width: 1024px) {
    aside {
        display: block !important;
    }
}
</style> -->
