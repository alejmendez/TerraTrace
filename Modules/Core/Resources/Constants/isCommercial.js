/**
 * Static "is commercial" filter options (all / yes / no) used by the
 * CategoryProducts index filter dropdown. Labels are i18n keys
 * resolved by the consumer via `__()` (laravel-vue-i18n).
 *
 * Note: `value: null` is intentional — when sent to the backend as a
 * filter query param, an empty string is then forwarded and the
 * service treats that as "no filter" (see CategoryProductService::collection
 * in the backend).
 */
export const IS_COMMERCIAL_OPTIONS = [
    { value: null, labelKey: 'generics.all' },
    { value: true, labelKey: 'generics.yes' },
    { value: false, labelKey: 'generics.no' },
];