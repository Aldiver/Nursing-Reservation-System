<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <section>
        <div
            class="max-w-screen-xl mx-auto px-4 py-28 gap-12 text-gray-600 md:px-8"
        >
            <div class="space-y-5 max-w-4xl mx-auto text-center">
                <h1 class="text-sm text-indigo-600 font-medium">
                    Build products for everyone
                </h1>
                <h2
                    class="text-4xl text-gray-800 font-extrabold mx-auto md:text-5xl"
                >
                    Efficiently Manage and Reserve
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-[#4F46E5] to-[#E114E5]"
                        >Nursing Resources with Ease</span
                    >
                </h2>
                <p class="max-w-2xl mx-auto">
                    Our Nursing Reservation Application streamlines the process
                    of reserving facilities, equipment, and resources, ensuring
                    that the nursing department runs smoothly and efficiently.
                    Whether you're booking a lab, equipment, or study space, our
                    system is designed to meet your needs.
                </p>
                <div
                    class="items-center justify-center gap-x-3 space-y-3 sm:flex sm:space-y-0"
                >
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard.index')"
                        class="block py-2 px-4 text-white font-medium bg-indigo-600 duration-150 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-lg hover:shadow-none"
                    >
                        Go to Dashboard
                    </Link>
                    <Link
                        v-else
                        :href="route('dashboard.index')"
                        class="block py-2 px-4 text-white font-medium bg-indigo-600 duration-150 hover:bg-indigo-500 active:bg-indigo-700 rounded-lg shadow-lg hover:shadow-none"
                    >
                        Get Started
                    </Link>
                    <!-- <a
                        href="javascript:void(0)"
                        class="block py-2 px-4 text-gray-700 hover:text-gray-500 font-medium duration-150 active:bg-gray-100 border rounded-lg"
                    >
                        Get access
                    </a> -->
                </div>
            </div>
            <div class="mt-14">
                <img
                    src="nrsapp-dashboard.png"
                    class="w-full shadow-lg rounded-lg border"
                    alt=""
                />
            </div>
        </div>
    </section>
</template>

<!-- <style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}
@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style> -->
