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
import QuarterService from '@Fields/Services/QuarterService.js';

const props = defineProps({
  fields: Array,
  toast: Object,
});

const canCreate = can('quarters.create');
const canDestroy = can('quarters.destroy');
const canEdit = can('quarters.edit');
const canShow = can('quarters.show');

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
  setSort,
  summary,
} = useCollection(QuarterService.list, { perPage: 12, sort: 'name' });

const collapsedFields = ref([]);
const recordToDelete = ref(null);
const toastMessage = ref(props.toast?.detail || '');
const toastTone = ref(props.toast?.severity === 'error' ? 'error' : 'success');

const groups = computed(() => {
  const grouped = new Map();

  records.value.forEach((record) => {
    const field = record.field || { id: 'unassigned', name: 'Campo sin asignar' };
    const group = grouped.get(field.id) || { ...field, quarters: [], area: 0, plants: 0 };
    group.quarters.push(record);
    group.area += Number(record.area || 0);
    group.plants += Number(record.plants_count || 0);
    grouped.set(field.id, group);
  });

  return Array.from(grouped.values());
});

const isExpanded = (id) => !collapsedFields.value.includes(id);
const toggleGroup = (id) => {
  collapsedFields.value = isExpanded(id)
    ? collapsedFields.value.filter((fieldId) => fieldId !== id)
    : [...collapsedFields.value, id];
};

const notify = (message, tone = 'success') => {
  toastMessage.value = message;
  toastTone.value = tone;
};

const deleteRecord = async () => {
  if (!recordToDelete.value) {
    return;
  }

  try {
    await QuarterService.del(recordToDelete.value.id);
    recordToDelete.value = null;
    await load();
    notify('El cuartel fue eliminado correctamente.');
  } catch {
    notify('No fue posible eliminar el cuartel.', 'error');
  }
};

onMounted(load);
</script>

<template>
  <AuthenticatedLayout title="Cuarteles">
    <CollectionPageHeader
      title="Cuarteles"
      description="Organiza y supervisa los sectores productivos de cada campo."
      :action-route="canCreate ? route('quarters.create') : ''"
      action-label="Nuevo cuartel"
    />

    <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumen de cuarteles">
      <CollectionMetricCard icon="grid_view" label="Cuarteles" :value="formatNumber(summary.quarters || 0, 0)" />
      <CollectionMetricCard icon="map" label="Campos" :value="formatNumber(summary.fields || 0, 0)" />
      <CollectionMetricCard icon="landscape" label="Superficie total" :value="`${formatNumber(summary.area || 0)} ha`" />
      <CollectionMetricCard icon="potted_plant" label="Plantas registradas" :value="formatNumber(summary.plants || 0, 0)" />
    </section>

    <section class="relative rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)]">
      <div class="flex flex-col gap-3 border-b border-[#e9efea] p-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-bold text-[#102f27]">Cuarteles por campo</h2>
          <p class="mt-1 text-sm text-[#61716c]">Expande cada campo para revisar sus cuarteles.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
          <label class="relative block">
            <span class="sr-only">Buscar cuartel</span>
            <CollectionIcon name="search" :size="20" class="absolute top-1/2 left-3 -translate-y-1/2 text-[#61716c]" aria-hidden="true" />
            <input v-model="query.q" class="h-10 w-full rounded-lg border border-[#d7e0d9] bg-white pr-3 pl-10 text-sm text-[#102f27] placeholder:text-[#8a9892] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1] sm:w-72" type="search" placeholder="Buscar cuartel o campo..." @input="search">
          </label>
          <select class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]" :value="query.field_id" aria-label="Filtrar por campo" @change="setFilter('field_id', $event.target.value)">
            <option value="">Todos los campos</option>
            <option v-for="field in props.fields" :key="field.value" :value="field.value">{{ field.text }}</option>
          </select>
          <select class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]" :value="query.sort" aria-label="Ordenar cuarteles" @change="setSort($event.target.value)">
            <option value="name">Ordenar por nombre</option>
            <option value="area">Ordenar por superficie</option>
            <option value="plants_count">Ordenar por plantas</option>
          </select>
        </div>
      </div>

      <p v-if="error" class="m-4 rounded-lg border border-[#fecdd3] bg-[#fff5f6] p-3 text-sm text-[#be123c]">{{ error }}</p>

      <div v-if="loading" class="space-y-3 p-4" aria-label="Cargando cuarteles">
        <div v-for="item in 3" :key="item" class="h-36 animate-pulse rounded-xl bg-[#f1f5f2]" />
      </div>

      <div v-else-if="groups.length" class="space-y-3 p-4">
        <section v-for="group in groups" :key="group.id" class="relative rounded-xl border border-[#e1e9e3]">
          <button class="flex w-full items-center gap-3 bg-[#f7faf7] px-4 py-3 text-left transition hover:bg-[#eef6ef]" type="button" :aria-expanded="isExpanded(group.id)" @click="toggleGroup(group.id)">
            <span class="flex size-9 items-center justify-center rounded-lg bg-[#e3f4e6] text-[#17663a]" aria-hidden="true"><CollectionIcon name="map" :size="20" /></span>
            <span class="min-w-0 flex-1">
              <span class="block truncate font-bold text-[#102f27]">{{ group.name }}</span>
              <span class="mt-0.5 block text-sm text-[#61716c]">{{ group.quarters.length }} cuarteles · {{ formatNumber(group.area) }} ha · {{ formatNumber(group.plants, 0) }} plantas</span>
            </span>
            <CollectionIcon :name="isExpanded(group.id) ? 'expand_less' : 'expand_more'" :size="20" class="text-[#456056]" aria-hidden="true" />
          </button>

          <div v-if="isExpanded(group.id)" class="divide-y divide-[#e9efea]">
            <article v-for="record in group.quarters" :key="record.id" class="flex items-center gap-3 px-4 py-3 hover:bg-[#fcfdfc]">
              <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#fff1d4] text-[#a96400]" aria-hidden="true"><CollectionIcon name="agriculture" :size="20" /></span>
              <div class="min-w-0 flex-1">
                <h3 class="font-semibold text-[#102f27]">{{ record.name }}</h3>
                <p class="mt-0.5 text-sm text-[#61716c]">{{ formatNumber(record.area) }} ha · {{ formatNumber(record.plants_count || 0, 0) }} plantas</p>
              </div>
              <span class="hidden rounded-full bg-[#e3f4e6] px-2.5 py-1 text-xs font-semibold text-[#17663a] sm:inline-flex">Registrado</span>
              <CollectionActionMenu :show-route="canShow ? route('quarters.show', record.id) : ''" :edit-route="canEdit ? route('quarters.edit', record.id) : ''" :show-destroy="canDestroy" @destroy="recordToDelete = record" />
            </article>
          </div>
        </section>
      </div>

      <div v-else class="p-12 text-center">
        <CollectionIcon name="grid_view" :size="40" class="text-[#9ab5a1]" aria-hidden="true" />
        <h3 class="mt-3 font-bold text-[#102f27]">No se encontraron cuarteles</h3>
        <p class="mt-1 text-sm text-[#61716c]">Ajusta los filtros o crea un nuevo cuartel.</p>
      </div>

      <CollectionPagination :meta="meta" :per-page-options="[12, 24]" @page="setPage" @per-page="setPerPage" />
    </section>

    <CollectionConfirmDialog :visible="Boolean(recordToDelete)" :message="`Eliminarás el cuartel ${recordToDelete?.name || ''} y sus plantas relacionadas.`" @cancel="recordToDelete = null" @confirm="deleteRecord" />
    <CollectionToast :message="toastMessage" :tone="toastTone" @dismiss="toastMessage = ''" />
  </AuthenticatedLayout>
</template>
