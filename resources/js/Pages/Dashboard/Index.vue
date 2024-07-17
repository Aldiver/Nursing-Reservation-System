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
import IndexStaff from "@/Components/IndexStaff.vue";

const styleStore = useStyleStore();

const props = defineProps({
    users: Array,
    columns: Array,
    permissions: Object,
    trendData: Object,
    incomingReservations: Object,
    reservations: Object,
    user_reservations_count: Object,
});

const UserType = ref("");

// Function to format time as required
const formatTime = (date, time) => {
    return date + " " + time.substring(0, 5); // Truncate seconds from time
};

const events = ref(
    props.reservations.map((reservation) => {
        const purposeData = reservation.purpose;
        const purposes = purposeData.purpose || [];
        const others = purposeData.others;

        // Combine purposes and others
        if (others) {
            purposes.push(`others: ${others}`);
        }

        return {
            title: purposes.join(", "),
            with: reservation.user.name,
            id: reservation.id,
            time: {
                start: formatTime(reservation.date, reservation.start_time),
                end: formatTime(reservation.date, reservation.end_time),
            },
            description: purposes.join(", "),
        };
    })
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
            <IndexAdmin v-if="!$page.props.auth.roles.includes('Staff')" :data="trendData" :incoming="incomingReservations" />
            <IndexStaff v-else :data="user_reservations_count"/>
            <SectionTitleLineWithButton
                :icon="mdiCalendarMonth"
                title="Calendar Schedules"
            >
                <slot />
            </SectionTitleLineWithButton>
            <CardBox
                
                class="calendar-container is-light-mode"
            >
                <Qalendar
                    class="bg-gray-200 text-slate-900"
                    :events="events"
                    :config="config"
                />
            </CardBox>
        </SectionMain>
    </AuthenticatedLayout>
</template>
