<template>
    <div>
        <h1>Edit User</h1>
        <form @submit.prevent="submit">
            <div>
                <label>Name</label>
                <input v-model="form.name" type="text" required />
            </div>
            <div>
                <label>Email</label>
                <input v-model="form.email" type="email" required />
            </div>
            <div>
                <label>Password</label>
                <input v-model="form.password" type="password" />
            </div>
            <div>
                <label>Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" />
            </div>
            <div>
                <label>Role</label>
                <select v-model="form.role" required>
                    <option
                        v-for="role in roles"
                        :key="role.name"
                        :value="role.name"
                    >
                        {{ role.name }}
                    </option>
                </select>
            </div>
            <button type="submit">Update</button>
        </form>
    </div>
</template>

<script>
import { Inertia } from "@inertiajs/inertia";
import { ref } from "vue";

export default {
    props: {
        user: Object,
        roles: Array,
        userRole: String,
    },
    setup(props) {
        const form = ref({
            name: props.user.name,
            email: props.user.email,
            password: "",
            password_confirmation: "",
            role: props.userRole,
        });

        const submit = () => {
            Inertia.put(`/admin/users/${props.user.id}`, form.value);
        };

        return { form, submit };
    },
};
</script>
