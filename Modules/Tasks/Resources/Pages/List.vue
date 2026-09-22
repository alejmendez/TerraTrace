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
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';
import CollectionMultiSelect from '@Core/Components/Collection/CollectionMultiSelect.vue';
import CollectionToast from '@Core/Components/Collection/CollectionToast.vue';
import { formatNumber } from '@Core/Utils/format';
import { dateToString } from '@Core/Utils/date';
import { can } from '@Auth/Services/Auth';

const props = defineProps({
  meta: Object,
  records: Array,
  responsibles: Array,
  summary: Object,
  task_priorities: Array,
  task_states: Array,
  toast: Object,
});

const canCreate = can('tasks.create');
const canDestroy = can('tasks.destroy');
const canEdit = can('tasks.edit');
const canShow = can('tasks.show');

const stateSeverities = {
  to_begin: 'warn',
  started: 'info',
  stopped: 'secondary',
  overdued: 'danger',
  finished: 'success',
};

const stateBadgeClass = (status) => {
  const severity = stateSeverities[status] || 'secondary';
  return {
    warn: 'bg-amber-50 text-amber-700 border-amber-200',
    info: 'bg-sky-50 text-sky-700 border-sky-200',
    secondary: 'bg-slate-50 text-slate-600 border-slate-200',
    danger: 'bg-rose-50 text-rose-700 border-rose-200',
    success: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  }[severity];
};

const stateLabel = (status) => {
  const state = props.task_states.find((s) => s.value === status);
  return state ? state.text : status;
};

const priorityLabel = (priority) => {
  const p = props.task_priorities.find((item) => item.value === priority);
  return p ? p.text : priority;
};

const initialQuery = () => {
  const params = new URLSearchParams(window.location.search);

  return {
    q: params.get('q') || '',
    status: params.get('status') || '',
    priority: params.get('priority') || '',
    responsible_id: params.get('responsible_id') || '',
    page: Number(params.get('page') || 1),
    per_page: Number(params.get('per_page') || 12),
    sort: params.get('sort') || 'updated_at',
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
  if (query.status) params.status = query.status;
  if (query.priority) params.priority = query.priority;
  if (query.responsible_id) params.responsible_id = query.responsible_id;
  if (query.page > 1) params.page = query.page;
  if (query.per_page !== 12) params.per_page = query.per_page;
  if (query.sort !== 'updated_at') params.sort = query.sort;
  if (query.direction !== 'desc') params.direction = query.direction;
  return params;
};

const reloadList = (extra = {}) => {
  Object.assign(query, extra);
  query.page = extra.page ?? 1;

  router.get(route('tasks.index', buildParams()), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    preserveScroll: true,
  });
};

const onSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => reloadList({ page: 1 }), 250);
};

const onStatusChange = (selected) => {
  const values = (selected ?? []).map((option) => option.value).filter(Boolean);
  reloadList({ status: values.join(','), page: 1 });
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
  router.get(route('tasks.index', { ...buildParams(), page }), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    preserveScroll: true,
  });
  query.page = page;
};

const setPerPage = (perPage) => {
  router.get(route('tasks.index', { ...buildParams(), per_page: perPage, page: 1 }), {
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
  const name = recordToDelete.value.name || 'la tarea';
  recordToDelete.value = null;

  router.delete(route('tasks.destroy', id), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    onSuccess: () => notify(`Se eliminó ${name}.`),
    onError: () => notify('No fue posible eliminar la tarea.', 'error'),
  });
};

const totalTareas = computed(() => formatNumber(props.summary?.tasks || 0, 0));
const totalAsignadas = computed(() => formatNumber(props.summary?.assigned || 0, 0));

const selectedStatuses = computed(() => (query.status ? query.status.split(',').filter(Boolean) : []));

const selectedStatusOptions = computed(() =>
    selectedStatuses.value
        .map((value) => props.task_states.find((s) => s.value == value))
        .filter(Boolean)
);

const selectedPriorityOption = computed(() =>
    props.task_priorities.find((p) => p.value == query.priority) ?? null
);

const selectedResponsibleOption = computed(() =>
    props.responsibles.find((r) => r.value == query.responsible_id) ?? null
);

// `__` is registered as app.config.globalProperties (see
// Modules/Core/Libs/i18n.js) so it's only available inside templates,
// not in <script setup>. These labels are local filter strings on a
// Spanish-only page, so hardcoding is simpler than threading an
// injection just for them.
const sort_options = [
    { value: 'updated_at', text: 'Ordenar por última actualización' },
    { value: 'end_date', text: 'Ordenar por fecha de fin' },
    { value: 'name', text: 'Ordenar por nombre' },
    { value: 'correlative', text: 'Ordenar por correlativo' },
];

const selectedSortOption = computed(() =>
    sort_options.find((s) => s.value === query.sort) ?? sort_options[0]
);

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
  <AuthenticatedLayout title="Tareas">
    <CollectionPageHeader
      title="Tareas"
      description="Administra las tareas del campo, asigna responsables y consulta el estado."
      :action-route="canCreate ? route('tasks.create') : ''"
      action-label="Nueva tarea"
    />

    <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-2" aria-label="Resumen de tareas">
      <CollectionMetricCard icon="category" label="Tareas registradas" :value="totalTareas" />
      <CollectionMetricCard icon="person" label="Asignadas a responsable" :value="totalAsignadas" />
    </section>

    <section class="relative rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)]">
      <div class="flex flex-col gap-3 border-b border-[#e9efea] p-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-bold text-[#102f27]">Listado de tareas</h2>
          <p class="mt-1 text-sm text-[#61716c]">Busca por nombre, correlativo o responsable; filtra por estado y prioridad.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
          <label class="relative block sm:flex-1 sm:min-w-[200px]">
            <span class="sr-only">Buscar tareas</span>
            <CollectionIcon name="search" :size="20" class="absolute top-1/2 left-3 -translate-y-1/2 text-[#61716c]" aria-hidden="true" />
            <input
              v-model="query.q"
              class="h-10 w-full rounded-lg border border-[#d7e0d9] bg-white pr-3 pl-10 text-sm text-[#102f27] placeholder:text-[#8a9892] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
              type="search"
              placeholder="Buscar nombre, correlativo..."
              @input="onSearchInput"
            >
          </label>
          <CollectionMultiSelect
            class-wrapper="sm:min-w-[180px] sm:flex-1"
            :model-value="selectedStatusOptions"
            :options="props.task_states"
            :placeholder="__('Filtrar por estado')"
            aria-label="Filtrar por estado"
            @change="onStatusChange"
          />
          <CollectionSelect
            class-wrapper="sm:min-w-[160px] sm:flex-1"
            :model-value="selectedPriorityOption"
            :options="props.task_priorities"
            :placeholder="__('Todas las prioridades')"
            aria-label="Filtrar por prioridad"
            @change="(opt) => setFilter('priority', opt?.value ?? '')"
          />
          <CollectionSelect
            class-wrapper="sm:min-w-[180px] sm:flex-1"
            :model-value="selectedResponsibleOption"
            :options="props.responsibles"
            :placeholder="__('Todos los responsables')"
            aria-label="Filtrar por responsable"
            @change="(opt) => setFilter('responsible_id', opt?.value ?? '')"
          />
          <CollectionSelect
            class-wrapper="sm:min-w-[200px] sm:flex-1"
            :model-value="selectedSortOption"
            :options="sort_options"
            :placeholder="__('Ordenar por')"
            aria-label="Ordenar tareas"
            @change="(opt) => setSort(opt?.value)"
          />
        </div>
      </div>

      <div v-if="records.length" class="grid gap-3 p-4">
        <article
          v-for="record in records"
          :key="record.id"
          class="flex min-w-0 gap-4 rounded-xl border border-[#e3ebe5] bg-[#fcfdfc] p-4 transition hover:border-[#bddcc6] hover:shadow-sm"
        >
          <span class="flex size-16 shrink-0 flex-col items-center justify-center rounded-lg bg-[#e7f3e9] px-2 text-center text-[#17663a]" aria-hidden="true">
            <span class="text-xs font-semibold uppercase tracking-wide text-[#75847d]">N°</span>
            <span class="text-base font-extrabold text-[#102f27]">{{ record.correlative }}</span>
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="truncate text-base font-bold text-[#102f27]">{{ record.name }}</h3>
                <p class="mt-1 truncate text-sm text-[#61716c]">
                  {{ record.responsible?.full_name || 'Sin responsable asignado' }}
                </p>
              </div>
              <CollectionActionMenu
                :show-route="canShow ? route('tasks.show', record.id) : ''"
                :edit-route="canEdit ? route('tasks.edit', record.id) : ''"
                :show-destroy="canDestroy"
                @destroy="recordToDelete = record"
              />
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-2 border-t border-[#e7eee8] pt-3 text-sm sm:grid-cols-4">
              <div>
                <dt class="text-[#75847d]">Estado</dt>
                <dd class="mt-1">
                  <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold" :class="stateBadgeClass(record.status)">
                    {{ stateLabel(record.status) }}
                  </span>
                </dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Prioridad</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ priorityLabel(record.priority) }}</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Fecha de fin</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ record.end_date ? dateToString(record.end_date) : 'Sin fecha' }}</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Última actualización</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ record.updated_at ? dateToString(record.updated_at) : 'Sin registro' }}</dd>
              </div>
            </dl>
          </div>
        </article>
      </div>

      <div v-else class="p-12 text-center">
        <CollectionIcon name="category" :size="40" class="text-[#9ab5a1]" aria-hidden="true" />
        <h3 class="mt-3 font-bold text-[#102f27]">No se encontraron tareas</h3>
        <p class="mt-1 text-sm text-[#61716c]">Ajusta los filtros o crea una nueva tarea.</p>
      </div>

      <CollectionPagination :meta="meta" :per-page-options="[12, 24]" @page="setPage" @per-page="setPerPage" />
    </section>

    <CollectionConfirmDialog
      :visible="Boolean(recordToDelete)"
      :message="`Eliminarás la tarea ${recordToDelete?.name || ''}. Esta acción no se puede deshacer.`"
      @cancel="recordToDelete = null"
      @confirm="deleteRecord"
    />
    <CollectionToast :message="toastMessage" :tone="toastTone" @dismiss="toastMessage = ''" />
  </AuthenticatedLayout>
</template>
