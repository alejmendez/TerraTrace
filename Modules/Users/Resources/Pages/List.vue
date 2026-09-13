<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

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
  meta: Object,
  records: Array,
  roles: Array,
  summary: Object,
  toast: Object,
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const canCreate = can('users.create');
const canDestroy = can('users.destroy');
const canEdit = can('users.edit');
const canShow = can('users.show');

const initialQuery = () => {
  const params = new URLSearchParams(window.location.search);

  return {
    q: params.get('q') || '',
    role: params.get('role') || '',
    page: Number(params.get('page') || 1),
    per_page: Number(params.get('per_page') || 12),
    sort: params.get('sort') || 'full_name',
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
  if (query.role) params.role = query.role;
  if (query.page > 1) params.page = query.page;
  if (query.per_page !== 12) params.per_page = query.per_page;
  if (query.sort !== 'full_name') params.sort = query.sort;
  if (query.direction !== 'asc') params.direction = query.direction;
  return params;
};

const reloadList = (extra = {}) => {
  Object.assign(query, extra);
  query.page = extra.page ?? 1;

  router.get(route('users.index', buildParams()), {
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
  router.get(route('users.index', { ...buildParams(), page }), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    preserveScroll: true,
  });
  query.page = page;
};

const setPerPage = (perPage) => {
  router.get(route('users.index', { ...buildParams(), per_page: perPage, page: 1 }), {
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
  const name = recordToDelete.value.full_name || recordToDelete.value.name || 'el usuario';
  recordToDelete.value = null;

  router.delete(route('users.destroy', id), {
    only: ['records', 'meta', 'summary'],
    preserveState: true,
    onSuccess: () => notify(`Se eliminó el usuario ${name}.`),
    onError: () => notify('No fue posible eliminar el usuario.', 'error'),
  });
};

const totalUsuarios = computed(() => formatNumber(props.summary?.users || 0, 0));
const totalConRol = computed(() => formatNumber(props.summary?.roles || 0, 0));

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
  <AuthenticatedLayout title="Usuarios">
    <CollectionPageHeader
      title="Usuarios"
      description="Gestiona los accesos al sistema, asigna roles y consulta datos del equipo."
      :action-route="canCreate ? route('users.create') : ''"
      action-label="Nuevo usuario"
    />

    <section class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-2" aria-label="Resumen de usuarios">
      <CollectionMetricCard icon="person" label="Usuarios registrados" :value="totalUsuarios" />
      <CollectionMetricCard icon="category" label="Usuarios con rol" :value="totalConRol" />
    </section>

    <section class="relative rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)]">
      <div class="flex flex-col gap-3 border-b border-[#e9efea] p-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-bold text-[#102f27]">Usuarios registrados</h2>
          <p class="mt-1 text-sm text-[#61716c]">Busca por nombre, RUT, email o filtra por rol.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
          <label class="relative block sm:flex-1 sm:min-w-[200px]">
            <span class="sr-only">Buscar usuarios</span>
            <CollectionIcon name="search" :size="20" class="absolute top-1/2 left-3 -translate-y-1/2 text-[#61716c]" aria-hidden="true" />
            <input
              v-model="query.q"
              class="h-10 w-full rounded-lg border border-[#d7e0d9] bg-white pr-3 pl-10 text-sm text-[#102f27] placeholder:text-[#8a9892] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
              type="search"
              placeholder="Buscar nombre, RUT, email..."
              @input="onSearchInput"
            >
          </label>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.role"
            aria-label="Filtrar por rol"
            @change="setFilter('role', $event.target.value)"
          >
            <option value="">Todos los roles</option>
            <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.text }}</option>
          </select>
          <select
            class="h-10 rounded-lg border border-[#d7e0d9] bg-white px-3 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
            :value="query.sort"
            aria-label="Ordenar usuarios"
            @change="setSort($event.target.value)"
          >
            <option value="full_name">Ordenar por nombre</option>
            <option value="dni">Ordenar por RUT</option>
            <option value="phone">Ordenar por teléfono</option>
            <option value="email">Ordenar por correo</option>
          </select>
        </div>
      </div>

      <div v-if="records.length" class="grid gap-3 p-4 md:grid-cols-2">
        <article
          v-for="record in records"
          :key="record.id"
          class="flex min-w-0 gap-4 rounded-xl border border-[#e3ebe5] bg-[#fcfdfc] p-4 transition hover:border-[#bddcc6] hover:shadow-sm"
        >
          <img
            :src="record.avatar_url"
            :alt="record.full_name || record.name"
            class="size-16 shrink-0 rounded-full border border-[#d7e0d9] bg-[#e7f3e9] object-cover"
          >
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="truncate text-base font-bold text-[#102f27]">{{ record.full_name || record.name }}</h3>
                <p class="mt-1 flex items-center gap-1 truncate text-sm text-[#61716c]">
                  <CollectionIcon name="search" :size="14" class="hidden" aria-hidden="true" />
                  <a :href="`mailto:${record.email}`" class="truncate hover:text-[#17663a]">{{ record.email || 'Sin correo' }}</a>
                </p>
              </div>
              <CollectionActionMenu
                :show-route="canShow ? route('users.show', record.id) : ''"
                :edit-route="canEdit ? route('users.edit', record.id) : ''"
                :show-destroy="canDestroy && record.id !== currentUserId"
                @destroy="recordToDelete = record"
              />
            </div>
            <dl class="mt-4 grid grid-cols-3 gap-2 border-t border-[#e7eee8] pt-3 text-sm">
              <div>
                <dt class="text-[#75847d]">RUT</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ record.dni || 'Sin registrar' }}</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Teléfono</dt>
                <dd class="mt-1 font-semibold text-[#284238]">{{ record.phone || 'Sin registrar' }}</dd>
              </div>
              <div>
                <dt class="text-[#75847d]">Rol</dt>
                <dd class="mt-1">
                  <span
                    v-if="record.role"
                    class="role"
                    :class="record.role.slug"
                  >
                    {{ record.role.name }}
                  </span>
                  <span v-else class="text-xs font-semibold text-[#75847d]">Sin rol</span>
                </dd>
              </div>
            </dl>
          </div>
        </article>
      </div>

      <div v-else class="p-12 text-center">
        <CollectionIcon name="person" :size="40" class="text-[#9ab5a1]" aria-hidden="true" />
        <h3 class="mt-3 font-bold text-[#102f27]">No se encontraron usuarios</h3>
        <p class="mt-1 text-sm text-[#61716c]">Ajusta los filtros o crea un nuevo usuario.</p>
      </div>

      <CollectionPagination :meta="meta" :per-page-options="[12, 24]" @page="setPage" @per-page="setPerPage" />
    </section>

    <CollectionConfirmDialog
      :visible="Boolean(recordToDelete)"
      :message="`Eliminarás al usuario ${recordToDelete?.full_name || recordToDelete?.name || ''}. Esta acción no se puede deshacer.`"
      @cancel="recordToDelete = null"
      @confirm="deleteRecord"
    />
    <CollectionToast :message="toastMessage" :tone="toastTone" @dismiss="toastMessage = ''" />
  </AuthenticatedLayout>
</template>
