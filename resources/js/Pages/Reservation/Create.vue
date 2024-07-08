<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, watch, computed, watchEffect } from "vue";
import axios from "axios";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import CardBox from "@/Components/CardBox.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import FormCheckRadioGroup from "@/Components/FormCheckRadioGroup.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { mdiInvoiceTextClock } from "@mdi/js";

const props = defineProps({
    user: Object,
    department: Object,
    venues: Array,
});

const form = useForm({
    date: "",
    start_time: "",
    end_time: "",
    purposeType: "",
    materials: "",
    purpose: [],
    otherPurpose: "",
    options: [],
    pax: {},
});

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
    forEndTime = false,
    startMinute = 0
) => {
    const options = [];
    for (let hour = startHour; hour < endHour; hour++) {
        for (let minute = startMinute; minute < 60; minute += interval) {
            const period = hour < 12 ? "AM" : "PM";
            const hourFormatted = hour % 12 || 12;
            const time = `${hourFormatted.toString().padStart(2, "0")}:${minute
                .toString()
                .padStart(2, "0")} ${period}`;
            options.push(time);
        }
        startMinute = 0; // Reset startMinute after the first iteration
    }
    if (forEndTime) {
        options.pop(); // Remove the last element for end time
    }
    return options;
};

const getTimeRange = (forEndTime = false, endTimeStart) => {
    const date = new Date(form.date);
    if (isWeekday(date)) {
        return forEndTime
            ? generateTimeOptions(endTimeStart, 17, 30, forEndTime) // 7:00 AM to 4:00 PM for end time
            : generateTimeOptions(7, 16, 30); // 7:00 AM to 3:30 PM for start time
    } else if (isSaturday(date)) {
        return forEndTime
            ? generateTimeOptions(endTimeStart, 14, 30, forEndTime) // 8:00 AM to 1:00 PM for end time
            : generateTimeOptions(8, 13, 30); // 8:00 AM to 12:30 PM for start time
    } else {
        return [];
    }
};

const startTimeOptions = computed(() => getTimeRange());
const endTimeOptions = computed(() => {
    if (!form.start_time) {
        return [];
    }

    // Parse selected start time
    const startHour =
        parseInt(form.start_time.split(":")[0], 10) +
        (form.start_time.includes("PM") ? 12 : 0);
    const startMinute = parseInt(form.start_time.split(":")[1], 10);

    // Filter end time options based on start time
    return getTimeRange(true, startHour).filter((time) => {
        const endHour =
            parseInt(time.split(":")[0], 10) + (time.includes("PM") ? 12 : 0);
        const endMinute = parseInt(time.split(":")[1], 10);

        // Ensure end time is after start time
        if (
            endHour > startHour ||
            (endHour === startHour && endMinute > startMinute)
        ) {
            return true;
        }
        return false;
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

// Function to fetch unavailable options from backend
const fetchUnavailableOptions = async () => {
    try {
        const response = await axios.get("/api/getUnavailableOptions", {
            params: {
                date: form.date,
                start_time: form.start_time,
                end_time: form.end_time,
            },
        });
        return response.data.unavailableOptions;
    } catch (error) {
        console.error("Error fetching unavailable options:", error);
        return [];
    }
};

const unavailableOptionsState = ref([]);

// Watch changes in date, start_time, end_time to fetch unavailable options
watchEffect(() => {
    fetchUnavailableOptions().then((unavailableOptions) => {
        console.log("Unavailable Options:", unavailableOptions);
        unavailableOptionsState.value = unavailableOptions;
        // Remove any options from form.options that are found in unavailableOptions
        for (const optionId of unavailableOptions) {
            if (form.options.includes(optionId)) {
                form.options = form.options.filter((id) => id !== optionId);
            }
        }
    });
});
</script>

<template>
    <div>
        <Head title="Create Reservation" />

        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="mdiInvoiceTextClock"
                    title="Create Reservation"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/reservations">Back</Link>
                    </PrimaryButton>
                </SectionTitleLineWithButton>

                <CardBox
                    is-form
                    @submit.prevent="form.post(route('reservations.store'))"
                >
                    <FormField
                        label="Date"
                        :class="{ 'text-red-400': form.errors.date }"
                    >
                        <FormControl
                            v-model="form.date"
                            type="date"
                            :error="form.errors.date"
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="form.errors.date"
                            >
                                {{ form.errors.date }}
                            </div>
                        </FormControl></FormField
                    >
                    <div class="mt-4 flex space-x-4">
                        <FormField
                            class="flex-1"
                            label="Start Time"
                            :class="{ 'text-red-400': form.errors.start_time }"
                        >
                            <FormControl
                                v-model="form.start_time"
                                :options="startTimeOptions"
                                :error="form.errors.start_time"
                                :disabled="!form.date"
                            >
                                <div
                                    class="text-red-400 text-sm"
                                    v-if="form.errors.start_time"
                                >
                                    {{ form.errors.start_time }}
                                </div>
                            </FormControl></FormField
                        >
                        <FormField
                            class="flex-1"
                            label="End Time"
                            :class="{ 'text-red-400': form.errors.end_time }"
                        >
                            <FormControl
                                v-model="form.end_time"
                                :options="endTimeOptions"
                                :error="form.errors.end_time"
                                :disabled="!form.start_time"
                            >
                                <div
                                    class="text-red-400 text-sm"
                                    v-if="form.errors.end_time"
                                >
                                    {{ form.errors.end_time }}
                                </div>
                            </FormControl></FormField
                        >
                    </div>
                    <FormField label="Purpose" wrap-body>
                        <FormCheckRadioGroup
                            v-model="form.purpose"
                            name="purpose"
                            :options="purposeOptions"
                        />
                    </FormField>
                    <FormField
                        label="Others"
                        :class="{ 'text-red-400': form.errors.otherPurpose }"
                    >
                        <FormControl
                            v-model="form.otherPurpose"
                            type="text"
                            placeholder="Others"
                            :error="form.errors.otherPurpose"
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="form.errors.otherPurpose"
                            >
                                {{ form.errors.otherPurpose }}
                            </div>
                        </FormControl></FormField
                    >
                    <FormField
                        v-if="form.purpose.includes(2)"
                        label="Procedure Type"
                        :class="{ 'text-red-400': form.errors.purposeType }"
                    >
                        <FormControl
                            v-model="form.purposeType"
                            type="text"
                            placeholder="Procedure Type"
                            :error="form.errors.purposeType"
                            required
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="form.errors.purposeType"
                            >
                                {{ form.errors.purposeType }}
                            </div>
                        </FormControl>
                    </FormField>
                    <FormField
                        v-if="form.purpose.includes(2)"
                        label="Materials"
                        :class="{ 'text-red-400': form.errors.materials }"
                    >
                        <FormControl
                            v-model="form.materials"
                            type="textarea"
                            placeholder="Materials"
                            :error="form.errors.materials"
                            required
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="form.errors.materials"
                            >
                                {{ form.errors.materials }}
                            </div>
                        </FormControl>
                    </FormField>
                    <div class="mt-4">
                        <h2 class="text-lg font-semibold mb-2">Venue</h2>
                        <FormField
                            v-for="(venue, index) in props.venues"
                            :key="index"
                            :label="venue.name"
                        >
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2"
                            >
                                <div
                                    v-for="(option, oIndex) in venue.options"
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
                                            :disabled="
                                                unavailableOptionsState.includes(
                                                    option.id
                                                )
                                            "
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
                                            :class="{
                                                'line-through text-red-500':
                                                    unavailableOptionsState.includes(
                                                        option.id
                                                    ),
                                            }"
                                        >
                                            {{ option.name }}
                                        </label>
                                        <div v-if="option.with_pax">
                                            <FormControl
                                                type="number"
                                                v-if="
                                                    isOptionSelected(option.id)
                                                "
                                                v-model="form.pax[option.id]"
                                                class="mt-2 ml-4 p-2"
                                                placeholder="Enter number of people"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </FormField>
                    </div>
                    <template #footer>
                        <BaseButtons>
                            <BaseButton
                                type="submit"
                                color="info"
                                label="Submit"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            />
                        </BaseButtons>
                    </template>
                </CardBox>
            </SectionMain>
        </AuthenticatedLayout>
    </div>
</template>
