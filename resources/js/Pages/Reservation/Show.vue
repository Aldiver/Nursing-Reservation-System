<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiOfficeBuildingMarkerOutline } from "@mdi/js";
import NotificationBar from "@/Components/NotificationBar.vue";
import { formatDate, formatTime } from "@/scripts/helpers";
import BaseDivider from "@/Components/BaseDivider.vue";

const props = defineProps({
    reservation: {
        type: Object,
        default: () => ({}),
    },
});

const reservation_data = ref({
    id: props.reservation.id,
    "Reserved by": props.reservation.user.name,
    Schedule: `${formatDate(props.reservation.date)} ${formatTime(
        props.reservation.start_time
    )} - ${formatTime(props.reservation.end_time)}`,
    Options: props.reservation.options,
    Purpose: [
        ...props.reservation.purpose.purpose,
        props.reservation.purpose.others,
    ].join(", "),
    Remarks: props.reservation.remarks || "N/A",
    Status: props.reservation.isApproved ? "Approved" : "Pending",
    "Noted by": props.reservation.noter?.name ?? "Pending",
    "Approved by": props.reservation.approver?.name ?? "Pending",
});
</script>

<template>
    <Head title="Reservations" />
    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiOfficeBuildingMarkerOutline"
                title="Reservation"
                main
            >
                <PrimaryButton class="ms-4">
                    <Link href="/reservations">Back</Link>
                </PrimaryButton>
            </SectionTitleLineWithButton>
            <NotificationBar
                v-if="$page.props.flash.message"
                color="success"
                :icon="mdiAlertBoxOutline"
            >
                {{ $page.props.flash.message }}
            </NotificationBar>
            <div class="py-12">
                <CardBox class="mb-6" has-table>
                    <label class="block font-bold p-4">
                        Reservation Details</label
                    >
                    <table>
                        <thead>
                            <tr>
                                <th class="w-1/4" />
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(value, index) in reservation_data">
                                <td>{{ index }}</td>
                                <td v-if="Array.isArray(value)">
                                    <span
                                        v-for="option in value"
                                        :key="option.id"
                                    >
                                        {{ option.name }}
                                        <span v-if="option.with_pax">
                                            | (Number of pax
                                            {{ option.pivot.pax }})</span
                                        >
                                        <br />
                                    </span>
                                </td>
                                <td v-else>{{ value }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <BaseDivider />
                    <template #footer>
                        <span class="dark:text-slate-700 text-slate-400">
                            Created at {{ formatDate(reservation.created_at) }}
                        </span>
                    </template>
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
