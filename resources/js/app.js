import './bootstrap';
import 'primeicons/primeicons.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { initLibs } from '@Core/Libs';

const appName = import.meta.env.VITE_APP_NAME || 'Agricola Frayleon';

// Lazy-load module pages. With `eager: false` (the default), Vite creates
// one chunk per .vue file and only fetches the chunk when the page is
// first visited. This cuts the initial JS bundle roughly by the number
// of pages, since most users only ever visit a handful of CRUD screens.
const modulePages = import.meta.glob('./../../Modules/*/Resources/Pages/**/*.vue');

const resolvePageComponent = (name) => {
  const [module, pageName] = name.split('::');
  const pagePath = `../../Modules/${module}/Resources/Pages/${pageName}.vue`;

  const importPage = modulePages[pagePath];

  if (! importPage) {
    throw new Error(`Page "${pagePath}" not found`);
  }

  return importPage().then((m) => m.default);
};

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(name),
  async setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });
    await initLibs(app);
    return app.use(plugin).use(ZiggyVue).mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
