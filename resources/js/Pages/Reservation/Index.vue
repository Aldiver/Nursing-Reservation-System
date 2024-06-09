<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    reservations: Array,
});
</script>

<template>
    <div>
        <Head title="Reservations" />

        <AuthenticatedLayout>
            <template #header>
                <div class="flex items-center justify-between">
                    <h2
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
                        Reservations
                    </h2>
                    <PrimaryButton>
                        <Link href="/reservations/create">Add Reservation</Link>
                    </PrimaryButton>
                </div>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div
                        class="bg-white overflow-x-auto shadow-sm sm:rounded-lg p-6"
                    >
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Start Time
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        End Time
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Purpose
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Options
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Noted By
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Approved By
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="reservation in props.reservations"
                                    :key="reservation.id"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ reservation.date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ reservation.start_time }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ reservation.end_time }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <ul>
                                            <template
                                                v-if="
                                                    reservation.purpose &&
                                                    reservation.purpose.length
                                                "
                                            >
                                                <li
                                                    v-for="(
                                                        purpose, index
                                                    ) in reservation.purpose"
                                                    :key="index"
                                                >
                                                    {{ purpose }}
                                                </li>
                                            </template>
                                            <template v-else>
                                                <li>-</li>
                                            </template>
                                        </ul>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <ul>
                                            <li
                                                v-for="option in reservation.options"
                                                :key="option.id"
                                            >
                                                {{ option.name }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{
                                            reservation.noter
                                                ? reservation.noter.name
                                                : "N/A"
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{
                                            reservation.approver
                                                ? reservation.approver.name
                                                : "N/A"
                                        }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <!-- Add buttons for approving, declining, etc. -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
