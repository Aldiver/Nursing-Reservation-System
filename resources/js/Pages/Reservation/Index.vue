<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import { Head, Link } from "@inertiajs/vue3";
import TableComponent from "@/Components/TableComponent.vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiListBox, mdiAlertBoxOutline, mdiSortBoolAscending } from "@mdi/js";
import { ref } from "vue";

const props = defineProps({
    reservations: Array,
    columns: Array,
    permissions: Object,
});

const filters = ref({
    status: [],
    sortByDateAsc: true,
});

const applyFilters = () => {
    // This function will be called when filters are changed
    // You need to send the updated filters to the server
};

const toggleDateSort = () => {
    filters.value.sortByDateAsc = !filters.value.sortByDateAsc;
    applyFilters();
};

const controller_routes = {
    edit: "reservations.edit",
    delete: "reservations.destroy",
    show: "reservations.show",
};
</script>

<template>
    <Head title="Reservations" />
    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiListBox" title="Reservations" main>
                <PrimaryButton class="ms-4">
                    <Link href="/reservations/create">Add</Link>
                </PrimaryButton>
            </SectionTitleLineWithButton>
            <NotificationBar v-if="$page.props.flash.message" color="success" :icon="mdiAlertBoxOutline">
                {{ $page.props.flash.message }}
            </NotificationBar>

            <div class="py-12">
                <CardBox class="mb-6">
                    <div class="flex flex-row space-x-4">
                        <details class="overflow-hidden rounded border w-1/3 border-gray-300 [&_summary::-webkit-details-marker]:hidden">
                            <summary class="flex cursor-pointer items-center justify-between gap-2 p-4 transition">
                                <span class="text-sm font-medium">Filter by Status</span>
                                <span class="transition group-open:-rotate-180">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </span>
                            </summary>
                            <div class="border-t">
                                <header class="flex items-center justify-between p-4">
                                    <span class="text-sm">{{ filters.status.length }} Selected</span>
                                    <button type="button" class="text-sm underline underline-offset-4" @click="filters.status = []; applyFilters();">Reset</button>
                                </header>
                                <ul class="space-y-1 border-t border-gray-200 p-4">
                                    <li>
                                        <label for="Approved" class="inline-flex items-center gap-2">
                                            <input type="checkbox" id="Approved" class="size-5 rounded border-gray-300" v-model="filters.status" value="Approved" @change="applyFilters()" />
                                            <span class="text-sm font-medium">Approved</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="Rejected" class="inline-flex items-center gap-2">
                                            <input type="checkbox" id="Rejected" class="size-5 rounded border-gray-300" v-model="filters.status" value="Rejected" @change="applyFilters()" />
                                            <span class="text-sm font-medium">Rejected</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label for="Pending" class="inline-flex items-center gap-2">
                                            <input type="checkbox" id="Pending" class="size-5 rounded border-gray-300" v-model="filters.status" value="Pending" @change="applyFilters()" />
                                            <span class="text-sm font-medium">Pending</span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </details>
                        <button @click="toggleDateSort" class="flex items-center space-x-2">
                            <span class="text-sm font-medium">Sort by Date</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="{ 'rotate-180': !filters.sortByDateAsc }" class="h-4 w-4 transition">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                    <TableComponent :checkable="true" :columns="columns" :rows="reservations" :permissions="permissions" :routes="controller_routes" />
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
