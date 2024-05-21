<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    departments: Array,
});
</script>

<template>
    <div>
        <Head title="Departments" />
        <AuthenticatedLayout>
            <template #header>
                <div class="flex items-center justify-between">
                    <h2
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
                        Departments
                    </h2>
                    <PrimaryButton class="ms-4">
                        <Link href="/admin/departments/create">Add</Link>
                    </PrimaryButton>
                </div>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6 bg-white border-b border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Name
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="department in departments"
                                        :key="department.id"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ department.name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                        >
                                            <Link
                                                :href="`/admin/departments/${department.id}/edit`"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                >Edit</Link
                                            >
                                            <Link
                                                :href="`/admin/departments/${department.id}`"
                                                class="text-red-600 hover:text-red-900"
                                                method="delete"
                                                as="button"
                                                >Delete</Link
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
