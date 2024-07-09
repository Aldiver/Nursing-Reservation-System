<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardBox from "@/Components/CardBox.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import FormField from "@/Components/FormField.vue";
import SectionMain from "@/Components/SectionMain.vue";
import FormControl from "@/Components/FormControl.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    mdiOfficeBuildingMarkerOutline,
    mdiCloseThick,
    mdiPlus,
} from "@mdi/js";
import BaseIcon from "@/Components/BaseIcon.vue";
import CardBoxModal from "@/Components/CardBoxModal.vue";
import { ref } from "vue";
import FormCheckBox from "@/Components/FormCheckBox.vue";

const props = defineProps({
    venue: Object,
});

const showModal = ref(false);
const form = useForm({
    name: props.venue.name,
    description: props.venue.description,
    options: props.venue.options,
});

const _options = ref({
    name: "",
    max_pax: null,
    with_pax: false,
    errors: {
        name: "",
        max_pax: "",
    },
});

const add_options = () => {
    const { name, max_pax, with_pax } = { ..._options.value };

    // Validate option name
    if (name === "") {
        showModal.value = true;
        _options.value.errors.name = "Option name cannot be empty!";
        return;
    } else {
        _options.value.errors.name = "";
    }

    // Validate pax if with_pax is true
    if (with_pax && (max_pax === null || max_pax === "")) {
        _options.value.errors.max_pax = "Must input pax limit!";
        showModal.value = true;
        return;
    } else {
        _options.value.errors.max_pax = "";
    }

    const option = { name, max_pax, with_pax };

    // Check if an option with the same name already exists
    const existingOptionIndex = form.options.findIndex(
        (opt) => opt.name === option.name
    );

    if (existingOptionIndex > -1) {
        // Update existing option without affecting the errors property
        form.options[existingOptionIndex] = {
            ...form.options[existingOptionIndex],
            ...option,
        };
    } else {
        // If validations pass, push to form options
        form.options.push(option);
    }
    clear_options();
};

const clear_options = () => {
    _options.value.name = "";
    _options.value.pax = null;
    _options.value.with_pax = false;
    _options.value.max_pax = null;
};

const remove_options = (opt) => {
    const index = form.options.indexOf(opt);
    if (index > -1) {
        form.options.splice(index, 1); // Remove one item at the found index
    }
};
</script>

<template>
    <div>
        <Head title="Edit Venue" />
        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="mdiOfficeBuildingMarkerOutline"
                    title="Edit Venue"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/admin/venues">Back</Link>
                    </PrimaryButton>
                </SectionTitleLineWithButton>
                <CardBoxModal
                    v-model="showModal"
                    title="Add Option"
                    button="info"
                    has-cancel
                    @confirm="add_options"
                >
                    <FormField
                        label="Option name"
                        :class="{ 'text-red-400': _options.errors.name }"
                    >
                        <FormControl
                            v-model="_options.name"
                            type="text"
                            placeholder="Enter Option name"
                            :error="_options.errors.name"
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="_options.errors.name"
                            >
                                {{ _options.errors.name }}
                            </div>
                        </FormControl></FormField
                    >
                    <FormField label="With pax?" wrap-body>
                        <FormCheckBox
                            label="With Pax"
                            :check="_options.with_pax"
                            :checked="_options.with_pax"
                            @checked="(val) => (_options.with_pax = val)"
                        />
                    </FormField>
                    <FormField
                        v-if="_options.with_pax"
                        label="Maximum pax"
                        :class="{ 'text-red-400': _options.errors.max_pax }"
                    >
                        <FormControl
                            v-model="_options.max_pax"
                            type="number"
                            placeholder="Enter max number of pax"
                            :error="_options.errors.max_pax"
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="_options.errors.max_pax"
                            >
                                {{ _options.errors.max_pax }}
                            </div>
                        </FormControl></FormField
                    >
                </CardBoxModal>
                <CardBox
                    is-form
                    @submit.prevent="
                        form.patch(route('admin.venues.update', venue))
                    "
                >
                    <FormField
                        label="Venue name"
                        :class="{ 'text-red-400': form.errors.name }"
                    >
                        <FormControl
                            v-model="form.name"
                            type="text"
                            placeholder="Enter Name"
                            :error="form.errors.name"
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="form.errors.name"
                            >
                                {{ form.errors.name }}
                            </div>
                        </FormControl></FormField
                    >
                    <FormField
                        label="Location"
                        :class="{ 'text-red-400': form.errors.description }"
                    >
                        <FormControl
                            v-model="form.description"
                            type="text"
                            placeholder="Enter location descripton"
                            :error="form.errors.description"
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="form.errors.description"
                            >
                                {{ form.errors.description }}
                            </div>
                        </FormControl></FormField
                    >
                    <BaseDivider />
                    <FormField label="Options" class="flex-1 w-full"
                        ><slot
                    /></FormField>
                    <div class="grid grid-cols-2">
                        <!-- band aid -->
                        <div
                            v-for="(option, index) in form.options"
                            :key="index"
                            class="px-2 mb-4 flex-1 flex flex-row items-baseline space-x-4 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700"
                        >
                            <BaseIcon
                                :path="mdiCloseThick"
                                @click="remove_options(option)"
                            />
                            <label class="block font-bold w-full">
                                {{ option.name }}</label
                            >
                        </div>
                        <div
                            class="flex-1 mt-2 w-1/2 space-x-4 px-6 items-center rounded-xl"
                        >
                            <BaseButton
                                label="Add more option"
                                :icon="mdiPlus"
                                small
                                outline
                                roundedFull
                                color="darkWhite"
                                @click="showModal = !showModal"
                            />
                        </div>
                    </div>
                    <BaseDivider />
                    <template #footer>
                        <BaseButtons>
                            <BaseButton
                                type="submit"
                                color="whiteDark"
                                label="Update"
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
