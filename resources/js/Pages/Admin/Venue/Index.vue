<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { Head, Link } from "@inertiajs/vue3";
import TableComponent from "@/Components/TableComponent.vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiOfficeBuildingMarkerOutline } from "@mdi/js";
import NotificationBar from "@/Components/NotificationBar.vue";

const props = defineProps({
    venues: Array,
    columns: Array,
    permissions: Object,
});
const controller_routes = {
    edit: "admin.venues.edit",
    delete: "admin.venues.destroy",
    show: "admin.venues.show",
};
</script>

<template>
    <Head title="Venues" />
    <AuthenticatedLayout>
        <SectionMain>
            <SectionTitleLineWithButton
                :icon="mdiOfficeBuildingMarkerOutline"
                title="Venues"
                main
            >
                <PrimaryButton class="ms-4">
                    <Link href="/admin/venues/create">Add</Link>
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
                        :rows="venues"
                        :permissions="permissions"
                        :routes="controller_routes"
                    />
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
