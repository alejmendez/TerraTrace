import { trans, i18nVue, I18n } from 'laravel-vue-i18n';

const langs = import.meta.glob('../../../../lang/*.json');
const resolver = async (lang) => {
  return await langs[`../../../../lang/${lang}.json`]();
};

export const initI18n = (app) => {
  app.use(i18nVue, {
    lang: 'es',
    fallbackLang: 'es',
    shared: true,
    resolve: resolver,
  });

  app.config.globalProperties.__ = (key, replacements) => trans(key, replacements);
};
