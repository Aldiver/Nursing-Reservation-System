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
    users: Array,
    columns: Array,
    permissions: Object,
});
</script>
<template>
    <div>
        <Head title="Users" />
        <AuthenticatedLayout>
            <SectionMain>
                <SectionTitleLineWithButton
                    :icon="IdentificationIcon"
                    title="Users"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/admin/users/create">Add</Link>
                    </PrimaryButton>
                </SectionTitleLineWithButton>
                <div class="py-12">
                    <CardBox class="mb-6" has-table>
                        <TableComponent
                            :checkable="true"
                            :columns="columns"
                            :rows="users"
                            :permissions="permissions"
                        />
                    </CardBox>
                </div>
            </SectionMain>
        </AuthenticatedLayout>
    </div>
</template>
