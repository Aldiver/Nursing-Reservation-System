<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import CardBox from "@/Components/CardBox.vue";
import FormField from "@/Components/FormField.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import FormControl from "@/Components/FormControl.vue";
import FormFilePicker from "@/Components/FormFilePicker.vue";
import { mdiAccount, mdiMail } from "@mdi/js";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <CardBox is-form @submit.prevent="form.put(route('profile.update'))">
        <!-- <FormField label="Avatar" help="Max 500kb">
            <FormFilePicker label="Upload" />
        </FormField> -->

        <FormField label="Name" help="Required. Your name">
            <FormControl
                v-model="form.name"
                :icon="mdiAccount"
                name="username"
                required
                autocomplete="username"
            />
        </FormField>
        <FormField label="E-mail" help="Required. Your e-mail">
            <FormControl
                v-model="form.email"
                :icon="mdiMail"
                type="email"
                name="email"
                required
                autocomplete="email"
            />
        </FormField>

        <!-- <template #footer>
            <BaseButtons>
                <BaseButton color="info" type="submit" label="Submit" />
                <BaseButton color="info" label="Options" outline />
            </BaseButtons>
        </template> -->
    </CardBox>
</template>
