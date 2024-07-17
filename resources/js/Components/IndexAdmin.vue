<script setup>
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBoxTransaction from "./CardBoxTransaction.vue";
import {
    mdiViewDashboardEditOutline,
    mdiTagCheckOutline,
    mdiHistory,
    mdiPercentOutline,
    mdiFormatListNumbered,
} from "@mdi/js";

const props = defineProps({
    data: Object,
    incoming: Object,
});

const {
    pendingApprovalCount,
    pendingApprovalTrend,
    recentReservationsCount,
    recentReservationsTrend,
    utilizationRate,
    utilizationRateTrend,
} = props.data;
</script>

<template>
    <SectionTitleLineWithButton
        :icon="mdiViewDashboardEditOutline"
        title="Overview"
        main
    >
        Weekly Trends
    </SectionTitleLineWithButton>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <CardBoxWidget
            :trend="`${pendingApprovalTrend.value} %`"
            :trend-type="pendingApprovalTrend.type"
            color="text-red-500"
            :icon="mdiTagCheckOutline"
            :number="pendingApprovalCount"
            label="Pending Approval"
            to=""
        />
        <CardBoxWidget
            :trend="`${recentReservationsTrend.value} %`"
            :trend-type="recentReservationsTrend.type"
            color="text-green-500"
            :icon="mdiHistory"
            :number="recentReservationsCount"
            label="Recent Reservations"
            to=""
        />
        <CardBoxWidget
            :trend="`${utilizationRateTrend.value} %`"
            :trend-type="utilizationRateTrend.type"
            color="text-blue-500"
            :icon="mdiPercentOutline"
            :number="utilizationRate"
            label="Venue Utilization"
            suffix=" %"
            to=""
        />
    </div>

    <SectionTitleLineWithButton
        :icon="mdiFormatListNumbered"
        title="Upcoming Reservations"
        main
    >
        <slot />
    </SectionTitleLineWithButton>

    <div class="flex flex-col justify-between">
        <CardBoxTransaction
            v-for="(reservation, index) in incoming"
            :key="index"
            :amount="reservation.user.name"
            :date="reservation.date"
            :business="reservation.approver.name"
            :type="`${reservation.start_time} - ${reservation.end_time}`"
            :name="reservation.user.email"
            :account="reservation.user.name"
        />
    </div>
</template>
