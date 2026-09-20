/**
 * Static scale-type options for the Quarters statistics tab. Labels
 * are i18n keys resolved by the consumer via `__()` (laravel-vue-i18n)
 * so the backend doesn't need to ship them per-request.
 *
 * Consumers (Quarters/Show.vue, Quarters/ShowComponents/StatisticsCard.vue)
 * map the array to `{ value, text }` once at script-setup time.
 */
export const SCALE_TYPE = [
    { value: 'weight', labelKey: 'quarter.show.statistics.scale_type.options.weight' },
    { value: 'quantity', labelKey: 'quarter.show.statistics.scale_type.options.quantity' },
];