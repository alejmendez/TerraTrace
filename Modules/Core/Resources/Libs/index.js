import { initPinia } from '@Core/Libs/pinia';
import { initI18n } from '@Core/Libs/i18n';
import { initPrime } from '@Core/Libs/prime';
import { initApexCharts } from '@Core/Libs/apexcharts';
import { initComponent } from '@Core/Libs/components';

export const initLibs = (app) => {
  initPinia(app);
  initI18n(app);
  initPrime(app);
  initApexCharts(app);
  initComponent(app);
};
