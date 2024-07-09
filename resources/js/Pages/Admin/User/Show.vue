<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiCardAccountDetails } from "@mdi/js";
import NotificationBar from "@/Components/NotificationBar.vue";
import { formatDate, formatTime } from "@/helpers";
import BaseDivider from "@/Components/BaseDivider.vue";
import { computed } from "vue";

const props = defineProps({
    user: {
        type: Object,
        default: () => ({}),
    },
});

const userRole = computed(() => {
    return props.user.roles && props.user.roles.length > 0
        ? props.user.roles[0].name
        : "N/A";
});

const user_data = ref({
    id: props.user.id,
    Name: props.user.name,
    "Contact Number": props.user.contact_number || "N/A",
    "Email address": props.user.email,
    Department: props.user.department?.name ?? "N/A",
    "User Role": userRole,
});
</script>

<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiCardAccountDetails"
                title="User"
                main
            >
                <PrimaryButton class="ms-4">
                    <Link href="/users">Back</Link>
                </PrimaryButton>
            </SectionTitleLineWithButton>
            <NotificationBar
                v-if="$page.props.flash.message"
                color="success"
                :icon="mdiAlertBoxOutline"
            >
                {{ $page.props.flash.message }}
            </NotificationBar>
            <div class="py-12">
                <CardBox class="mb-6" has-table>
                    <label class="block font-bold p-4"> User Details</label>
                    <table>
                        <thead>
                            <tr>
                                <th class="w-1/4" />
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(value, index) in user_data">
                                <td>{{ index }}</td>
                                <td>{{ value }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <BaseDivider />
                    <template #footer>
                        <span class="dark:text-slate-700 text-slate-400">
                            Created at {{ formatDate(user.created_at) }}
                        </span>
                    </template>
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
