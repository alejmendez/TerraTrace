import { trans, i18nVue, loadLanguageAsync } from 'laravel-vue-i18n';

const langs = import.meta.glob('../../../../lang/*.json');
const resolver = async (lang) => {
  const loadLanguage = langs[`../../../../lang/${lang}.json`];

  if (!loadLanguage) {
    return { default: {} };
  }

  return loadLanguage();
};

export const initI18n = async (app) => {
  app.use(i18nVue, {
    lang: 'es',
    fallbackLang: 'es',
    shared: true,
    resolve: resolver,
  });

  await loadLanguageAsync('es');

  app.config.globalProperties.__ = (key, replacements) => trans(key, replacements);
};
