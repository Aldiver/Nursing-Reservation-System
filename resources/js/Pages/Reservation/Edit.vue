<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, watch, computed, watchEffect, onMounted } from "vue";
import axios from "axios";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import CardBox from "@/Components/CardBox.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import FormField from "@/Components/FormField.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import FormControl from "@/Components/FormControl.vue";
import FormCheckRadioGroup from "@/Components/FormCheckRadioGroup.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { mdiInvoiceTextClock, mdiAlertBoxOutline } from "@mdi/js";

const props = defineProps({
    reservation: Object,
    venues: Array,
});

const formatTime12h = (time24h) => {
    const [hours, minutes] = time24h.split(":");
    let period = "AM";
    let hours12 = parseInt(hours, 10);

    if (hours12 >= 12) {
        period = "PM";
        if (hours12 > 12) {
            hours12 -= 12;
        }
    }
    if (hours12 === 0) {
        hours12 = 12; // 12 AM
    }

    // Pad single-digit hours with leading zero if needed
    const formattedHours = hours12.toString().padStart(2, "0");

    return `${formattedHours}:${minutes} ${period}`;
};

const form = useForm({
    date: props.reservation.date,
    start_time: formatTime12h(props.reservation.start_time),
    end_time: "",
    purposeType: "",
    materials: "",
    purpose: Object.keys(props.reservation.purpose.purpose).map((key) =>
        parseInt(key)
    ),
    otherPurpose: props.reservation.purpose.others,
    options: [],
    pax: Object.fromEntries(
        props.reservation.options.map((option) => [
            String(option.id),
            option.pivot.pax,
        ])
    ),
});

onMounted(() => {
    form.end_time = formatTime12h(props.reservation.end_time);
    if (props.reservation.remarks) {
        const [string1, string2] = props.reservation.remarks.split("\n\n");

        const extractText = (str) => str.split(":")[1]?.trim() || str.trim();

        form.purposeType = extractText(string1);
        form.materials = extractText(string2);
    }
    form.options = props.reservation.options.map((option) => {
        const { pivot, ...rest } = option;
        return rest;
    });
    // fetchUnavailableOptions;
});

// Check if an option is selected
function isOptionSelected(optionId) {
    // console.log("Checking if option is selected:", optionId, form.options); // Debug log
    return form.options.some((option) => option.id === optionId);
}

// Handle option change to ensure pax is properly managed
function handleOptionChange(option, event) {
    if (!event.target.checked && option.with_pax) {
        delete form.pax[option.id];
    }
}

// Watch form.options to ensure we update pax correctly
watch(
    () => form.pax,
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
    for (let hour = startHour; hour <= endHour; hour++) {
        for (let minute = startMinute; minute < 60; minute += interval) {
            if (hour === endHour && minute >= startMinute) break;
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

const getTimeRange = (date, forEndTime = false, endTimeStart = null) => {
    if (isWeekday(date)) {
        return forEndTime
            ? generateTimeOptions(endTimeStart, 17, 30, forEndTime)
            : generateTimeOptions(7, 16, 30); // 7:00 AM to 3:30 PM for start time
    } else if (isSaturday(date)) {
        return forEndTime
            ? generateTimeOptions(endTimeStart, 13, 30, forEndTime)
            : generateTimeOptions(8, 12, 30); // 8:00 AM to 11:30 AM for start time
    } else {
        return [];
    }
};

const startTimeOptions = computed(() => getTimeRange(form.date));
const endTimeOptions = computed(() => {
    if (!form.start_time) {
        return [];
    }

    // Parse selected start time
    const [startHourString, startMinuteString] = form.start_time.split(/:| /);
    let startHour = parseInt(startHourString, 10);
    const startMinute = parseInt(startMinuteString, 10);
    if (form.start_time.includes("PM") && startHour !== 12) {
        startHour += 12;
    } else if (form.start_time.includes("AM") && startHour === 12) {
        startHour = 0;
    }

    // Filter end time options based on start time
    return getTimeRange(form.date, true, startHour).filter((time) => {
        const [endHourString, endMinuteString] = time.split(/:| /);
        let endHour = parseInt(endHourString, 10);
        const endMinute = parseInt(endMinuteString, 10);
        if (time.includes("PM") && endHour !== 12) {
            endHour += 12;
        } else if (time.includes("AM") && endHour === 12) {
            endHour = 0;
        }

        // Ensure end time is after start time
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
        form.options = [];
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
                currentReservationId: props.reservation.id,
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
        form.options = form.options.filter(option => !unavailableOptions.includes(option.id));
    });
});
</script>

<template>
    <div>
        <Head title="Edit Reservation" />

        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="mdiInvoiceTextClock"
                    title="Edit Reservation"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/reservations">Back</Link>
                    </PrimaryButton>
                </SectionTitleLineWithButton>
                <NotificationBar
                    v-if="$page.props.flash.message || $page.props.flash.error"
                    :color="$page.props.flash.message ? 'success' : 'danger'"
                    :icon="mdiAlertBoxOutline"
                >
                    {{ $page.props.flash.message ?? $page.props.flash.error }}
                </NotificationBar>
                <CardBox
                    is-form
                    @submit.prevent="
                        form.put(route('reservations.update', reservation))
                    "
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
                            </FormControl>
                        </FormField>
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
                            </FormControl>
                        </FormField>
                    </div>
                    <FormField
                        label="Purpose"
                        :class="{ 'text-red-400': form.errors.purpose }"
                        wrap-body
                    >
                        <FormCheckRadioGroup
                            v-model="form.purpose"
                            name="purpose"
                            :options="purposeOptions"
                        />
                        <div
                            class="text-red-400 text-sm"
                            v-if="form.errors.purpose"
                        >
                            {{ form.errors.purpose }}
                        </div>
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
                        <h2
                            class="text-lg font-semibold mb-2"
                            :class="{ 'text-red-400': form.errors.options }"
                        >
                            Venue
                        </h2>
                        <div
                            class="text-red-400 text-sm flex-1"
                            v-if="form.errors.options"
                        >
                            {{ form.errors.options }}
                        </div>
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
                                            :value="option"
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
                                                :max="option.max_pax"
                                                v-model="form.pax[option.id]"
                                                class="mt-2 ml-4 p-2"
                                                placeholder="Enter number of people"
                                            />
                                            <span
                                                class="ml-6 text-xs text-gray-500 dark:text-slate-400 mt-1"
                                            >
                                                Maximum pax is
                                                {{ option.max_pax }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </FormField>
                    </div>
                    <BaseDivider />
                    <template #footer>
                        <BaseButtons>
                            <BaseButton
                                type="submit"
                                color="whiteDark"
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
