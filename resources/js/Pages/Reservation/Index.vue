<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { IdentificationIcon } from "@heroicons/vue/24/outline";
import { Head, Link } from "@inertiajs/vue3";
import TableComponent from "@/Components/TableComponent.vue";
import CardBox from "@/Components/CardBox.vue";

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
                :icon="IdentificationIcon"
                title="Venues"
                main
            >
                <PrimaryButton class="ms-4">
                    <Link href="/reservations/create">Add</Link>
                </PrimaryButton>
            </SectionTitleLineWithButton>
            <div class="py-12">
                <CardBox class="mb-6" has-table>
                    <TableComponent
                        :checkable="true"
                        :columns="columns"
                        :rows="reservations"
                    />
                </CardBox>
            </div>
        </SectionMain>
    </AuthenticatedLayout>
</template>
