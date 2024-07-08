<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import SectionMain from "@/Components/SectionMain.vue";
import CardBox from "@/Components/CardBox.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import FormCheckRadioGroup from "@/Components/FormCheckRadioGroup.vue";
import BaseDivider from "@/Components/BaseDivider.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";

import { mdiCardAccountDetails } from "@mdi/js";

const props = defineProps({
    user: Object,
    roles: {
        type: Object,
        default: () => ({}),
    },
    permissions: {
        type: Object,
        default: () => ({}),
    },
    departments: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    contact_number: props.user.contact_number,
    role: props.user.roles[0].label,
    permissions: Object.keys(props.user.permissions),
    department: props.user.department.label,
});
</script>

<template>
    <div>
        <Head title="Create User" />
        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="mdiCardAccountDetails"
                    title="Create Users"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/admin/users">Back</Link>
                    </PrimaryButton>
                </SectionTitleLineWithButton>

                <CardBox
                    is-form
                    @submit.prevent="
                        form.patch(route('admin.users.update', user))
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
                    <FormField
                        label="Contact"
                        :class="{ 'text-red-400': form.errors.contact_number }"
                    >
                        <FormControl
                            v-model="form.contact_number"
                            type="text"
                            placeholder="Enter Contact Number"
                            :error="form.errors.contact_number"
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="form.errors.contact_number"
                            >
                                {{ form.errors.contact_number }}
                            </div>
                        </FormControl></FormField
                    >
                    <FormField
                        label="Email (Not Editable)"
                        :class="{ 'text-red-400': form.errors.email }"
                    >
                        <FormControl
                            v-model="form.email"
                            type="text"
                            placeholder="Enter Email"
                            :error="form.errors.email"
                            :disabled="true"
                        >
                            <div
                                class="text-red-400 text-sm"
                                v-if="form.errors.email"
                            >
                                {{ form.errors.email }}
                            </div>
                        </FormControl></FormField
                    >
                    <FormField
                        label="Department"
                        :class="{ 'text-red-400': form.errors.department }"
                    >
                        <FormControl
                            v-model="form.department"
                            :options="props.departments"
                        />

                        <div
                            class="text-red-400 text-sm"
                            v-if="form.errors.department"
                        >
                            {{ form.errors.department }}
                        </div>
                    </FormField>

                    <BaseDivider />
                    <FormField label="Roles">
                        <FormControl
                            v-model="form.role"
                            :options="props.roles"
                        />
                    </FormField>
                    <FormField label="Permissions" wrap-body>
                        <FormCheckRadioGroup
                            v-model="form.permissions"
                            name="roles"
                            :options="props.permissions"
                        />
                    </FormField>
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
