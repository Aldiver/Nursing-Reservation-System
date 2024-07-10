<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import SectionMain from "@/Components/SectionMain.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import CardBox from "@/Components/CardBox.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import FormCheckRadioGroup from "@/Components/FormCheckRadioGroup.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { mdiClipboardList } from "@mdi/js";

const props = defineProps({
    department: Object,
});

const form = useForm({
    name: props.department.name,
});
</script>

<template>
    <div>
        <Head title="Edit Department" />
        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="mdiClipboardList"
                    title="Edit Department"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/admin/departments">Back</Link>
                    </PrimaryButton>
                </SectionTitleLineWithButton>

                <CardBox
                    is-form
                    @submit.prevent="
                        form.put(route('admin.departments.update', department))
                    "
                >
                    <FormField
                        label="Name"
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
