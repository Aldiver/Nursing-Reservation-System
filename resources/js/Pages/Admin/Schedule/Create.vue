<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    user: Object,
    department: Object,
    venues: Array,
});

const form = useForm({
    date: "",
    start_time: "",
    end_time: "",
    purpose: [],
    options: [],
    pax: {},
});

const otherPurpose = ref("");

// Check if an option is selected
function isOptionSelected(optionId) {
    return form.options.includes(optionId);
}

// Handle option change to ensure pax is properly managed
function handleOptionChange(option, event) {
    if (!event.target.checked && option.with_pax) {
        delete form.pax[option.id];
    }
}

// Watch form.options to ensure we update pax correctly
watch(
    () => form.options,
    (newOptions) => {
        for (const option of Object.keys(form.pax)) {
            if (!newOptions.includes(parseInt(option))) {
                delete form.pax[option];
            }
        }
    }
);

const purposeOptions = [
    "Class",
    "Meeting",
    "Demonstration/Return Demonstration",
];

watch(otherPurpose, (newValue) => {
    const othersIndex = form.purpose.findIndex((p) => p.startsWith("Others:"));
    if (newValue.trim() !== "") {
        if (othersIndex !== -1) {
            form.purpose.splice(othersIndex, 1, `Others: ${newValue.trim()}`);
        } else {
            form.purpose.push(`Others: ${newValue.trim()}`);
        }
    } else {
        if (othersIndex !== -1) {
            form.purpose.splice(othersIndex, 1);
        }
    }
});

const submit = () => {
    // console.log("Submitting form:", form);
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
const generateTimeOptions = (
    startHour,
    endHour,
    interval,
    forEndTime = false
) => {
    const options = [];
    for (let hour = startHour; hour < endHour; hour++) {
        for (let minute = 0; minute < 60; minute += interval) {
            const period = hour < 12 ? "AM" : "PM";
            const hourFormatted = hour % 12 || 12;
            const time = `${hourFormatted.toString().padStart(2, "0")}:${minute
                .toString()
                .padStart(2, "0")} ${period}`;
            options.push(time);
        }
    }
    if (forEndTime) {
        options.pop();
    }
    return options;
};

const getTimeRange = (forEndTime = false) => {
    const date = new Date(form.date);
    if (isWeekday(date)) {
        return forEndTime
            ? generateTimeOptions(7, 17, 30, forEndTime) // 7:00 AM to 4:00 PM for end time
            : generateTimeOptions(7, 16, 30); // 7:00 AM to 3:30 PM for start time
    } else if (isSaturday(date)) {
        return forEndTime
            ? generateTimeOptions(8, 13, 30) // 8:00 AM to 12:00 PM for end time
            : generateTimeOptions(8, 12, 30); // 8:00 AM to 11:30 AM for start time
    } else {
        return [];
    }
};

const startTimeOptions = computed(() => getTimeRange());
const endTimeOptions = computed(() => {
    if (!form.start_time) {
        return [];
    }

    const startHour =
        parseInt(form.start_time.split(":")[0], 10) +
        (form.start_time.includes("PM") ? 12 : 0);
    const startMinute = parseInt(form.start_time.split(":")[1], 10);

    return getTimeRange(true).filter((time) => {
        const endHour =
            parseInt(time.split(":")[0], 10) + (time.includes("PM") ? 12 : 0);
        const endMinute = parseInt(time.split(":")[1], 10);
        return (
            endHour > startHour ||
            (endHour === startHour && endMinute > startMinute)
        );
    });
});

watch(
    () => form.date,
    (newDate) => {
        form.start_time = "";
        form.end_time = "";
    }
);

watch(
    () => form.start_time,
    (newStartTime) => {
        if (form.end_time && form.end_time <= newStartTime) {
            form.end_time = "";
        }
    }
);
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
                            <div class="mt-4 flex space-x-4">
                                <div class="flex-1">
                                    <InputLabel
                                        for="start_time"
                                        value="Start Time"
                                    />
                                    <select
                                        id="start_time"
                                        class="mt-1 block w-full"
                                        v-model="form.start_time"
                                        :disabled="!form.date"
                                        required
                                    >
                                        <option value="" disabled>
                                            Select start time
                                        </option>
                                        <template
                                            v-for="time in startTimeOptions"
                                            :key="time"
                                        >
                                            <option :value="time">
                                                {{ time }}
                                            </option>
                                        </template>
                                    </select>
                                    <InputError
                                        :message="form.errors.start_time"
                                        class="mt-2"
                                    />
                                </div>

                                <div class="flex-1">
                                    <InputLabel
                                        for="end_time"
                                        value="End Time"
                                    />
                                    <select
                                        id="end_time"
                                        class="mt-1 block w-full"
                                        v-model="form.end_time"
                                        :disabled="!form.start_time"
                                        required
                                    >
                                        <option value="" disabled>
                                            Select end time
                                        </option>
                                        <template
                                            v-for="time in endTimeOptions"
                                            :key="time"
                                        >
                                            <option :value="time">
                                                {{ time }}
                                            </option>
                                        </template>
                                    </select>
                                    <InputError
                                        :message="form.errors.end_time"
                                        class="mt-2"
                                    />
                                </div>
                            </div>

                            <div class="mt-4">
                                <InputLabel for="purpose" value="Purpose" />
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2"
                                >
                                    <template
                                        v-for="(
                                            option, index
                                        ) in purposeOptions"
                                        :key="index"
                                    >
                                        <div>
                                            <input
                                                type="checkbox"
                                                :id="'purpose_' + index"
                                                :value="option"
                                                v-model="form.purpose"
                                                class="mr-2"
                                            />
                                            <label :for="'purpose_' + index">{{
                                                option
                                            }}</label>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        id="others"
                                        v-model="otherPurpose"
                                        placeholder="Others"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <InputError
                                    :message="form.errors.purpose"
                                    class="mt-2"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="venue_id" value="Venue" />
                                <div
                                    v-for="(venue, index) in props.venues"
                                    :key="index"
                                >
                                    <div class="mt-4 border rounded p-4">
                                        <h3 class="text-lg font-semibold">
                                            {{ venue.name }}
                                        </h3>
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2"
                                        >
                                            <div
                                                v-for="(
                                                    option, oIndex
                                                ) in venue.options"
                                                :key="oIndex"
                                            >
                                                <div>
                                                    <input
                                                        type="checkbox"
                                                        :id="
                                                            'option_' +
                                                            venue.id +
                                                            '_' +
                                                            oIndex
                                                        "
                                                        :value="option.id"
                                                        v-model="form.options"
                                                        class="mr-2"
                                                        @change="
                                                            handleOptionChange(
                                                                option,
                                                                $event
                                                            )
                                                        "
                                                    />
                                                    <label
                                                        :for="
                                                            'option_' +
                                                            venue.id +
                                                            '_' +
                                                            oIndex
                                                        "
                                                        >{{
                                                            option.name
                                                        }}</label
                                                    >
                                                    <div v-if="option.with_pax">
                                                        <input
                                                            type="number"
                                                            v-if="
                                                                isOptionSelected(
                                                                    option.id
                                                                )
                                                            "
                                                            v-model="
                                                                form.pax[
                                                                    option.id
                                                                ]
                                                            "
                                                            class="mt-2 ml-4 p-2 border rounded"
                                                            placeholder="Enter number of people"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
