<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import { Head, Link } from "@inertiajs/vue3";
import TableComponent from "@/Components/TableComponent.vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiInvoiceTextClock } from "@mdi/js";

const props = defineProps({
    reservations: Array,
    columns: Array,
    permissions: Object,
});
</script>

<template>
    <Head title="Reservations" />

    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiInvoiceTextClock"
                title="Reservations"
                main
            >
                <PrimaryButton class="ms-4">
                    <Link href="/reservations/create">Add</Link>
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
                    <TableComponent
                        :checkable="true"
                        :columns="columns"
                        :rows="reservations"
                        :permissions="permissions"
                        delete_route="reservations.destroy"
                    />
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
