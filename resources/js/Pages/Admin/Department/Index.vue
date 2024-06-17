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
    departments: Array,
    columns: Array,
    permissions: Object,
});
</script>

<template>
    <div>
        <Head title="Departments" />
        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="IdentificationIcon"
                    title="Departments"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/admin/departments/create">Add</Link>
                    </PrimaryButton>
                </SectionTitleLineWithButton>
                <div class="py-12">
                    <CardBox class="mb-6" has-table>
                        <TableComponent
                            :checkable="true"
                            :columns="columns"
                            :rows="departments"
                            :permissions="permissions"
                        />
                    </CardBox>
                </div>
            </SectionMain>
        </AuthenticatedLayout>
    </div>
</template>
