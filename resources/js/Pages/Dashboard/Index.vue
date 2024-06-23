<script setup>
import { useStyleStore } from "@/stores/style.js";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import IndexAdmin from "@/Components/IndexAdmin.vue";
import { Qalendar } from "qalendar";
import CardBox from "@/Components/CardBox.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import { mdiCalendarMonth } from "@mdi/js";

const styleStore = useStyleStore();

const props = defineProps({
    users: Array,
    columns: Array,
    permissions: Object,
    trendData: Object,
    incomingReservations: Object,
    reservations: Object,
});

// Function to format time as required
const formatTime = (date, time) => {
    return date + " " + time.substring(0, 5); // Truncate seconds from time
};

const events = ref(
    props.reservations.map((reservation) => ({
        title: reservation.purpose.join(", "),
        with: reservation.user.name,
        id: reservation.id,
        time: {
            start: formatTime(reservation.date, reservation.start_time),
            end: formatTime(reservation.date, reservation.end_time),
        },
        description: reservation.purpose.toString(),
    }))
);

const config = ref({
    defaultMode: "month", // Set the default view to Month
    week: {
        startsOn: "sunday",
        nDays: 7,
        scrollToHour: 8,
    },
    locale: "en-PH", // Set locale to Philippines
    isSilent: true,
    month: {
        showTrailingAndLeadingDates: false,
    },
    showCurrentTime: true,
});
</script>

<style>
@import "qalendar/dist/style.css";
</style>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <SectionMain>
            <IndexAdmin :data="trendData" :incoming="incomingReservations" />
            <SectionTitleLineWithButton
                :icon="mdiCalendarMonth"
                title="Calendar Schedules"
            >
                <slot />
            </SectionTitleLineWithButton>
            <CardBox class="mb-6 calendar-container is-light-mode">
                <Qalendar :events="events" :config="config" />
            </CardBox>
        </SectionMain>
    </AuthenticatedLayout>
</template>
