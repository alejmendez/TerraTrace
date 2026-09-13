import './bootstrap';
import 'primeicons/primeicons.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { initLibs } from '@Core/Libs';

const appName = import.meta.env.VITE_APP_NAME || 'Agricola Frayleon';

// Importamos todos los módulos de una vez
const modulePages = import.meta.glob('./../../Modules/*/Resources/Pages/**/*.vue', { eager: true });

const resolvePageComponent = (name) => {
  const [module, pageName] = name.split('::');
  const pagePath = `../../Modules/${module}/Resources/Pages/${pageName}.vue`;

  if (!modulePages[pagePath]) {
    throw new Error(`Page "${pagePath}" not found`);
  }

  const page = modulePages[pagePath];

  return typeof page === 'function' ? page() : page;
};

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(name),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });
    initLibs(app);
    return app.use(plugin).use(ZiggyVue).mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
