import { createPinia } from 'pinia';

export const initPinia = (app) => {
  const pinia = createPinia();
  app.use(pinia);
};
