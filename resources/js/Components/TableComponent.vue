<script setup>
import { computed, ref } from "vue";
import CardBoxModal from "@/Components/CardBoxModal.vue";
import TableCheckboxCell from "@/Components/TableCheckboxCell.vue";
import BaseLevel from "@/Components/BaseLevel.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import { mdiTrashCan, mdiTextBoxEdit, mdiCheckCircleOutline } from "@mdi/js";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    checkable: Boolean,
    columns: Array,
    rows: Array,
    permissions: {
        type: Object,
        default: () => ({}),
    },
    delete_route: String,
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

const shouldDisplayNoteButton = (action, row) => {
    return action === "note" && props.permissions.note && !row["isNoted"];
};

const shouldDisplayApproveButton = (action, row) => {
    return (
        action === "approve" &&
        row["isNoted"] &&
        props.permissions.approve &&
        !row["isApproved"]
    );
};

const getCellContent = (action, row) => {
    if (action === "note") {
        if (!props.permissions.note && !row["isNoted"]) return "Pending";
        if (row["isNoted"]) return row["noted_by"];
    } else if (action === "approve") {
        if (!row["isNoted"]) return "Pending";
        if (!props.permissions.approve && !row["isApproved"]) return "Pending";
        if (row["isApproved"]) return row["approved_by"];
    }
    return "";
};

const userIdToDelete = ref(0);

const confirmDelete = (userId) => {
    userIdToDelete.value = userId;
    isModalDangerActive.value = true;
};

const formDelete = useForm({});
function destroy() {
    formDelete.delete(route(props.delete_route, userIdToDelete.value));
}
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
        @confirm="destroy"
    >
        <p>Are you sure you want to delete this item?</p>
    </CardBoxModal>

    <table>
        <thead>
            <tr>
                <th />
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
            <tr v-for="(row, index) in itemsPaginated" :key="row.id">
                <!-- <TableCheckboxCell
                    v-if="checkable"
                    @checked="checked($event, row)"
                /> -->
                <td>{{ index + 1 + currentPage * 5 }}</td>
                <td
                    v-for="column in columns"
                    :key="column.field"
                    :data-label="column.label"
                >
                    <template v-if="column.action">
                        <!-- Note Button Logic -->
                        <BaseButton
                            v-if="shouldDisplayNoteButton(column.action, row)"
                            :href="route(`reservation.note`, row.id)"
                            color="info"
                            :icon="mdiCheckCircleOutline"
                            small
                        />
                        <!-- Approve Button Logic -->
                        <BaseButton
                            v-if="
                                shouldDisplayApproveButton(column.action, row)
                            "
                            :href="route(`reservation.approve`, row.id)"
                            color="info"
                            :icon="mdiCheckCircleOutline"
                            small
                        />
                        <!-- Pending or Noted/Approved Display Logic -->
                        <span v-else>
                            {{ getCellContent(column.action, row) }}
                        </span>
                    </template>
                    <template v-else>
                        <span v-if="column.field != 'remarks'">
                            {{ row[column.field] }}
                        </span>
                        <span v-else>
                            <pre class="font-figtree">{{
                                row[column.field]
                            }}</pre>
                        </span>
                    </template>
                </td>
                <td
                    v-if="permissions.edit || permissions.delete"
                    class="before:hidden lg:w-1 whitespace-nowrap"
                >
                    <BaseButtons type="justify-start lg:justify-end" no-wrap>
                        <BaseButton
                            v-if="permissions.edit"
                            color="info"
                            :icon="mdiTextBoxEdit"
                            small
                            @click="isModalActive = true"
                        />
                        <BaseButton
                            v-if="permissions.delete"
                            color="danger"
                            :icon="mdiTrashCan"
                            small
                            @click="confirmDelete(row.id)"
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
<style scoped>
.font-figtree {
    font-family: "Figtree", ui-sans-serif;
}
</style>
