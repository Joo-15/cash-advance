<script setup>
import { ref, reactive } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";

import InputError from "@/Components/InputError.vue";

const showPassword = ref(false);
const errors = reactive({});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const handleLogin = () => {
    errors.email = null;
    errors.password = null;

    if (!form.email.trim()) {
        errors.email = "Email tidak boleh kosong";
        return;
    }

    if (!form.password.trim()) {
        errors.password = "Kata sandi harus diisi";
        return;
    }

    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};

const handleForgotPassword = () => {
    window.location.href = "/forgot-password";
};
</script>

<template>
    <div class="min-h-screen flex">
        <!-- Bagian Kiri: Informasi Perusahaan & Branding -->
        <div
            class="hidden lg:flex lg:w-[60%] bg-gradient-to-br from-blue-700 to-indigo-900 relative overflow-hidden"
        >
            <!-- Background Image dengan Overlay -->
            <div class="absolute inset-0">
                <img
                    src="/images/perusahaan.jpg"
                    alt="Office Background"
                    class="w-full h-full object-cover"
                />
                <!-- Overlay gradient (warna tetap) -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-blue-700/90 to-indigo-900/90"
                ></div>
            </div>

            <!-- Background Pattern (tetap) -->
            <div class="absolute inset-0 opacity-10">
                <svg
                    class="w-full h-full"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 800 800"
                >
                    <defs>
                        <pattern
                            id="grid"
                            width="40"
                            height="40"
                            patternUnits="userSpaceOnUse"
                        >
                            <path
                                d="M 40 0 L 0 0 0 40"
                                fill="none"
                                stroke="white"
                                stroke-width="1"
                            />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Konten Kiri (tetap) -->
            <div
                class="relative z-10 flex flex-col justify-between p-12 w-full"
            >
                <!-- Main Content Kiri -->
                <div class="flex-1 flex flex-col justify-center">
                    <div class="space-y-8">
                        <div class="animate-fadeInUp">
                            <h2 class="text-4xl font-bold text-white mb-4">
                                Kelola Cash Advance
                                <br />
                                <span class="text-blue-200"
                                    >dengan Mudah & Cepat</span
                                >
                            </h2>
                            <p class="text-blue-100 text-lg leading-relaxed">
                                Platform terintegrasi untuk pengajuan dan
                                pengelolaan cash advance karyawan secara
                                real-time.
                            </p>
                        </div>

                        <!-- Feature List -->
                        <div
                            class="space-y-4 animate-fadeInUp animation-delay-200"
                        >
                            <!-- ... feature items tetap sama ... -->
                        </div>

                        <!-- Testimonial / Stats -->
                        <div
                            class="pt-8 border-t border-white/20 animate-fadeInUp animation-delay-400"
                        >
                            <!-- ... stats tetap sama ... -->
                        </div>
                    </div>
                </div>

                <!-- Footer Kiri -->
                <div class="mt-8 text-blue-200 text-sm">
                    <p>© 2026 CashAdvance v1.0.0</p>
                </div>
            </div>
        </div>

        <!-- Bagian Kanan: Form Login -->
        <div
            class="w-full lg:w-[40%] h-screen flex items-center justify-center p-6 bg-gray-100"
        >
            <div class="w-full max-w-lg h-full">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <div class="flex justify-center mb-3">
                        <div
                            class="h-14 w-14 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-md"
                        >
                            <!-- icon -->
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        CashAdvance Pro
                    </h2>
                    <p class="text-gray-500 text-sm mt-1">Masuk ke akun Anda</p>
                </div>

                <!-- Card Login -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-12 h-full flex items-center justify-center"
                >
                    <div class="w-full">
                        <!-- Desktop Header -->
                        <div class="hidden lg:block text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Selamat Datang Kembali
                            </h2>
                            <p class="text-gray-500 text-sm mt-2">
                                Masuk ke akun cash advance Anda
                            </p>
                        </div>

                        <form @submit.prevent="handleLogin">
                            <!-- Identifier -->
                            <div class="mb-5">
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                >
                                    USERNAME / EMAIL
                                </label>

                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                    </div>

                                    <input
                                        v-model="form.email"
                                        type="text"
                                        required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Masukan username atau email"
                                        autocomplete="username"
                                    />
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.email"
                                />
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                >
                                    KATA SANDI
                                </label>

                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 15v2m-6-4h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2zm10-4a4 4 0 00-8 0v4h8V7z"
                                            />
                                        </svg>
                                    </div>

                                    <input
                                        v-model="form.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        required
                                        class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Masukkan kata sandi"
                                        autocomplete="current-password"
                                    />

                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    >
                                        <svg
                                            v-if="!showPassword"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.password"
                                />
                            </div>

                            <!-- Remember + Forgot -->
                            <div class="flex items-center justify-between mb-6">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.remember"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer"
                                    />
                                    <span class="ml-2 text-sm text-gray-600"
                                        >Ingat saya</span
                                    >
                                </label>

                                <button
                                    type="button"
                                    @click="handleForgotPassword"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-300 rounded-full px-2 py-0.5"
                                >
                                    Lupa kata sandi?
                                </button>
                            </div>

                            <!-- Submit -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                            >
                                <svg
                                    v-if="!form.processing"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="animate-spin h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                <span>{{
                                    form.processing ? "Memproses..." : "Masuk"
                                }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.6s ease-out forwards;
}

.animation-delay-200 {
    animation-delay: 0.2s;
    opacity: 0;
    animation-fill-mode: forwards;
}

.animation-delay-400 {
    animation-delay: 0.4s;
    opacity: 0;
    animation-fill-mode: forwards;
}
</style>
