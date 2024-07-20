<script setup>
import { computed, ref } from "vue";
import CardBoxModal from "@/Components/CardBoxModal.vue";
import BaseLevel from "@/Components/BaseLevel.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import BaseButton from "@/Components/BaseButton.vue";
import {
    mdiTrashCan,
    mdiTextBoxEdit,
    mdiCheckCircleOutline,
    mdiAlert,
    mdiEyeCircle,
    mdiAlphaXCircleOutline,
} from "@mdi/js";
import { useForm } from "@inertiajs/vue3";
import IconRounded from "./IconRounded.vue";
import NotificationBar from "./NotificationBar.vue";
import CardBoxComponentEmpty from "./CardBoxComponentEmpty.vue";
import CardBox from "./CardBox.vue";
import PillTag from "./PillTag.vue";

const props = defineProps({
    checkable: Boolean,
    columns: Array,
    rows: Array,
    permissions: {
        type: Object,
        default: () => ({}),
    },
    routes: {
        type: Object,
        default: () => ({}),
    },
});

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
const buttonToDisplay = (action, row) => {
    switch (action) {
        case "note":
            return (
                action === "note" && props.permissions.note && !row["isNoted"]
            );
        case "approve":
            return (
                action === "approve" &&
                row["isNoted"] &&
                props.permissions.approve &&
                !row["isApproved"]
            );
        case "default":
            break;
    }
};

const getCellContent = (action, row) => {
    if (action === "note") {
        if (!props.permissions.note && !row["isNoted"]) return "Pending";
        if (row["isNoted"]) return `${row["noted_by"]}`;
    } else if (action === "approve") {
        if (row["status"] === "Rejected") return "-";
        if (!row["isNoted"]) return "Pending";
        if (!props.permissions.approve && !row["isApproved"]) return "Pending";
        if (row["isApproved"]) return `${row["approved_by"]}`;
    }
    return null;
};

const getColor = (val) => {
    switch (val) {
        case "Rejected":
            return "danger";
        case "Approved":
            return "success";
        case "Pending":
            return "info";
        case "Overdue":
            return "warning"
    }
}
const userIdToDelete = ref(0);

const confirmDelete = (userId) => {
    userIdToDelete.value = userId;
    isModalDangerActive.value = true;
};

const formDelete = useForm({});
function destroy() {
    formDelete.delete(route(props.routes.delete, userIdToDelete.value));
}
</script>

<template>
    <CardBoxModal v-model="isModalDangerActive" title="Please confirm" button="danger" has-cancel @confirm="destroy">
        <p>Are you sure you want to delete this item?</p>
    </CardBoxModal>

    <div v-if="itemsPaginated.length > 0">
        <table>
            <thead>
                <tr>
                    <!-- <th /> -->
                    <th v-for="column in columns" :key="column.field" :class="column.class">
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
                    <!-- <td>{{ index + 1 + currentPage * 5 }}</td> -->
                    <td v-for="column in columns" :key="column.field" :data-label="column.label">
                        <template v-if="column.action">
                            <IconRounded v-if="
                                row.conflict &&
                                !row.isApproved &&
                                row.status !== 'Rejected'
                            " color="warning" :icon="mdiAlert" small v-tooltip="`Conflict schedules for ${row['conflictData']}`
                                " />
                            <div v-else-if="
                                buttonToDisplay(column.action, row) &&
                                row.status !== 'Rejected'
                            ">
                                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                                    <BaseButton :route-name="route(
                                        `reservation.${column.action}`,
                                        row.id
                                    )
                                        " color="success" preserve-state :icon="mdiCheckCircleOutline" small
                                        v-tooltip="'Approve Reservation'" outline />

                                    <BaseButton :route-name="route(
                                        `reservation.reject_${column.action}r`,
                                        row.id
                                    )
                                        " color="danger" :icon="mdiAlphaXCircleOutline" preserve-state small
                                        v-tooltip="'Reject Reservation'" outline />
                                </BaseButtons>
                            </div>

                            <span v-else>
                                <PillTag outline color="contrast" :label="getCellContent(column.action, row) ?? '--'" />
                                <!-- {{ getCellContent(column.action, row) ?? "--" }} -->
                            </span>
                        </template>
                        <template v-else>
                            <div v-if="Array.isArray(row[column.field])">
                                <span v-for="(opt, idx) in row[column.field]" :key="idx">
                                    {{ opt }} <br />
                                </span>
                            </div>
                            <span v-else-if="column.field == 'remarks'">
                                <pre class="font-figtree">{{
                                    row[column.field]
                                }}</pre>
                            </span>

                            <span v-else-if="column.field == 'status'">
                                <PillTag :color="getColor(row[column.field])" :label="row[column.field]" />
                            </span>
                            <span v-else>
                                {{ row[column.field] }}
                            </span>
                        </template>
                    </td>
                    <td class="before:hidden lg:w-1 whitespace-nowrap">
                        <BaseButtons type="justify-start lg:justify-end" no-wrap>
                            <BaseButton v-if="permissions.show" color="info" :icon="mdiEyeCircle"
                                :href="route(routes.show, row.id)" small />
                            <BaseButton v-if="permissions.edit && !row.isApproved" color="success"
                                :icon="mdiTextBoxEdit" :href="route(routes.edit, row.id)" small />
                            <BaseButton v-if="permissions.delete" color="danger" :icon="mdiTrashCan" small
                                @click="confirmDelete(row.id)" />
                        </BaseButtons>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
            <BaseLevel>
                <BaseButtons>
                    <BaseButton v-for="page in pagesList" :key="page" :active="page === currentPage" :label="page + 1"
                        small @click="currentPage = page" />
                </BaseButtons>
                <small>Page {{ currentPageHuman }} of {{ numPages }}</small>
            </BaseLevel>
        </div>
    </div>

    <div v-else>
        <CardBox>
            <CardBoxComponentEmpty />
        </CardBox>
    </div>
</template>
<style scoped>
.font-figtree {
    font-family: "Figtree", ui-sans-serif;
}
</style>
