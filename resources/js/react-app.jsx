import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';


createInertiaApp({

    title: (title) =>
        title
            ? `${title} | RR ERP`
            : 'RR ERP',

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx')
        ),

    setup({ el, App, props }) {

        createRoot(el).render(
            <App {...props} />
        );

    },

    progress: {
        color: '#0d6efd',
    },

});