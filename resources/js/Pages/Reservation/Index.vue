<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import TableComponent from "@/Components/TableComponent.vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiListBox, mdiAlertBoxOutline, mdiSortClockAscendingOutline } from "@mdi/js";
import { ref, watch } from "vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";

const props = defineProps({
    reservations: Array,
    columns: Array,
    permissions: Object,
});

const showFilters = ref(false);

const filterOptions = [
    "Approved",
    "Rejected",
    "Pending",
];

const form = useForm({
    filterByDate: "",
    status: [],
    sortByTimeStart: false,
});

const applyFilters = () => {
    form.get(route('reservations.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['reservations'],
    });
};

const toggleTimeSort = () => {
    form.sortByTimeStart = !form.sortByTimeStart;
    applyFilters();
};

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const controller_routes = {
    edit: "reservations.edit",
    delete: "reservations.destroy",
    show: "reservations.show",
};

watch(
    () => form.date,
    (newDate) => {
        applyFilters();
    }
);
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
                <CardBox is-form class="mb-6">
                    <div class="flex space-x-4 align-top">
                        <button @click.prevent="toggleFilters" class="h-10 flex items-center space-x-2">
                            <span class="text-sm font-medium">Filters</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="h-4 w-4 transition">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18m-7 6h7" />
                            </svg>
                        </button>
                        <transition name="fade">
                            <div v-if="showFilters" class="space-x-4 align-top flex w-full">
                                <details
                                    class="overflow-hidden rounded border w-1/3 border-gray-300 [&_summary::-webkit-details-marker]:hidden">
                                    <summary
                                        class="flex cursor-pointer items-center justify-between gap-2 p-4 transition">
                                        <span class="text-sm font-medium">Filter by Status</span>
                                        <span class="transition group-open:-rotate-180">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </span>
                                    </summary>
                                    <div class="border-t">
                                        <header class="flex items-center justify-between p-4">
                                            <span class="text-sm">{{ form.status.length }} Selected</span>
                                            <button type="button" class="text-sm underline underline-offset-4"
                                                @click="() => { form.status = []; applyFilters(); }">
                                                Reset
                                            </button>
                                        </header>
                                        <ul class="space-y-1 border-t border-gray-200 p-4">
                                            <li v-for="filter in filterOptions" :key="filter">
                                                <label :for="filter" class="inline-flex items-center gap-2">
                                                    <input type="checkbox" :id="filter"
                                                        class="size-5 rounded border-gray-300" v-model="form.status"
                                                        :value="filter" @change="applyFilters" />
                                                    <span class="text-sm font-medium">{{ filter }}</span>
                                                </label>
                                            </li>
                                        </ul>
                                    </div>
                                </details>


                                <FormField class="h-12 overflow-hidden rounded border border-gray-300">
                                    <FormControl v-model="form.filterByDate" type="date"
                                        :error="form.errors.filterByDate" @change="applyFilters" />
                                </FormField>

                                <button @click.prevent="toggleTimeSort" class="h-10 flex items-center space-x-2">
                                    <span class="text-sm font-medium">Sort by Time</span>
                                    <!-- <BaseIcon :size="30":path="mdiSortClockAscendingOutline" :class="{ 'rotate-180': !form.sortByTimeStart }" /> -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        :class="{ 'rotate-180': !form.sortByTimeStart }" class="h-4 w-4 transition">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                        </transition>
                    </div>
                    <TableComponent :checkable="true" :columns="columns" :rows="reservations" :permissions="permissions"
                        :routes="controller_routes" />
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
