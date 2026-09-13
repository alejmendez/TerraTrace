<script setup>
import { computed, onMounted, ref } from 'vue';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import CollectionActionMenu from '@Core/Components/Collection/CollectionActionMenu.vue';
import CollectionConfirmDialog from '@Core/Components/Collection/CollectionConfirmDialog.vue';
import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';
import CollectionMetricCard from '@Core/Components/Collection/CollectionMetricCard.vue';
import CollectionPageHeader from '@Core/Components/Collection/CollectionPageHeader.vue';
import CollectionPagination from '@Core/Components/Collection/CollectionPagination.vue';
import CollectionToast from '@Core/Components/Collection/CollectionToast.vue';
import { useCollection } from '@Core/Composables/useCollection.js';
import { formatNumber } from '@Core/Utils/format';
import { can } from '@Auth/Services/Auth';
import PlantService from '@Fields/Services/PlantService.js';

const props = defineProps({
  fields: Array,
  plant_types: Array,
  quarters: Array,
  toast: Object,
});

const canCreate = can('plants.create');
const canDestroy = can('plants.destroy');
const canEdit = can('plants.edit');
const canShow = can('plants.show');

const {
  error,
  load,
  loading,
  meta,
  query,
  records,
  search,
  setFilter,
  setPage,
  setPerPage,
  summary,
} = useCollection(PlantService.list, { perPage: 50, sort: 'code' });

const recordToDelete = ref(null);
const selectedIds = ref([]);
const toastMessage = ref(props.toast?.detail || '');
const toastTone = ref(props.toast?.severity === 'error' ? 'error' : 'success');

const selectedRecords = computed(() => records.value.filter((record) => selectedIds.value.includes(record.id)));
const allVisibleSelected = computed(() => records.value.length > 0 && selectedRecords.value.length === records.value.length);

const notify = (message, tone = 'success') => {
  toastMessage.value = message;
  toastTone.value = tone;
};

const toggleAll = () => {
  selectedIds.value = allVisibleSelected.value ? [] : records.value.map((record) => record.id);
};

const changePage = (page) => {
  selectedIds.value = [];
  setPage(page);
};

const changePerPage = (perPage) => {
  selectedIds.value = [];
  setPerPage(perPage);
};

const exportSelected = () => {
  if (!selectedRecords.value.length) {
    notify('Selecciona al menos una planta para exportar.', 'error');
    return;
  }

  const headers = ['Código', 'Campo', 'Cuartel', 'Tipo', 'Edad', 'Hilera'];
  const rows = selectedRecords.value.map((record) => [
    record.code,
    record.quarter?.field?.name || '',
    record.quarter?.name || '',
    record.plant_type?.name || '',
    record.age || '',
    record.row || '',
  ]);
  const csv = [headers, ...rows]
    .map((row) => row.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(','))
    .join('\n');
  const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
  const anchor = document.createElement('a');

  anchor.href = url;
  anchor.download = 'plantas-seleccionadas.csv';
  anchor.click();
  URL.revokeObjectURL(url);
  notify(`${selectedRecords.value.length} plantas fueron exportadas.`);
};

const deleteRecord = async () => {
  if (!recordToDelete.value) {
    return;
  }

  try {
    await PlantService.del(recordToDelete.value.id);
    recordToDelete.value = null;
    selectedIds.value = [];
    await load();
    notify('La planta fue eliminada correctamente.');
  } catch {
    notify('No fue posible eliminar la planta.', 'error');
  }
};

onMounted(load);
</script>

<template>
  <AuthenticatedLayout title="Plantas">
    <CollectionPageHeader
      title="Plantas"
      description="Consulta cada registro individual y actúa sobre los datos de plantación."
      :action-route="canCreate ? route('plants.create') : ''"
      action-label="Nueva planta"
    />

    <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumen de plantas">
      <CollectionMetricCard icon="potted_plant" label="Plantas" :value="formatNumber(summary.plants || 0, 0)" />
      <CollectionMetricCard icon="grid_view" label="Cuarteles" :value="formatNumber(summary.quarters || 0, 0)" />
      <CollectionMetricCard icon="map" label="Campos" :value="formatNumber(summary.fields || 0, 0)" />
      <CollectionMetricCard icon="category" label="Tipos de planta" :value="formatNumber(summary.types || 0, 0)" />
    </section>

    <section class="relative rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)]">
      <div class="border-b border-[#e9efea] p-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
          <div>
            <h2 class="text-xl font-bold text-[#102f27]">Registros de plantas</h2>
            <p class="mt-1 text-sm text-[#61716c]">Usa filtros y acciones sobre la selección visible.</p>
          </div>
          <button class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-[#17663a] px-3 text-sm font-semibold text-[#17663a] transition hover:bg-[#eef6ef] disabled:cursor-not-allowed disabled:opacity-50" type="button" :disabled="!selectedRecords.length" @click="exportSelected">
            <CollectionIcon name="download" :size="18" aria-hidden="true" />
            Exportar selección<span v-if="selectedRecords.length"> ({{ selectedRecords.length }})</span>
          </button>
        </div>

        <div class="mt-4 grid gap-2 xl:grid-cols-[minmax(260px,1fr)_repeat(3,minmax(150px,auto))]">
          <label class="relative block">
            <span class="sr-only">Buscar plantas</span>
            <CollectionIcon name="search" :size="20" class="absolute top-1/2 left-3 -translate-y-1/2 text-[#61716c]" aria-hidden="true" />
            <input v-model="query.q" class="h-10 w-full rounded-lg border border-[#d7e0d9] bg-white pr-3 pl-10 text-sm text-[#102f27] placeholder:text-[#8a9892] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]" type="search" placeholder="Buscar código, cuartel o campo..." @input="search">
          </label>
          <select class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]" :value="query.field_id" aria-label="Filtrar por campo" @change="setFilter('field_id', $event.target.value)">
            <option value="">Todos los campos</option>
            <option v-for="field in props.fields" :key="field.value" :value="field.value">{{ field.text }}</option>
          </select>
          <select class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]" :value="query.quarter_id" aria-label="Filtrar por cuartel" @change="setFilter('quarter_id', $event.target.value)">
            <option value="">Todos los cuarteles</option>
            <option v-for="quarter in props.quarters" :key="quarter.value" :value="quarter.value">{{ quarter.text }}</option>
          </select>
          <select class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]" :value="query.plant_type_id" aria-label="Filtrar por tipo" @change="setFilter('plant_type_id', $event.target.value)">
            <option value="">Todos los tipos</option>
            <option v-for="type in props.plant_types" :key="type.value" :value="type.value">{{ type.text }}</option>
          </select>
        </div>
      </div>

      <p v-if="error" class="m-4 rounded-lg border border-[#fecdd3] bg-[#fff5f6] p-3 text-sm text-[#be123c]">{{ error }}</p>

      <div v-if="loading" class="space-y-px p-4" aria-label="Cargando plantas">
        <div v-for="item in 8" :key="item" class="h-14 animate-pulse rounded-md bg-[#f1f5f2]" />
      </div>

      <div v-else-if="records.length" class="overflow-x-auto">
        <div class="min-w-[880px]">
          <div class="grid grid-cols-[48px_150px_minmax(220px,1fr)_170px_120px_48px] items-center gap-3 border-b border-[#e1e9e3] bg-[#f7faf7] px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#61716c]">
            <label class="flex items-center justify-center"><span class="sr-only">Seleccionar todos</span><input class="size-4 rounded border-[#9bb0a1] text-[#17663a] focus:ring-[#17663a]" type="checkbox" :checked="allVisibleSelected" @change="toggleAll"></label>
            <span>Código</span><span>Ubicación</span><span>Tipo</span><span>Edad</span><span class="sr-only">Acciones</span>
          </div>
          <article v-for="record in records" :key="record.id" class="grid grid-cols-[48px_150px_minmax(220px,1fr)_170px_120px_48px] items-center gap-3 border-b border-[#edf2ee] px-4 py-3 text-sm transition hover:bg-[#fcfdfc]">
            <label class="flex items-center justify-center"><span class="sr-only">Seleccionar {{ record.code }}</span><input v-model="selectedIds" class="size-4 rounded border-[#9bb0a1] text-[#17663a] focus:ring-[#17663a]" type="checkbox" :value="record.id"></label>
            <div class="font-bold text-[#102f27]">{{ record.code }}</div>
            <div class="min-w-0">
              <p class="truncate font-medium text-[#284238]">{{ record.quarter?.field?.name || 'Campo sin asignar' }}</p>
              <p class="mt-0.5 truncate text-xs text-[#75847d]">{{ record.quarter?.name || 'Cuartel sin asignar' }} · Hilera {{ record.row || '—' }}</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-[#e3f4e6] px-2.5 py-1 text-xs font-semibold text-[#17663a]">{{ record.plant_type?.name || 'Sin tipo' }}</span>
            <span class="text-[#456056]">{{ formatNumber(record.age || 0) }} años</span>
            <CollectionActionMenu :show-route="canShow ? route('plants.show', record.id) : ''" :edit-route="canEdit ? route('plants.edit', record.id) : ''" :show-destroy="canDestroy" @destroy="recordToDelete = record" />
          </article>
        </div>
      </div>

      <div v-else class="p-12 text-center">
        <CollectionIcon name="potted_plant" :size="40" class="text-[#9ab5a1]" aria-hidden="true" />
        <h3 class="mt-3 font-bold text-[#102f27]">No se encontraron plantas</h3>
        <p class="mt-1 text-sm text-[#61716c]">Ajusta los filtros o crea una nueva planta.</p>
      </div>

      <CollectionPagination :meta="meta" :per-page-options="[25, 50, 100]" @page="changePage" @per-page="changePerPage" />
    </section>

    <CollectionConfirmDialog :visible="Boolean(recordToDelete)" :message="`Eliminarás la planta ${recordToDelete?.code || ''}.`" @cancel="recordToDelete = null" @confirm="deleteRecord" />
    <CollectionToast :message="toastMessage" :tone="toastTone" @dismiss="toastMessage = ''" />
  </AuthenticatedLayout>
</template>
