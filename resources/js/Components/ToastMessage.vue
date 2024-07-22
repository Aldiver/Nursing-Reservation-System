<script setup>
import { ref, watch, computed } from "vue";

// Props
const props = defineProps({
    conflictReservations: {
        type: Array,
        required: true,
    },
    displayDate: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div>
        <transition enter-active-class="animate-slide-in" leave-active-class="animate-fade-out">
            <div class="z-50 w-full items-center space-x-4 rounded-lg shadow right-5" role="alert">
                <div id="toast-danger"
                    class="border border-gray-600 flex flex-col items-start w-full p-4 mb-4 text-gray-500 rounded-lg shadow dark:text-gray-400 dark:bg-gray-800"
                    role="alert">
                    <div class="flex items-center w-full">
                        <div
                            class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
                            </svg>
                            <span class="sr-only">Error icon</span>
                        </div>
                        <div class="ms-3 text-sm font-normal">
                            Conflict Schedule! {{ displayDate }}
                        </div>
                        <button type="button"
                            class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700">
                            <span class="sr-only">Close</span>
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                        </button>
                    </div>
                    <div class="w-full mt-3 text-sm font-normal">
                        <table class="conflict-table w-full">
                            <thead class="custom-table-header">
                                <tr>
                                    <th class="whitespace-nowrap">User ID</th>
                                    <!-- <th class="whitespace-nowrap">Schedule</th> -->
                                    <th class="whitespace-nowrap">Time In</th>
                                    <th class="whitespace-nowrap">Time Out</th>
                                    <th class="whitespace-nowrap">
                                        Conflict Options
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(
                                    reservation, index
                                ) in conflictReservations" :key="index">
                                    <td class="whitespace-nowrap">
                                        ID: {{ reservation.user_id }}
                                    </td>
                                    <!-- <td class="whitespace-nowrap">
                                    <span
                                        v-for="(value, index) in reservation
                                            .purpose.purpose"
                                        :key="index"
                                        >{{ value }}<br
                                    /></span>
                                </td> -->
                                    <td class="whitespace-nowrap">
                                        {{ reservation.start_time }}
                                    </td>
                                    <td class="whitespace-nowrap">
                                        {{ reservation.end_time }}
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <span v-for="option in reservation.options" :key="option.id">{{ option.name
                                            }}<br /></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
