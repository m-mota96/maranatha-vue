import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import es from 'element-plus/es/locale/lang/es';
es.el.pagination.pagesize = ' por página';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { fab } from '@fortawesome/free-brands-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
import '../css/generalStyles.css';

library.add(fas, fab, far);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        window.addEventListener('popstate', () => {
            document.body.style.display = 'none';
            window.location.href = '/login';
        });

        const appInstance = createApp({ render: () => h(App, props) });

        appInstance
        .use(plugin)
        .use(ZiggyVue)
        .use(ElementPlus, { locale: es })
        .component('font-awesome-icon', FontAwesomeIcon)
        .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
