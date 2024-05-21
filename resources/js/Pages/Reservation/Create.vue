<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    venues: Array,
    noters: Array,
    approvers: Array,
});

const form = useForm({
    date: "",
    start_time: "",
    end_time: "",
    number_of_participants: "",
    venue_id: "",
    noted_by: "",
    approved_by: "",
});

const submit = () => {
    form.post(route("reservations.store"));
};

const isWeekday = (date) => {
    const day = new Date(date).getDay();
    return day !== 0 && day !== 6; // Returns true for Mon-Fri
};

// Function to check if the selected date is Saturday
const isSaturday = (date) => {
    const day = new Date(date).getDay();
    return day === 6; // Returns true for Saturday
};

// Function to generate time options with specified start, end, and interval
const generateTimeOptions = (startHour, endHour, interval) => {
    const options = [];
    for (let hour = startHour; hour <= endHour; hour++) {
        for (let minute = 0; minute < 60; minute += interval) {
            const time = `${hour.toString().padStart(2, "0")}:${minute
                .toString()
                .padStart(2, "0")}`;
            options.push(time);
        }
    }
    return options;
};
</script>

<template>
    <div>
        <Head title="Create Reservation" />

        <AuthenticatedLayout>
            <template #header>
                <div class="flex items-center justify-between">
                    <h2
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
                        Create Reservation
                    </h2>
                </div>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="date" value="Date" />
                                <TextInput
                                    id="date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.date"
                                    required
                                />
                                <InputError
                                    :message="form.errors.date"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Start and End Time -->

                            <!-- End -->
                            <div class="mt-4">
                                <InputLabel
                                    for="number_of_participants"
                                    value="Number of Participants"
                                />
                                <TextInput
                                    id="number_of_participants"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.number_of_participants"
                                />
                                <InputError
                                    :message="
                                        form.errors.number_of_participants
                                    "
                                    class="mt-2"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="venue_id" value="Venue" />
                                <select
                                    id="venue_id"
                                    class="mt-1 block w-full"
                                    v-model="form.venue_id"
                                    required
                                >
                                    <option
                                        v-for="venue in props.venues"
                                        :key="venue.id"
                                        :value="venue.id"
                                    >
                                        {{ venue.name }}
                                    </option>
                                </select>
                                <InputError
                                    :message="form.errors.venue_id"
                                    class="mt-2"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="noted_by" value="Noted By" />
                                <select
                                    id="noted_by"
                                    class="mt-1 block w-full"
                                    v-model="form.noted_by"
                                >
                                    <option
                                        v-for="user in props.noters"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ user.name }}
                                    </option>
                                </select>
                                <InputError
                                    :message="form.errors.noted_by"
                                    class="mt-2"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel
                                    for="approved_by"
                                    value="Approved By"
                                />
                                <select
                                    id="approved_by"
                                    class="mt-1 block w-full"
                                    v-model="form.approved_by"
                                >
                                    <option
                                        v-for="user in props.approvers"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ user.name }}
                                    </option>
                                </select>
                                <InputError
                                    :message="form.errors.approved_by"
                                    class="mt-2"
                                />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton
                                    class="ms-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Create
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
