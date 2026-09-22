<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionPagination from '@Core/Components/Collection/CollectionPagination.vue';
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';
import { useToast } from '@Core/Composables/useToast';
import { defaultDeleteHandler } from '@Core/Utils/table.js';
import { stringToFormat } from '@Core/Utils/date';
import { can } from '@Auth/Services/Auth';
import HarvestService from '@Fields/Services/HarvestService.js';

// `__` is registered as a global Vue property for templates, but is NOT
// auto-imported into `<script setup>` scope. Alias `trans` so the script
// body can resolve i18n keys at setup time / inside async handlers.
const __ = trans;

// Match-mode constants kept locally so we don't depend on @primevue/core/api.
// The wire format sent to PrimevueDatatables.php on the backend still uses
// these string tokens ("contains", "equals") and operators ("and" / "or").
const MATCH_CONTAINS = 'contains';
const MATCH_EQUALS = 'equals';

const props = defineProps({
    field_id: {
        type: Number,
    },
    quarter_id: {
        type: Number,
    },
    show_actions: {
        type: Boolean,
        default: true,
    },
    harvest_available_years: Array,
    harvest_available_weeks: Array,
    fields: Array,
    quarters: Array,
    users: Array,
});

const toast = useToast();

const records = ref([]);
const meta = ref({
    total: 0,
    from: 1,
    to: 1,
    current_page: 1,
    last_page: 1,
    per_page: 10,
});
const loading = ref(false);

const showFieldColumn = computed(() => props.field_id === undefined && props.quarter_id === undefined);
const showQuarterColumn = computed(() => props.quarter_id === undefined);

const filters = reactive({
    global: { value: null, matchMode: MATCH_CONTAINS },
    year: { operator: 'and', constraints: [{ value: null, matchMode: MATCH_EQUALS }] },
    week: { operator: 'and', constraints: [{ value: null, matchMode: MATCH_EQUALS }] },
    batch: { value: null, matchMode: MATCH_CONTAINS },
    'details.quarter.field_id': { value: null, matchMode: MATCH_EQUALS },
    'details.quarter_id': { value: null, matchMode: MATCH_EQUALS },
    'farmer.id': { value: null, matchMode: MATCH_EQUALS },
});

const yearSelection = ref(props.harvest_available_years?.[0] ?? null);

const sort = reactive({ field: 'date', order: 1 });

const canShow = can('harvests.show');
const canEdit = can('harvests.edit');
const canDestroy = can('harvests.destroy');

/**
 * Build the params object expected by HarvestService.list / PrimevueDatatables.
 * Year, field_id, and quarter_id come from props and top-level state; the
 * rest is whatever the user typed in the per-column filters.
 */
const buildParams = () => {
    const payload = {
        page: (meta.value.current_page ?? 1) - 1,
        rows: meta.value.per_page,
        sortField: sort.field,
        sortOrder: sort.order,
        filters: {
            global: { ...filters.global },
            year: { operator: 'and', constraints: [{ ...filters.year.constraints[0] }] },
            week: { operator: 'and', constraints: [{ ...filters.week.constraints[0] }] },
            batch: { ...filters.batch },
            'details.quarter.field_id': { ...filters['details.quarter.field_id'] },
            'details.quarter_id': { ...filters['details.quarter_id'] },
            'farmer.id': { ...filters['farmer.id'] },
        },
    };

    if (yearSelection.value?.value !== undefined && yearSelection.value?.value !== null) {
        payload.filters.year.constraints[0].value = yearSelection.value.value;
    }

    if (props.field_id !== undefined) {
        payload.filters['details.quarter.field_id'] = {
            value: { value: props.field_id },
            matchMode: MATCH_EQUALS,
        };
    }

    if (props.quarter_id !== undefined) {
        payload.filters['details.quarter_id'] = {
            value: { value: props.quarter_id },
            matchMode: MATCH_EQUALS,
        };
    }

    return payload;
};

const loadLazyData = async () => {
    loading.value = true;
    try {
        const response = await HarvestService.list(buildParams());
        records.value = response.data ?? [];
        meta.value = {
            total: response.total ?? 0,
            from: response.from ?? 1,
            to: response.to ?? 1,
            current_page: response.current_page ?? 1,
            last_page: response.last_page ?? 1,
            per_page: response.per_page ?? meta.value.per_page,
            details_sum_weight: response.details_sum_weight,
            details_count: response.details_count,
        };
    } catch {
        toast.show({
            tone: 'error',
            message: __('generics.tables.errors.could_not_load_the_data'),
        });
        records.value = [];
        meta.value = {
            total: 0,
            from: 1,
            to: 1,
            current_page: 1,
            last_page: 1,
            per_page: meta.value.per_page,
        };
    } finally {
        loading.value = false;
    }
};

defineExpose({ loadLazyData, records, meta });

const onPage = (page) => {
    if (page === meta.value.current_page) return;
    meta.value.current_page = page;
    loadLazyData();
};

const onPerPage = (perPage) => {
    meta.value.per_page = Number(perPage);
    meta.value.current_page = 1;
    loadLazyData();
};

const setSort = (field) => {
    if (sort.field === field) {
        sort.order = sort.order === 1 ? -1 : 1;
    } else {
        sort.field = field;
        sort.order = 1;
    }
    meta.value.current_page = 1;
    loadLazyData();
};

const applyFilters = () => {
    meta.value.current_page = 1;
    loadLazyData();
};

const clearFilters = () => {
    filters.global.value = null;
    filters.year.constraints[0].value = null;
    filters.week.constraints[0].value = null;
    filters.batch.value = null;
    filters['details.quarter.field_id'].value = null;
    filters['details.quarter_id'].value = null;
    filters['farmer.id'].value = null;
    yearSelection.value = null;
    applyFilters();
};

const deleteHandler = (record) => {
    defaultDeleteHandler({ loadLazyData }, () => HarvestService.del(record.id));
};

const numberFormat = (n) =>
    new Intl.NumberFormat('es-CL', { maximumFractionDigits: 2 }).format(Number.isFinite(n) ? n : 0);

const weightTotal = computed(() => {
    const total = (meta.value.details_sum_weight ?? 0) / 1000;
    return `${numberFormat(total)} Kgs`;
});

const unitCountTotal = computed(() => numberFormat(meta.value.details_count ?? 0));

const footerColspan = computed(() => (props.quarter_id ? 3 : props.field_id ? 4 : 5));

const totalColspan = computed(
    () =>
        (showFieldColumn.value ? 1 : 0) +
        (showQuarterColumn.value ? 1 : 0) +
        6 +
        (props.show_actions ? 1 : 0),
);

onMounted(loadLazyData);
</script>

<template>
    <div class="grid gap-4 items-stretch mb-4 md:grid-cols-3 sm:grid-cols-1">
        <CollectionCardSection
            section-class="flex-1 mt-5 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-[#1b2d22]"
            wrapper-class="p-5"
        >
            <div class="text-gray-400 pb-1">{{ __('harvest.table_filters.year') }}</div>
            <CollectionSelect
                id="harvest-year"
                v-model="yearSelection"
                :placeholder="__('generics.please_select')"
                :options="props.harvest_available_years"
                @change="applyFilters"
            />
        </CollectionCardSection>

        <CollectionCardSection
            section-class="flex-1 mt-5 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-[#1b2d22]"
            wrapper-class="p-5"
        >
            <div class="text-gray-400 pb-1">Unidades</div>
            <div class="pb-3 text-3xl font-bold dark:text-gray-100">{{ unitCountTotal }}</div>
        </CollectionCardSection>

        <CollectionCardSection
            section-class="flex-1 mt-5 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-[#1b2d22]"
            wrapper-class="p-5"
        >
            <div class="text-gray-400 pb-1">Peso Total</div>
            <div class="pb-3 text-3xl font-bold dark:text-gray-100">{{ weightTotal }}</div>
        </CollectionCardSection>
    </div>

    <section
        class="terra-datatable overflow-x-auto rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)] dark:bg-[#1b2d22]"
    >
        <div class="flex flex-col gap-3 border-b border-[#e9efea] p-4 lg:flex-row lg:items-center lg:justify-between">
            <button
                type="button"
                class="inline-flex items-center gap-1 rounded-md border border-[#d7e0d9] px-3 py-1.5 text-sm text-[#284238] hover:bg-[#f1f7f1] dark:text-[#dcebdd] dark:border-[#374b3d] dark:hover:bg-[#263a2d]"
                @click="clearFilters"
            >
                <CollectionIcon name="filter_alt_off" :size="16" aria-hidden="true" />
                Limpiar
            </button>
            <label class="relative block w-full max-w-xs">
                <span class="sr-only">{{ __('generics.tables.search') }}</span>
                <CollectionIcon
                    name="search"
                    :size="18"
                    class="absolute top-1/2 left-3 -translate-y-1/2 text-[#61716c]"
                    aria-hidden="true"
                />
                <input
                    v-model="filters.global.value"
                    class="h-9 w-full rounded-md border border-[#d7e0d9] bg-white pr-3 pl-9 text-sm text-[#102f27] placeholder:text-[#9ba8a0] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1] dark:bg-[#111d16] dark:border-[#374b3d] dark:text-[#edf6ed]"
                    type="search"
                    :placeholder="__('generics.tables.search') + '...'"
                    @keyup.enter="applyFilters"
                />
            </label>
        </div>

        <div v-if="loading" class="p-8 text-center text-sm text-[#61716c]">Cargando cosechas…</div>

        <table v-else class="w-full min-w-[1100px] text-left text-sm">
            <thead class="bg-[#f6faf7] text-[#284238] dark:bg-[#1b2d22] dark:text-[#dcebdd]">
                <tr>
                    <th class="px-4 py-2 font-semibold">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-[#17663a]"
                            @click="setSort('week')"
                        >
                            {{ __('harvest.table.week') }}
                            <CollectionIcon
                                v-if="sort.field === 'week'"
                                :name="sort.order === 1 ? 'arrow_upward' : 'arrow_downward'"
                                :size="14"
                                aria-hidden="true"
                            />
                        </button>
                    </th>
                    <th class="px-4 py-2 font-semibold">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-[#17663a]"
                            @click="setSort('date')"
                        >
                            {{ __('harvest.table.date') }}
                            <CollectionIcon
                                v-if="sort.field === 'date'"
                                :name="sort.order === 1 ? 'arrow_upward' : 'arrow_downward'"
                                :size="14"
                                aria-hidden="true"
                            />
                        </button>
                    </th>
                    <th class="px-4 py-2 font-semibold">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-[#17663a]"
                            @click="setSort('batch')"
                        >
                            {{ __('harvest.table.batch') }}
                            <CollectionIcon
                                v-if="sort.field === 'batch'"
                                :name="sort.order === 1 ? 'arrow_upward' : 'arrow_downward'"
                                :size="14"
                                aria-hidden="true"
                            />
                        </button>
                    </th>
                    <th v-if="showFieldColumn" class="px-4 py-2 font-semibold">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-[#17663a]"
                            @click="setSort('field.names')"
                        >
                            {{ __('harvest.table.field') }}
                            <CollectionIcon
                                v-if="sort.field === 'field.names'"
                                :name="sort.order === 1 ? 'arrow_upward' : 'arrow_downward'"
                                :size="14"
                                aria-hidden="true"
                            />
                        </button>
                    </th>
                    <th v-if="showQuarterColumn" class="px-4 py-2 font-semibold">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-[#17663a]"
                            @click="setSort('quarter.name')"
                        >
                            {{ __('harvest.table.quarter') }}
                            <CollectionIcon
                                v-if="sort.field === 'quarter.name'"
                                :name="sort.order === 1 ? 'arrow_upward' : 'arrow_downward'"
                                :size="14"
                                aria-hidden="true"
                            />
                        </button>
                    </th>
                    <th class="px-4 py-2 font-semibold">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-[#17663a]"
                            @click="setSort('total_weight')"
                        >
                            {{ __('harvest.table.weight') }}
                            <CollectionIcon
                                v-if="sort.field === 'total_weight'"
                                :name="sort.order === 1 ? 'arrow_upward' : 'arrow_downward'"
                                :size="14"
                                aria-hidden="true"
                            />
                        </button>
                    </th>
                    <th class="px-4 py-2 font-semibold">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-[#17663a]"
                            @click="setSort('unit_count')"
                        >
                            {{ __('harvest.table.count_details') }}
                            <CollectionIcon
                                v-if="sort.field === 'unit_count'"
                                :name="sort.order === 1 ? 'arrow_upward' : 'arrow_downward'"
                                :size="14"
                                aria-hidden="true"
                            />
                        </button>
                    </th>
                    <th class="px-4 py-2 font-semibold">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 hover:text-[#17663a]"
                            @click="setSort('farmer.name')"
                        >
                            {{ __('harvest.table.responsible') }}
                            <CollectionIcon
                                v-if="sort.field === 'farmer.name'"
                                :name="sort.order === 1 ? 'arrow_upward' : 'arrow_downward'"
                                :size="14"
                                aria-hidden="true"
                            />
                        </button>
                    </th>
                    <th v-if="props.show_actions" class="px-4 py-2 font-semibold">Acciones</th>
                </tr>
                <tr class="bg-white dark:bg-[#111d16]">
                    <td class="px-4 py-2">
                        <CollectionSelect
                            v-model="filters.week.constraints[0].value"
                            :options="props.harvest_available_weeks"
                            placeholder="Todos"
                            @change="applyFilters"
                        />
                    </td>
                    <td class="px-4 py-2">
                        <CollectionSelect
                            v-model="filters.week.constraints[0].value"
                            :options="props.harvest_available_weeks"
                            placeholder="Todos"
                            @change="applyFilters"
                        />
                    </td>
                    <td class="px-4 py-2">
                        <CollectionInput
                            v-model="filters.batch.value"
                            type="text"
                            placeholder="Buscar por batch"
                            @keyup.enter="applyFilters"
                        />
                    </td>
                    <td v-if="showFieldColumn" class="px-4 py-2">
                        <CollectionSelect
                            v-model="filters['details.quarter.field_id'].value"
                            :options="props.fields"
                            placeholder="Todos"
                            @change="applyFilters"
                        />
                    </td>
                    <td v-if="showQuarterColumn" class="px-4 py-2">
                        <CollectionSelect
                            v-model="filters['details.quarter_id'].value"
                            :options="props.quarters"
                            placeholder="Todos"
                            @change="applyFilters"
                        />
                    </td>
                    <td class="px-4 py-2"></td>
                    <td class="px-4 py-2"></td>
                    <td class="px-4 py-2">
                        <CollectionSelect
                            v-model="filters['farmer.id'].value"
                            :options="props.users"
                            placeholder="Todos"
                            @change="applyFilters"
                        />
                    </td>
                    <td v-if="props.show_actions" class="px-4 py-2"></td>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!records.length">
                    <td :colspan="totalColspan" class="px-4 py-12 text-center text-[#61716c]">
                        {{ __('generics.tables.empty') }}
                    </td>
                </tr>
                <tr
                    v-for="row in records"
                    v-else
                    :key="row.id"
                    class="border-t border-[#e9efea] hover:bg-[#f6faf7] dark:border-[#263a2d] dark:hover:bg-[#1b2d22]"
                >
                    <td class="px-4 py-2">
                        {{ __('harvest.table_data.date', { week: row.week, year: row.year }) }}
                    </td>
                    <td class="px-4 py-2">{{ stringToFormat(row.date) }}</td>
                    <td class="px-4 py-2">{{ row.batch }}</td>
                    <td v-if="showFieldColumn" class="px-4 py-2">{{ row.field_names }}</td>
                    <td v-if="showQuarterColumn" class="px-4 py-2">{{ row.quarter_names }}</td>
                    <td class="px-4 py-2">{{ numberFormat(row.total_weight / 1000) }} Kgs</td>
                    <td class="px-4 py-2">{{ numberFormat(row.unit_count) }}</td>
                    <td class="px-4 py-2">{{ row.farmer_name }}</td>
                    <td v-if="props.show_actions" class="px-4 py-2 whitespace-nowrap">
                        <Link :href="route('harvests.show', row.id)" v-if="canShow" class="inline-block me-2">
                            <CollectionIcon name="visibility" :size="18" class="cursor-pointer text-slate-500 hover:text-sky-600" />
                        </Link>
                        <Link :href="route('harvests.edit', row.id)" v-if="canEdit" class="inline-block me-2">
                            <CollectionIcon name="edit" :size="18" class="cursor-pointer text-slate-500 hover:text-emerald-600" />
                        </Link>
                        <CollectionIcon
                            v-if="canDestroy"
                            name="delete"
                            :size="18"
                            class="cursor-pointer text-slate-500 hover:text-pink-600"
                            role="button"
                            tabindex="0"
                            @click="deleteHandler(row)"
                            @keydown.enter="deleteHandler(row)"
                        />
                    </td>
                </tr>
            </tbody>
            <tfoot class="bg-[#f6faf7] font-semibold text-[#284238] dark:bg-[#1b2d22] dark:text-[#dcebdd]">
                <tr>
                    <td :colspan="footerColspan" class="px-4 py-2 text-right">Totals:</td>
                    <td class="px-4 py-2">{{ weightTotal }}</td>
                    <td :colspan="3" class="px-4 py-2">{{ unitCountTotal }}</td>
                </tr>
            </tfoot>
        </table>

        <CollectionPagination
            :meta="meta"
            :per-page-options="[5, 10, 25, 50, 100]"
            @page="onPage"
            @per-page="onPerPage"
        />
    </section>
</template>
