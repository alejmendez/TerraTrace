<script setup>
import { onMounted, ref } from 'vue';

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
import FieldService from '@Fields/Services/FieldService.js';

const props = defineProps({
  toast: Object,
});

const canCreate = can('fields.create');
const canDestroy = can('fields.destroy');
const canEdit = can('fields.edit');
const canShow = can('fields.show');

const {
  error,
  load,
  loading,
  meta,
  query,
  records,
  search,
  setPage,
  setPerPage,
  setSort,
  summary,
} = useCollection(FieldService.list, { perPage: 12, sort: 'name' });

const recordToDelete = ref(null);
const toastMessage = ref(props.toast?.detail || '');
const toastTone = ref(props.toast?.severity === 'error' ? 'error' : 'success');

const notify = (message, tone = 'success') => {
  toastMessage.value = message;
  toastTone.value = tone;
};

const deleteRecord = async () => {
  if (!recordToDelete.value) {
    return;
  }

  try {
    await FieldService.del(recordToDelete.value.id);
    recordToDelete.value = null;
    await load();
    notify('El campo fue eliminado correctamente.');
  } catch {
    notify('No fue posible eliminar el campo.', 'error');
  }
};

onMounted(load);
</script>

<template>
  <AuthenticatedLayout title="Campos">
    <CollectionPageHeader
      title="Campos"
      description="Administra la superficie, ubicación y producción de cada campo."
      :action-route="canCreate ? route('fields.create') : ''"
      action-label="Nuevo campo"
    />

    <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumen de campos">
      <CollectionMetricCard icon="map" label="Campos" :value="formatNumber(summary.fields || 0, 0)" />
      <CollectionMetricCard icon="landscape" label="Superficie total" :value="`${formatNumber(summary.area || 0)} ha`" />
      <CollectionMetricCard icon="grid_view" label="Cuarteles" :value="formatNumber(summary.quarters || 0, 0)" />
      <CollectionMetricCard icon="potted_plant" label="Plantas registradas" :value="formatNumber(summary.plants || 0, 0)" />
    </section>

    <section class="relative rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)]">
      <div class="flex flex-col gap-3 border-b border-[#e9efea] p-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-bold text-[#102f27]">Campos registrados</h2>
          <p class="mt-1 text-sm text-[#61716c]">Consulta cada campo sin depender de una tabla.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
          <label class="relative block">
            <span class="sr-only">Buscar campo</span>
            <CollectionIcon name="search" :size="20" class="absolute top-1/2 left-3 -translate-y-1/2 text-[#61716c]" aria-hidden="true" />
            <input
              v-model="query.q"
              class="h-10 w-full rounded-lg border border-[#d7e0d9] bg-white pr-3 pl-10 text-sm text-[#102f27] placeholder:text-[#8a9892] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1] sm:w-72"
              type="search"
              placeholder="Buscar campo o ubicación..."
              @input="search"
            >
          </label>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.sort"
            aria-label="Ordenar campos"
            @change="setSort($event.target.value)"
          >
            <option value="name">Ordenar por nombre</option>
            <option value="size">Ordenar por superficie</option>
            <option value="plants_count">Ordenar por plantas</option>
          </select>
        </div>
      </div>

      <p v-if="error" class="m-4 rounded-lg border border-[#fecdd3] bg-[#fff5f6] p-3 text-sm text-[#be123c]">{{ error }}</p>

      <div v-if="loading" class="grid gap-3 p-4 md:grid-cols-2" aria-label="Cargando campos">
        <div v-for="item in 6" :key="item" class="h-44 animate-pulse rounded-xl bg-[#f1f5f2]" />
      </div>

      <div v-else-if="records.length" class="grid gap-3 p-4 md:grid-cols-2">
        <article v-for="record in records" :key="record.id" class="flex min-w-0 gap-4 rounded-xl border border-[#e3ebe5] bg-[#fcfdfc] p-4 transition hover:border-[#bddcc6] hover:shadow-sm">
          <span class="flex size-16 shrink-0 items-center justify-center rounded-lg bg-[#e7f3e9] text-[#17663a]" aria-hidden="true">
            <CollectionIcon name="landscape" :size="36" />
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="truncate text-base font-bold text-[#102f27]">{{ record.name }}</h3>
                <p class="mt-1 flex items-center gap-1 truncate text-sm text-[#61716c]">
                  <CollectionIcon name="location_on" :size="16" aria-hidden="true" />
                  {{ record.location || 'Ubicación sin registrar' }}
                </p>
              </div>
              <CollectionActionMenu
                :show-route="canShow ? route('fields.show', record.id) : ''"
                :edit-route="canEdit ? route('fields.edit', record.id) : ''"
                :show-destroy="canDestroy"
                @destroy="recordToDelete = record"
              />
            </div>
            <dl class="mt-4 grid grid-cols-3 gap-2 border-t border-[#e7eee8] pt-3 text-sm">
              <div>
                <dt class="text-[#75847d]">Superficie</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ formatNumber(record.size) }} ha</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Cuarteles</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ formatNumber(record.quarters_count || 0, 0) }}</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Plantas</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ formatNumber(record.plants_count || 0, 0) }}</dd>
              </div>
            </dl>
          </div>
        </article>
      </div>

      <div v-else class="p-12 text-center">
        <CollectionIcon name="landscape" :size="40" class="text-[#9ab5a1]" aria-hidden="true" />
        <h3 class="mt-3 font-bold text-[#102f27]">No se encontraron campos</h3>
        <p class="mt-1 text-sm text-[#61716c]">Ajusta la búsqueda o crea un nuevo campo.</p>
      </div>

      <CollectionPagination :meta="meta" :per-page-options="[12, 24]" @page="setPage" @per-page="setPerPage" />
    </section>

    <CollectionConfirmDialog
      :visible="Boolean(recordToDelete)"
      :message="`Eliminarás el campo ${recordToDelete?.name || ''} y sus registros relacionados.`"
      @cancel="recordToDelete = null"
      @confirm="deleteRecord"
    />
    <CollectionToast :message="toastMessage" :tone="toastTone" @dismiss="toastMessage = ''" />
  </AuthenticatedLayout>
</template>
