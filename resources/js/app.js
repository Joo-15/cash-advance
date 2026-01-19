import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { NConfigProvider, NMessageProvider, dateIdID, idID } from "naive-ui";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

// Definisikan halaman guest
const guestPages = [
    "Auth/Login",
    "Auth/Register",
    "Auth/ForgotPassword",
    "Auth/ResetPassword",
    "Auth/VerifyEmail",
    "Welcome",
];

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: async (name) => {
        try {
            const page = await resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob("./Pages/**/*.vue"),
            );

            // Cek apakah halaman termasuk guest page
            const isGuestPage = guestPages.some(
                (guestPage) => name.startsWith(guestPage) || name === guestPage,
            );

            // Assign layout langsung (untuk layout dengan slot)
            if (!page.default.layout && !isGuestPage) {
                page.default.layout = AuthenticatedLayout;
            }

            return page;
        } catch (error) {
            console.error(`Failed to load page ${name}:`, error);

            // Fallback ke halaman error jika ada
            try {
                const errorPage = await resolvePageComponent(
                    `./Pages/Error.vue`,
                    import.meta.glob("./Pages/**/*.vue"),
                );
                return errorPage;
            } catch {
                // Fallback minimal
                return {
                    default: {
                        name: "ErrorPage",
                        template: "<div>Page not found</div>",
                    },
                };
            }
        }
    },

    setup({ el, App, props, plugin }) {
        // Konfigurasi tema Naive UI
        const themeOverrides = {
            common: {
                primaryColor: "#4B5563",
                primaryColorHover: "#374151",
                primaryColorPressed: "#1F2937",
            },
            Button: {
                colorPrimary: "#4B5563",
            },
        };

        const vueApp = createApp({
            render: () =>
                h(
                    NConfigProvider,
                    {
                        themeOverrides,
                        locale: idID, // ✅ Gunakan object locale
                        dateLocale: dateIdID, // ✅ Gunakan object dateLocale
                    },
                    {
                        default: () =>
                            h(
                                NMessageProvider,
                                {
                                    max: 3,
                                    duration: 3000,
                                    containerStyle: {
                                        top: "60px",
                                    },
                                },
                                {
                                    default: () => h(App, props),
                                },
                            ),
                    },
                ),
        });

        // Gunakan plugin Inertia
        vueApp.use(plugin);

        // Gunakan Ziggy untuk routing
        vueApp.use(ZiggyVue);

        // Mount aplikasi
        vueApp.mount(el);

        // Return cleanup function
        return () => {
            vueApp.unmount();
        };
    },

    progress: {
        color: "#4B5563",
        showSpinner: true,
        delay: 250,
    },
});
