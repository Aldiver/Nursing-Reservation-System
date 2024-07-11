<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiOfficeBuildingMarkerOutline } from "@mdi/js";
import NotificationBar from "@/Components/NotificationBar.vue";
import { formatDate } from "@/scripts/helpers";
import BaseDivider from "@/Components/BaseDivider.vue";

const props = defineProps({
    department: {
        type: Object,
        default: () => ({}),
    },
});

const { id, name, description } = props.department;

const department_data = ref({
    id,
    name,
});
</script>

<template>
    <Head title="Departments" />
    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiOfficeBuildingMarkerOutline"
                title="Department"
                main
            >
                <PrimaryButton class="ms-4">
                    <Link href="/admin/departments/">Back</Link>
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
                    <label class="block font-bold p-4">
                        Department Details</label
                    >
                    <table>
                        <thead>
                            <tr>
                                <th class="w-1/4" />
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(value, index) in department_data">
                                <td>{{ index }}</td>
                                <td>{{ value }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <BaseDivider />
                    <template #footer>
                        <span class="dark:text-slate-700 text-slate-400">
                            Created at {{ formatDate(department.created_at) }}
                        </span>
                    </template>
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
