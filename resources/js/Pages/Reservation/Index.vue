<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import { Head, Link } from "@inertiajs/vue3";
import TableComponent from "@/Components/TableComponent.vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiListBox } from "@mdi/js";

const props = defineProps({
    reservations: Array,
    columns: Array,
    permissions: Object,
});
const controller_routes = {
    edit: "reservations.edit",
    delete: "reservations.destroy",
    show: "reservations.show",
};
</script>

<template>
    <Head title="Reservations" />

    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiListBox"
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
                        :routes="controller_routes"
                    />
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
