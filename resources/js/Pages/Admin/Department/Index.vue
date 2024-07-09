<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import { IdentificationIcon } from "@heroicons/vue/24/outline";
import { Head, Link } from "@inertiajs/vue3";
import TableComponent from "@/Components/TableComponent.vue";
import CardBox from "@/Components/CardBox.vue";
import { mdiClipboardList } from "@mdi/js";
const props = defineProps({
    departments: Array,
    columns: Array,
    permissions: Object,
});
const controller_routes = {
    edit: "admin.departments.edit",
    delete: "admin.departments.destroy",
    show: "admin.departments.show",
};
</script>

<template>
    <div>
        <Head title="Departments" />
        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="mdiClipboardList"
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
                            :routes="controller_routes"
                        />
                    </CardBox>
                </div>
            </SectionMain>
        </AuthenticatedLayout>
    </div>
</template>
