<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { Link } from "@inertiajs/vue3";
import CardBox from "@/Components/CardBox.vue";
import TableComponent from "@/Components/TableComponent.vue";
import { mdiInvoiceTextClock } from "@mdi/js";

const props = defineProps({
    reservations: Array,
    columns: Array,
    permissions: Object,
});
const controller_routes = {
    edit: "admin.schedules.edit",
    delete: "admin.schedules.delete",
};
</script>

<template>
    <div>
        <Head title="Schedules" />

        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="mdiInvoiceTextClock"
                    title="Schedules"
                    main
                >
                    <slot />
                </SectionTitleLineWithButton>

                <div class="py-12">
                    <CardBox class="mb-6">
                        <TableComponent
                            :checkable="true"
                            :columns="columns"
                            :rows="reservations"
                            :routes="controller_routes"
                        />
                    </CardBox>
                </div>
            </SectionMain>
        </AuthenticatedLayout>
    </div>
</template>
