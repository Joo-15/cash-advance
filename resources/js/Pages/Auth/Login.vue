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
    <div class="min-h-screen flex relative overflow-hidden">
        <!-- Animated Background Blobs -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div
                class="absolute top-20 left-10 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"
            ></div>
            <div
                class="absolute top-40 right-10 w-72 h-72 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"
            ></div>
            <div
                class="absolute bottom-20 left-1/2 w-72 h-72 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"
            ></div>
        </div>

        <!-- Bagian Kiri: Informasi Perusahaan & Branding -->
        <div
            class="hidden lg:flex lg:w-[70%] bg-gradient-to-br from-blue-700 to-indigo-900 relative overflow-hidden group"
        >
            <!-- Background Image dengan Overlay & Parallax -->
            <div class="absolute inset-0">
                <img
                    src="/images/perusahaan.jpg"
                    alt="Office Background"
                    class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-1000"
                />
                <!-- Animated Gradient Overlay -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-blue-700/90 to-indigo-900/90 animate-gradient"
                ></div>
            </div>

            <!-- Animated Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg
                    class="w-full h-full animate-rotate-slow"
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
                                class="animate-pulse"
                            />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Floating Elements -->
            <div
                class="absolute top-20 right-20 w-32 h-32 bg-white/10 rounded-full blur-2xl animate-float"
            ></div>
            <div
                class="absolute bottom-20 left-20 w-48 h-48 bg-blue-400/10 rounded-full blur-3xl animate-float-delayed"
            ></div>

            <!-- Konten Kiri -->
            <div
                class="relative z-10 flex flex-col justify-between p-12 w-full"
            >
                <!-- Main Content Kiri -->
                <div class="flex-1 flex flex-col justify-center">
                    <div class="space-y-8">
                        <div class="animate-fadeInUp">
                            <!-- Animated Badge -->
                            <div
                                class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm mb-6 animate-slideInLeft"
                            >
                                <span class="relative flex h-2 w-2 mr-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"
                                    ></span>
                                    <span
                                        class="relative inline-flex rounded-full h-2 w-2 bg-green-500"
                                    ></span>
                                </span>
                                <span class="text-sm font-medium text-white"
                                    >Sistem Terintegrasi Real-time</span
                                >
                            </div>

                            <h2
                                class="text-4xl font-bold text-white mb-4 leading-tight"
                            >
                                Kelola Cash Advance
                                <br />
                                <span
                                    class="text-blue-200 relative inline-block"
                                >
                                    dengan Mudah & Cepat
                                </span>
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
                            <div
                                class="flex items-center space-x-3 group/item hover:translate-x-2 transition-all duration-300"
                            >
                                <div
                                    class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm group-hover/item:bg-white/30 transition-all group-hover/item:scale-110"
                                >
                                    <svg
                                        class="w-5 h-5 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"
                                        />
                                    </svg>
                                </div>
                                <span class="text-white text-base"
                                    >Proses Pengajuan Cepat</span
                                >
                            </div>
                            <div
                                class="flex items-center space-x-3 group/item hover:translate-x-2 transition-all duration-300 animation-delay-100"
                            >
                                <div
                                    class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm group-hover/item:bg-white/30 transition-all group-hover/item:scale-110"
                                >
                                    <svg
                                        class="w-5 h-5 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                        />
                                    </svg>
                                </div>
                                <span class="text-white text-base"
                                    >Keamanan Data Terjamin</span
                                >
                            </div>
                            <div
                                class="flex items-center space-x-3 group/item hover:translate-x-2 transition-all duration-300 animation-delay-200"
                            >
                                <div
                                    class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm group-hover/item:bg-white/30 transition-all group-hover/item:scale-110"
                                >
                                    <svg
                                        class="w-5 h-5 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <span class="text-white text-base"
                                    >Approval Real-time</span
                                >
                            </div>
                        </div>

                        <!-- Testimonial / Stats -->
                        <div
                            class="pt-8 border-t border-white/20 animate-fadeInUp animation-delay-400"
                        >
                            <div class="flex space-x-8">
                                <div
                                    class="text-center group/stat hover:scale-105 transition-transform duration-300"
                                >
                                    <div
                                        class="text-3xl font-bold text-white mb-1"
                                    >
                                        5000+
                                    </div>
                                    <div class="text-blue-200 text-sm">
                                        Pengguna Aktif
                                    </div>
                                </div>
                                <div
                                    class="text-center group/stat hover:scale-105 transition-transform duration-300"
                                >
                                    <div
                                        class="text-3xl font-bold text-white mb-1"
                                    >
                                        99.9%
                                    </div>
                                    <div class="text-blue-200 text-sm">
                                        Kepuasan
                                    </div>
                                </div>
                                <div
                                    class="text-center group/stat hover:scale-105 transition-transform duration-300"
                                >
                                    <div
                                        class="text-3xl font-bold text-white mb-1"
                                    >
                                        24/7
                                    </div>
                                    <div class="text-blue-200 text-sm">
                                        Support
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Kiri -->
                <div class="mt-8 text-blue-200 text-sm relative">
                    <div
                        class="absolute -top-4 left-0 w-12 h-px bg-gradient-to-r from-blue-300 to-transparent animate-slideInLeft"
                    ></div>
                    <p>© 2026 CashAdvance v1.0.0</p>
                </div>
            </div>
        </div>

        <!-- Bagian Kanan: Form Login -->
        <div
            class="w-full lg:w-[30%] h-screen flex items-center justify-center bg-gray-100 relative overflow-y-auto"
        >
            <div class="w-full h-full animate-fadeInRight">
                <!-- Mobile Logo -->
                <!-- <div class="lg:hidden text-center mb-8">
                    <div class="flex justify-center mb-3">
                        <div
                            class="h-14 w-14 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-md"
                        > -->
                <!-- icon -->
                <!-- </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        CashAdvance Pro
                    </h2>
                    <p class="text-gray-500 text-sm mt-1">Masuk ke akun Anda</p>
                </div> -->

                <!-- Card Login -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-10 h-full flex items-center justify-center hover:shadow-2xl transition-shadow duration-300"
                >
                    <div class="w-full">
                        <!-- Desktop Header -->
                        <div
                            class="lg:block text-center mb-8 animate-slideInDown"
                        >
                            <div class="inline-block relative mb-2">
                                <div
                                    class="absolute inset-0 bg-blue-500 rounded-full blur-xl opacity-30 animate-pulse"
                                ></div>
                                <div
                                    class="relative h-16 w-16 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center shadow-lg mx-auto transform hover:scale-110 transition-transform duration-300"
                                >
                                    <svg
                                        class="w-8 h-8 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <h2
                                class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent"
                            >
                                Selamat Datang Kembali
                            </h2>
                            <p class="text-gray-500 text-sm mt-2">
                                Masuk ke akun cash advance Anda
                            </p>
                        </div>

                        <form @submit.prevent="handleLogin" class="space-y-5">
                            <!-- Identifier -->
                            <div class="group">
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2 group-focus-within:text-blue-600 transition-colors"
                                >
                                    USERNAME / EMAIL
                                </label>

                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
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
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white/50 hover:bg-white focus:bg-white"
                                        placeholder="Masukan username atau email"
                                        autocomplete="username"
                                    />
                                </div>
                                <InputError
                                    class="mt-2 animate-shake"
                                    :message="form.errors.email"
                                />
                            </div>

                            <!-- Password -->
                            <div class="group">
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2 group-focus-within:text-blue-600 transition-colors"
                                >
                                    KATA SANDI
                                </label>

                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
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
                                        class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white/50 hover:bg-white focus:bg-white"
                                        placeholder="Masukkan kata sandi"
                                        autocomplete="current-password"
                                    />

                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center hover:scale-110 transition-transform duration-200"
                                    >
                                        <svg
                                            v-if="!showPassword"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 text-gray-400 hover:text-blue-600 transition-colors"
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
                                            class="h-5 w-5 text-gray-400 hover:text-blue-600 transition-colors"
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
                                    <!-- <div
                                        class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-300 group-focus-within:w-full"
                                    ></div> -->
                                </div>
                                <InputError
                                    class="mt-2 animate-shake"
                                    :message="form.errors.password"
                                />
                            </div>

                            <!-- Remember + Forgot -->
                            <div class="flex items-center justify-between mb-6">
                                <label
                                    class="flex items-center cursor-pointer group/remember"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="form.remember"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer transition-all duration-200"
                                    />
                                    <span
                                        class="ml-2 text-sm text-gray-600 group-hover/remember:text-gray-800 transition-colors"
                                        >Ingat saya</span
                                    >
                                </label>

                                <button
                                    type="button"
                                    @click="handleForgotPassword"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded-full px-2 py-0.5"
                                >
                                    Lupa kata sandi?
                                </button>
                            </div>

                            <!-- Submit -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="relative w-full py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-md hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden group/btn"
                            >
                                <span
                                    class="absolute inset-0 w-full h-full bg-gradient-to-r from-white/0 via-white/20 to-white/0 transform -translate-x-full group-hover/btn:translate-x-full transition-transform duration-1000"
                                ></span>
                                <div
                                    class="flex items-center justify-center space-x-2 relative z-10"
                                >
                                    <svg
                                        v-if="!form.processing"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 group-hover/btn:animate-bounce"
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
                                        form.processing
                                            ? "Memproses..."
                                            : "Masuk"
                                    }}</span>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Fade Animations */
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

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    10%,
    30%,
    50%,
    70%,
    90% {
        transform: translateX(-2px);
    }
    20%,
    40%,
    60%,
    80% {
        transform: translateX(2px);
    }
}

/* Float & Blob Animations */
@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes blob {
    0%,
    100% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

@keyframes rotate-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes dash {
    to {
        stroke-dashoffset: -100;
    }
}

@keyframes gradient {
    0%,
    100% {
        opacity: 0.8;
    }
    50% {
        opacity: 0.9;
    }
}

/* Animation Classes */
.animate-fadeInUp {
    animation: fadeInUp 0.6s ease-out forwards;
}

.animate-fadeInRight {
    animation: fadeInRight 0.6s ease-out forwards;
}

.animate-fadeInDown {
    animation: fadeInDown 0.6s ease-out forwards;
}

.animate-slideInLeft {
    animation: slideInLeft 0.5s ease-out forwards;
}

.animate-slideInDown {
    animation: slideInDown 0.5s ease-out forwards;
}

.animate-shake {
    animation: shake 0.5s ease-in-out;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float 8s ease-in-out infinite;
    animation-delay: -2s;
}

.animate-blob {
    animation: blob 7s infinite;
}

.animate-rotate-slow {
    animation: rotate-slow 60s linear infinite;
}

.animate-dash {
    animation: dash 20s linear infinite;
}

.animate-gradient {
    animation: gradient 4s ease-in-out infinite;
}

/* Delay Classes */
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

.animation-delay-1000 {
    animation-delay: 1s;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
