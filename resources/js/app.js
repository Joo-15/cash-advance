import '../css/app.css'
import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'

import {
    NConfigProvider,
    NMessageProvider
} from 'naive-ui'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({
            render: () =>
                h(NConfigProvider, null, {
                    default: () =>
                        h(NMessageProvider, null, {
                            default: () => h(App, props),
                        }),
                }),
        })

        vueApp.use(plugin)
        vueApp.use(ZiggyVue)
        vueApp.mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
