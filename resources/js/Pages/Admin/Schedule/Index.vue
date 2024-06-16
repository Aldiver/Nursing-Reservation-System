<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { Qalendar } from "qalendar";

const props = defineProps({
    reservations: Array,
});

// Function to format time as required
const formatTime = (date, time) => {
    return date + " " + time.substring(0, 5); // Truncate seconds from time
};

// const events = ref(
//     props.reservations.map((reservation) => ({
//         title: reservation.purpose.join(", "),
//         with: reservation.user.name,
//         id: reservation.id,
//         time: {
//             start: formatTime(reservation.date, reservation.start_time),
//             end: formatTime(reservation.date, reservation.end_time),
//         },
//         description: reservation.purpose.toString(),
//     }))
// );

const events = [
    {
        title: "Some Event",
        with: "Al Nabigh",
        time: {
            start: "2024-06-14 12:05",
            end: "2024-06-14 13:35",
        },
        color: "yellow",
        // isEditable: true,
        id: "753944708f0f",
        description: "",
    },
];

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
    <div>
        <Head title="Schedules" />

        <AuthenticatedLayout>
            <template #header>
                <div class="flex items-center justify-between">
                    <h2
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
                        Schedules
                    </h2>
                    <PrimaryButton>
                        <Link href="/reservations/create">Add Reservation</Link>
                    </PrimaryButton>
                </div>
            </template>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div
                            class="calendar-container is-light-mode p-6 bg-white border-b border-gray-200"
                        >
                            <Qalendar :events="events" :config="config" />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
