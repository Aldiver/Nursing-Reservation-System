<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardBox from "@/Components/CardBox.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import SectionMain from "@/Components/SectionMain.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

import { mdiOfficeBuildingMarkerOutline } from "@mdi/js";
const form = useForm({
    name: "",
    description: "",
});
</script>

<template>
    <div>
        <Head title="Create Venue" />
        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="mdiOfficeBuildingMarkerOutline"
                    title="Create Venue"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/admin/venues">Back</Link>
                    </PrimaryButton>
                </SectionTitleLineWithButton>
                <CardBox
                    is-form
                    @submit.prevent="form.post(route('admin.venues.store'))"
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
