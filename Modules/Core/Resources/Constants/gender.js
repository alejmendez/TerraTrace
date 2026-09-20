/**
 * Static gender options (M / F) used by the Dogs module. Labels
 * are i18n keys resolved by the consumer via `__()` (the laravel-
 * vue-i18n global) so the backend never ships these strings over
 * the wire and there's no HTTP roundtrip to fetch them.
 *
 * Consumers (Dogs/Form.vue, Dogs/List.vue) typically map the
 * array once to `{ value, text }` at script-setup time:
 *
 *   import { GENDERS } from '@Core/Constants/gender';
 *   const genderOptions = GENDERS.map((g) => ({
 *     value: g.value,
 *     text: __(g.labelKey),
 *   }));
 */
export const GENDERS = [
    { value: 'M', labelKey: 'dog.form.gender.options.male' },
    { value: 'F', labelKey: 'dog.form.gender.options.female' },
];