<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import CollectionActionMenu from '@Core/Components/Collection/CollectionActionMenu.vue';
import CollectionConfirmDialog from '@Core/Components/Collection/CollectionConfirmDialog.vue';
import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';
import CollectionMetricCard from '@Core/Components/Collection/CollectionMetricCard.vue';
import CollectionPageHeader from '@Core/Components/Collection/CollectionPageHeader.vue';
import CollectionPagination from '@Core/Components/Collection/CollectionPagination.vue';
import CollectionToast from '@Core/Components/Collection/CollectionToast.vue';
import { formatNumber } from '@Core/Utils/format';
import { stringToFormat } from '@Core/Utils/date';
import { can } from '@Auth/Services/Auth';

const props = defineProps({
  fields: Array,
  harvest_available_weeks: Array,
  harvest_available_years: Array,
  meta: Object,
  quarters: Array,
  records: Array,
  summary: Object,
  toast: Object,
  users: Array,
});

const canCreate = can('harvests.create');
const canDestroy = can('harvests.destroy');
const canEdit = can('harvests.edit');
const canShow = can('harvests.show');

const initialQuery = () => {
  const params = new URLSearchParams(window.location.search);

  return {
    q: params.get('q') || '',
    year: params.get('year') || '',
    week: params.get('week') || '',
    field_id: params.get('field_id') || '',
    quarter_id: params.get('quarter_id') || '',
    farmer_id: params.get('farmer_id') || '',
    page: Number(params.get('page') || 1),
    per_page: Number(params.get('per_page') || 12),
    sort: params.get('sort') || 'date',
    direction: params.get('direction') || 'desc',
  };
};

const query = reactive(initialQuery());

const recordToDelete = ref(null);
const toastMessage = ref(props.toast?.detail || '');
const toastTone = ref(props.toast?.severity === 'error' ? 'error' : 'success');

let searchTimer = null;

const buildParams = () => {
  const params = {};
  if (query.q) params.q = query.q;
  if (query.year) params.year = query.year;
  if (query.week) params.week = query.week;
  if (query.field_id) params.field_id = query.field_id;
  if (query.quarter_id) params.quarter_id = query.quarter_id;
  if (query.farmer_id) params.farmer_id = query.farmer_id;
  if (query.page > 1) params.page = query.page;
  if (query.per_page !== 12) params.per_page = query.per_page;
  if (query.sort !== 'date') params.sort = query.sort;
  if (query.direction !== 'desc') params.direction = query.direction;
  return params;
};

const reloadList = (extra = {}) => {
  Object.assign(query, extra);
  query.page = extra.page ?? 1;

  router.get(route('harvests.index', buildParams()), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    preserveScroll: true,
  });
};

const onSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => reloadList({ page: 1 }), 250);
};

const setFilter = (key, value) => {
  clearTimeout(searchTimer);
  reloadList({ [key]: value, page: 1 });
};

const setSort = (sort) => {
  if (query.sort === sort) {
    reloadList({ direction: query.direction === 'asc' ? 'desc' : 'asc', page: 1 });
  } else {
    reloadList({ sort, direction: 'asc', page: 1 });
  }
};

const setPage = (page) => {
  if (page < 1 || page > props.meta.last_page || page === query.page) return;
  router.get(route('harvests.index', { ...buildParams(), page }), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    preserveScroll: true,
  });
  query.page = page;
};

const setPerPage = (perPage) => {
  router.get(route('harvests.index', { ...buildParams(), per_page: perPage, page: 1 }), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    preserveScroll: true,
  });
  query.per_page = Number(perPage);
  query.page = 1;
};

const notify = (message, tone = 'success') => {
  toastMessage.value = message;
  toastTone.value = tone;
};

const deleteRecord = () => {
  if (!recordToDelete.value) return;
  const id = recordToDelete.value.id;
  const label = recordToDelete.value.batch ? `Lote ${recordToDelete.value.batch}` : `cosecha #${id}`;
  recordToDelete.value = null;

  router.delete(route('harvests.destroy', id), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    onSuccess: () => notify(`Se eliminó ${label}.`),
    onError: () => notify('No fue posible eliminar la cosecha.', 'error'),
  });
};

const totalCosechas = computed(() => formatNumber(props.summary?.harvests || 0, 0));
const totalPeso = computed(() => formatNumber(props.summary?.total_weight || 0, 0));

watch(() => props.toast, (next) => {
  if (next?.detail) notify(next.detail, next.severity === 'error' ? 'error' : 'success');
});

onMounted(() => {
  if (props.toast?.detail && !toastMessage.value) {
    notify(props.toast.detail, props.toast.severity === 'error' ? 'error' : 'success');
  }
});

onUnmounted(() => clearTimeout(searchTimer));
</script>

<template>
  <AuthenticatedLayout title="Cosechas">
    <CollectionPageHeader
      title="Cosechas"
      description="Histórico de cosechas por año, semana, campo, cuartel y responsable."
      :action-route="canCreate ? route('harvests.create') : ''"
      action-label="Nueva cosecha"
    />

    <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-2" aria-label="Resumen de cosechas">
      <CollectionMetricCard icon="potted_plant" label="Cosechas registradas" :value="totalCosechas" />
      <CollectionMetricCard icon="agriculture" label="Peso total acumulado" :value="totalPeso" />
    </section>

    <section class="relative rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)]">
      <div class="flex flex-col gap-3 border-b border-[#e9efea] p-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-bold text-[#102f27]">Listado de cosechas</h2>
          <p class="mt-1 text-sm text-[#61716c]">Busca por lote, campo o responsable; filtra por año y semana.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
          <label class="relative block sm:flex-1 sm:min-w-[200px]">
            <span class="sr-only">Buscar cosechas</span>
            <CollectionIcon name="search" :size="20" class="absolute top-1/2 left-3 -translate-y-1/2 text-[#61716c]" aria-hidden="true" />
            <input
              v-model="query.q"
              class="h-10 w-full rounded-lg border border-[#d7e0d9] bg-white pr-3 pl-10 text-sm text-[#102f27] placeholder:text-[#8a9892] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
              type="search"
              placeholder="Buscar lote, campo, responsable..."
              @input="onSearchInput"
            >
          </label>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.year"
            aria-label="Filtrar por año"
            @change="setFilter('year', $event.target.value)"
          >
            <option value="">Todos los años</option>
            <option v-for="year in harvest_available_years" :key="year.value" :value="year.value">{{ year.text }}</option>
          </select>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.week"
            aria-label="Filtrar por semana"
            @change="setFilter('week', $event.target.value)"
          >
            <option value="">Todas las semanas</option>
            <option v-for="week in harvest_available_weeks" :key="week.value" :value="week.value">{{ week.text }}</option>
          </select>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.field_id"
            aria-label="Filtrar por campo"
            @change="setFilter('field_id', $event.target.value)"
          >
            <option value="">Todos los campos</option>
            <option v-for="field in fields" :key="field.value" :value="field.value">{{ field.text }}</option>
          </select>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.quarter_id"
            aria-label="Filtrar por cuartel"
            @change="setFilter('quarter_id', $event.target.value)"
          >
            <option value="">Todos los cuarteles</option>
            <option v-for="quarter in quarters" :key="quarter.value" :value="quarter.value">{{ quarter.text }}</option>
          </select>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.farmer_id"
            aria-label="Filtrar por agricultor"
            @change="setFilter('farmer_id', $event.target.value)"
          >
            <option value="">Todos los agricultores</option>
            <option v-for="user in users" :key="user.value" :value="user.value">{{ user.text }}</option>
          </select>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.sort"
            aria-label="Ordenar cosechas"
            @change="setSort($event.target.value)"
          >
            <option value="date">Ordenar por fecha</option>
            <option value="year">Ordenar por año</option>
            <option value="week">Ordenar por semana</option>
            <option value="batch">Ordenar por lote</option>
            <option value="weight">Ordenar por peso total</option>
          </select>
        </div>
      </div>

      <div v-if="records.length" class="grid gap-3 p-4">
        <article
          v-for="record in records"
          :key="record.id"
          class="flex min-w-0 gap-4 rounded-xl border border-[#e3ebe5] bg-[#fcfdfc] p-4 transition hover:border-[#bddcc6] hover:shadow-sm"
        >
          <span class="flex size-16 shrink-0 flex-col items-center justify-center rounded-lg bg-[#e7f3e9] text-center text-[#17663a]" aria-hidden="true">
            <span class="text-xs font-semibold uppercase tracking-wide text-[#75847d]">Año</span>
            <span class="text-base font-extrabold text-[#102f27]">{{ record.year }}</span>
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="truncate text-base font-bold text-[#102f27]">
                  Lote {{ record.batch || 'sin correlativo' }}
                </h3>
                <p class="mt-1 truncate text-sm text-[#61716c]">
                  {{ record.farmer_name || 'Sin agricultor asignado' }}
                </p>
              </div>
              <CollectionActionMenu
                :show-route="canShow ? route('harvests.show', record.id) : ''"
                :edit-route="canEdit ? route('harvests.edit', record.id) : ''"
                :show-destroy="canDestroy"
                @destroy="recordToDelete = record"
              />
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-2 border-t border-[#e7eee8] pt-3 text-sm sm:grid-cols-4">
              <div>
                <dt class="text-[#75847d]">Fecha</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ record.date ? stringToFormat(record.date) : 'Sin fecha' }}</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Semana</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ record.week || '—' }}</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Peso total</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ formatNumber(record.total_weight || 0, 0) }} kg</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Unidades</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ record.unit_count || 0 }}</dd>
              </div>
            </dl>
            <p v-if="record.field_names && record.field_names.length" class="mt-3 truncate text-xs text-[#75847d]">
              Campos: {{ record.field_names.join(', ') }}
            </p>
            <p v-if="record.quarter_names && record.quarter_names.length" class="truncate text-xs text-[#75847d]">
              Cuarteles: {{ record.quarter_names.join(', ') }}
            </p>
          </div>
        </article>
      </div>

      <div v-else class="p-12 text-center">
        <CollectionIcon name="potted_plant" :size="40" class="text-[#9ab5a1]" aria-hidden="true" />
        <h3 class="mt-3 font-bold text-[#102f27]">No se encontraron cosechas</h3>
        <p class="mt-1 text-sm text-[#61716c]">Ajusta los filtros o crea una nueva cosecha.</p>
      </div>

      <CollectionPagination :meta="meta" :per-page-options="[12, 24]" @page="setPage" @per-page="setPerPage" />
    </section>

    <CollectionConfirmDialog
      :visible="Boolean(recordToDelete)"
      :message="`Eliminarás la cosecha del lote ${recordToDelete?.batch || recordToDelete?.id || ''}. Esta acción no se puede deshacer.`"
      @cancel="recordToDelete = null"
      @confirm="deleteRecord"
    />
    <CollectionToast :message="toastMessage" :tone="toastTone" @dismiss="toastMessage = ''" />
  </AuthenticatedLayout>
</template>
