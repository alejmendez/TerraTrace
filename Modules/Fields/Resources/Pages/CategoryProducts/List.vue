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
import { can } from '@Auth/Services/Auth';

const props = defineProps({
  isCommercialOptions: Array,
  meta: Object,
  records: Array,
  summary: Object,
  toast: Object,
});

const canCreate = can('category_products.create');
const canDestroy = can('category_products.destroy');
const canEdit = can('category_products.edit');

const initialQuery = () => {
  const params = new URLSearchParams(window.location.search);

  return {
    q: params.get('q') || '',
    is_commercial: params.get('is_commercial') || '',
    page: Number(params.get('page') || 1),
    per_page: Number(params.get('per_page') || 12),
    sort: params.get('sort') || 'name',
    direction: params.get('direction') || 'asc',
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
  if (query.is_commercial !== '') params.is_commercial = query.is_commercial;
  if (query.page > 1) params.page = query.page;
  if (query.per_page !== 12) params.per_page = query.per_page;
  if (query.sort !== 'name') params.sort = query.sort;
  if (query.direction !== 'asc') params.direction = query.direction;
  return params;
};

const reloadList = (extra = {}) => {
  Object.assign(query, extra);
  query.page = extra.page ?? 1;

  router.get(route('category_products.index', buildParams()), {
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
  router.get(route('category_products.index', { ...buildParams(), page }), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    preserveScroll: true,
  });
  query.page = page;
};

const setPerPage = (perPage) => {
  router.get(route('category_products.index', { ...buildParams(), per_page: perPage, page: 1 }), {
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
  const name = recordToDelete.value.name || 'la categoría';
  recordToDelete.value = null;

  router.delete(route('category_products.destroy', id), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    onSuccess: () => notify(`Se eliminó ${name}.`),
    onError: () => notify('No fue posible eliminar la categoría.', 'error'),
  });
};

const totalCategorias = computed(() => formatNumber(props.summary?.categories || 0, 0));
const totalComercial = computed(() => formatNumber(props.summary?.commercial || 0, 0));
const totalNoComercial = computed(() => formatNumber(props.summary?.non_commercial || 0, 0));

const commercialLabel = (value) => (value ? 'Sí' : 'No');
const commercialBadgeClass = (value) => (value ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200');

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
  <AuthenticatedLayout title="Categorías de producto">
    <CollectionPageHeader
      title="Categorías de producto"
      description="Administra las categorías de producto usadas en liquidaciones."
      :action-route="canCreate ? route('category_products.create') : ''"
      action-label="Nueva categoría"
    />

    <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3" aria-label="Resumen de categorías">
      <CollectionMetricCard icon="category" label="Categorías registradas" :value="totalCategorias" />
      <CollectionMetricCard icon="check_circle" label="Comerciales" :value="totalComercial" />
      <CollectionMetricCard icon="close" label="No comerciales" :value="totalNoComercial" />
    </section>

    <section class="relative rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)]">
      <div class="flex flex-col gap-3 border-b border-[#e9efea] p-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-bold text-[#102f27]">Listado de categorías</h2>
          <p class="mt-1 text-sm text-[#61716c]">Busca por nombre y filtra por tipo comercial.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
          <label class="relative block sm:flex-1 sm:min-w-[200px]">
            <span class="sr-only">Buscar categorías</span>
            <CollectionIcon name="search" :size="20" class="absolute top-1/2 left-3 -translate-y-1/2 text-[#61716c]" aria-hidden="true" />
            <input
              v-model="query.q"
              class="h-10 w-full rounded-lg border border-[#d7e0d9] bg-white pr-3 pl-10 text-sm text-[#102f27] placeholder:text-[#8a9892] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
              type="search"
              placeholder="Buscar nombre..."
              @input="onSearchInput"
            >
          </label>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.is_commercial"
            aria-label="Filtrar por tipo comercial"
            @change="setFilter('is_commercial', $event.target.value)"
          >
            <option value="">Todos los tipos</option>
            <option value="true">Comerciales</option>
            <option value="false">No comerciales</option>
          </select>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.sort"
            aria-label="Ordenar categorías"
            @change="setSort($event.target.value)"
          >
            <option value="name">Ordenar por nombre</option>
            <option value="is_commercial">Ordenar por tipo</option>
          </select>
        </div>
      </div>

      <div v-if="records.length" class="grid gap-3 p-4 md:grid-cols-2">
        <article
          v-for="record in records"
          :key="record.id"
          class="flex min-w-0 gap-4 rounded-xl border border-[#e3ebe5] bg-[#fcfdfc] p-4 transition hover:border-[#bddcc6] hover:shadow-sm"
        >
          <span class="flex size-16 shrink-0 items-center justify-center rounded-lg bg-[#e7f3e9] text-[#17663a]" aria-hidden="true">
            <CollectionIcon name="category" :size="36" />
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="truncate text-base font-bold text-[#102f27]">{{ record.name }}</h3>
                <p class="mt-1 truncate text-sm text-[#61716c]">
                  <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold" :class="commercialBadgeClass(record.is_commercial)">
                    {{ commercialLabel(record.is_commercial) }}
                  </span>
                </p>
              </div>
              <CollectionActionMenu
                :edit-route="canEdit ? route('category_products.edit', record.id) : ''"
                :show-destroy="canDestroy"
                @destroy="recordToDelete = record"
              />
            </div>
            <dl class="mt-4 grid grid-cols-1 gap-2 border-t border-[#e7eee8] pt-3 text-sm">
              <div>
                <dt class="text-[#75847d]">Productos de liquidación asociados</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ record.liquidation_products_count ?? 0 }}</dd>
              </div>
            </dl>
          </div>
        </article>
      </div>

      <div v-else class="p-12 text-center">
        <CollectionIcon name="category" :size="40" class="text-[#9ab5a1]" aria-hidden="true" />
        <h3 class="mt-3 font-bold text-[#102f27]">No se encontraron categorías</h3>
        <p class="mt-1 text-sm text-[#61716c]">Ajusta los filtros o crea una nueva categoría.</p>
      </div>

      <CollectionPagination :meta="meta" :per-page-options="[12, 24]" @page="setPage" @per-page="setPerPage" />
    </section>

    <CollectionConfirmDialog
      :visible="Boolean(recordToDelete)"
      :message="`Eliminarás la categoría ${recordToDelete?.name || ''}. Esta acción no se puede deshacer.`"
      @cancel="recordToDelete = null"
      @confirm="deleteRecord"
    />
    <CollectionToast :message="toastMessage" :tone="toastTone" @dismiss="toastMessage = ''" />
  </AuthenticatedLayout>
</template>
