import { initPinia } from '@Core/Libs/pinia';
import { initI18n } from '@Core/Libs/i18n';
import { initApexCharts } from '@Core/Libs/apexcharts';

export const initLibs = async (app) => {
  initPinia(app);
  await initI18n(app);
  initApexCharts(app);
};
