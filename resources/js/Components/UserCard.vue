<script setup>
import { ref } from "vue";
import { mdiCheckDecagram, mdiClipboardList } from "@mdi/js";
import BaseLevel from "@/Components/BaseLevel.vue";
import UserAvatarCurrentUser from "@/Components/UserAvatarCurrentUser.vue";
import CardBox from "@/Components/CardBox.vue";
import PillTag from "@/Components/PillTag.vue";
import { usePage } from "@inertiajs/vue3";

// const mainStore = useMainStore();

// const userName = computed(() => mainStore.userName);
const user = usePage().props.auth.user;
const userSwitchVal = ref(false);
</script>

<template>
    <CardBox>
        <BaseLevel type="justify-around lg:justify-center">
            <UserAvatarCurrentUser :username="user.name" class="lg:mx-12" />
            <div class="space-y-3 text-center md:text-left lg:mx-12">
                <div class="flex justify-center md:block"></div>
                <h1 class="text-2xl">
                    Howdy, <b>{{ user.name }}</b
                    >!
                </h1>
                <p>
                    Created at:
                    <b>{{
                        new Date(user.created_at).toLocaleDateString("en-US", {
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                        })
                    }}</b>
                </p>
                <div class="flex justify-center md:block space-x-4">
                    <PillTag
                        :label="user.department ?? 'None'"
                        color="info"
                        :icon="mdiClipboardList"
                    />
                    <PillTag
                        v-for="role in user.roles"
                        :label="role.name"
                        color="info"
                        :icon="mdiCheckDecagram"
                    />
                </div>
            </div>
        </BaseLevel>
    </CardBox>
</template>
