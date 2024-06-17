<script setup>
import { computed, ref } from "vue";
import CardBoxModal from "@/Components/CardBoxModal.vue";
import TableCheckboxCell from "@/Components/TableCheckboxCell.vue";
import BaseLevel from "@/Components/BaseLevel.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { EyeIcon, TrashIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    checkable: Boolean,
    columns: Array,
    rows: Array,
    permissions: {
        type: Object,
        default: () => ({}),
    },
});

const isModalActive = ref(false);
const isModalDangerActive = ref(false);
const perPage = ref(5);
const currentPage = ref(0);
const checkedRows = ref([]);

const itemsPaginated = computed(() =>
    props.rows.slice(
        perPage.value * currentPage.value,
        perPage.value * (currentPage.value + 1)
    )
);

const numPages = computed(() => Math.ceil(props.rows.length / perPage.value));
const currentPageHuman = computed(() => currentPage.value + 1);

const pagesList = computed(() => {
    const pages = [];
    for (let i = 0; i < numPages.value; i++) {
        pages.push(i);
    }
    return pages;
});

const remove = (arr, cb) => {
    return arr.filter((item) => !cb(item));
};

const checked = (isChecked, row) => {
    if (isChecked) {
        checkedRows.value.push(row);
    } else {
        checkedRows.value = remove(
            checkedRows.value,
            (checkedRow) => checkedRow.id === row.id
        );
    }
};
</script>

<template>
    <CardBoxModal v-model="isModalActive" title="Edit Row">
        <!-- Add form for editing row here -->
        <p>Editing row content here</p>
    </CardBoxModal>

    <CardBoxModal
        v-model="isModalDangerActive"
        title="Please confirm"
        button="danger"
        has-cancel
    >
        <p>Are you sure you want to delete this item?</p>
    </CardBoxModal>

    <table>
        <thead>
            <tr>
                <th v-if="checkable" />
                <th
                    v-for="column in columns"
                    :key="column.field"
                    :class="column.class"
                >
                    {{ column.label }}
                </th>
                <th v-if="permissions.edit || permissions.delete" />
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in itemsPaginated" :key="row.id">
                <TableCheckboxCell
                    v-if="checkable"
                    @checked="checked($event, row)"
                />
                <td
                    v-for="column in columns"
                    :key="column.field"
                    :data-label="column.label"
                >
                    {{ row[column.field] }}
                </td>
                <td
                    v-if="permissions.edit || permissions.delete"
                    class="before:hidden lg:w-1 whitespace-nowrap"
                >
                    <BaseButtons type="justify-start lg:justify-end" no-wrap>
                        <BaseButton
                            v-if="permissions.edit"
                            color="info"
                            :icon="EyeIcon"
                            small
                            @click="isModalActive = true"
                        />
                        <BaseButton
                            v-if="permissions.delete"
                            color="danger"
                            :icon="TrashIcon"
                            small
                            @click="isModalDangerActive = true"
                        />
                    </BaseButtons>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
        <BaseLevel>
            <BaseButtons>
                <BaseButton
                    v-for="page in pagesList"
                    :key="page"
                    :active="page === currentPage"
                    :label="page + 1"
                    small
                    @click="currentPage = page"
                />
            </BaseButtons>
            <small>Page {{ currentPageHuman }} of {{ numPages }}</small>
        </BaseLevel>
    </div>
</template>
