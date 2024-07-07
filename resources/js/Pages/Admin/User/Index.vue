<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import { mdiCardAccountDetails, mdiAlertBoxOutline } from "@mdi/js";
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
                    :icon="mdiCardAccountDetails"
                    title="Users"
                    main
                >
                    <PrimaryButton class="ms-4">
                        <Link href="/admin/users/create">Add</Link>
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
                            :rows="users"
                            :permissions="permissions"
                            delete_route="admin.users.destroy"
                        />
                    </CardBox>
                </div>
            </SectionMain>
        </AuthenticatedLayout>
    </div>
</template>
