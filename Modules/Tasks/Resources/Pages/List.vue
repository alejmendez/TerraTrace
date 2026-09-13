<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import Tag from 'primevue/tag';

import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Select from 'primevue/select';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import Datatable from '@Core/Components/Table/Datatable.vue';
import TaskService from '@Fields/Services/TaskService.js';
import { dateToString } from '@Core/Utils/date.js';
import { defaultDeleteHandler } from '@Core/Utils/table.js';
import { can } from '@Auth/Services/Auth';

const props = defineProps({
  toast: Object,
  status: {
    type: Array,
    default: [],
  },
  task_states: Array,
  task_priorities: Array,
  responsibles: Array,
});

const toast = useToast();
const confirm = useConfirm();

const datatable = ref(null);
const filter_responsible_options = props.responsibles;

const filter_states_options = props.task_states.map((s) => ({ value: s.value, text: s.text }));
const statesSeverities = {
  to_begin: 'warn',
  started: 'info',
  stopped: 'secondary',
  overdued: 'danger',
  finished: 'success',
};

const filter_priorities_options = props.task_priorities.map((p) => ({ value: p.value, text: p.text }));

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  correlative: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  status: { value: filter_states_options.filter((s) => props.status.includes(s.value)), matchMode: FilterMatchMode.IN },
  priority: { value: null, matchMode: FilterMatchMode.EQUALS },
  updated_at: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  end_date: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  responsible_id: { value: null, matchMode: FilterMatchMode.EQUALS },
};

const canShow = can('tasks.show');
const canEdit = can('tasks.edit');
const canDestroy = can('tasks.destroy');

const headerLinks = [];
if (can('tasks.create')) {
  headerLinks.push({ to: 'tasks.create', text: 'generics.new' });
}

const fetchHandler = async (params) => {
  if (!params.filters) {
    params.filters = {};
  }
  if (props.status && params.filters.status == null) {
    params.filters.status = {
      value: filter_states_options.filter((s) => props.status.includes(s.value)),
      matchMode: FilterMatchMode.IN,
    };
  }
  return await TaskService.list(params);
};

const deleteHandler = (record) => {
  defaultDeleteHandler(confirm, datatable, toast, () => TaskService.del(record.id));
};

onMounted(async () => {
  if (props.toast) {
    toast.add(props.toast);
  }
});
</script>

<template>
  <AuthenticatedLayout :title="__('task.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('task.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'tasks.index', text: __('task.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <Datatable
      ref="datatable"
      :filters="filters"
      :fetchHandler="fetchHandler"
      sortField="updated_at"
      :sortOrder="-1"
    >
      <Column field="correlative" filterField="correlative" :header="__('task.table.correlative')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.correlative }}
        </template>
      </Column>
      <Column field="name" filterField="name" :header="__('task.table.name')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.name }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por nombre" />
        </template>
      </Column>
      <Column field="status" filterField="status" :showFilterMatchModes="false" :header="__('task.table.status')" sortable style="min-width: 200px">
        <template #body="{ data }">
          <Tag :severity="statesSeverities[data.status]" :value="__('task.form.status.options.' + data.status)"></Tag>
        </template>
        <template #filter="{ filterModel }">
          <MultiSelect v-model="filterModel.value" :options="filter_states_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>
      <Column field="priority" filterField="priority" :showFilterMatchModes="false" :header="__('task.table.priority')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ __('task.form.priority.options.' + data.priority) }}
        </template>
        <template #filter="{ filterModel }">
          <Select v-model="filterModel.value" :options="filter_priorities_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>
      <Column field="updated_at" filterField="updated_at" :header="__('task.table.updated_at')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ dateToString(data.updated_at) }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por fecha de actualización" />
        </template>
      </Column>
      <Column field="end_date" filterField="end_date" :header="__('task.table.end_date')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ dateToString(data.end_date) }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por fecha de fin" />
        </template>
      </Column>
      <Column field="responsible.name" filterField="responsible_id" :showFilterMatchModes="false" :header="__('task.table.responsible')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.responsible?.full_name }}
        </template>
        <template #filter="{ filterModel }">
          <Select v-model="filterModel.value" :options="filter_responsible_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>

      <Column :exportable="false" style="min-width: 130px">
        <template #body="slotProps">
          <Link :href="route('tasks.show', slotProps.data.id)" v-if="canShow">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-sky-600">visibility</span>
          </Link>
          <Link :href="route('tasks.edit', slotProps.data.id)" v-if="canEdit">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-emerald-600">edit</span>
          </Link>
          <span
            class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-pink-600"
            @click="deleteHandler(slotProps.data)"
            v-if="canDestroy"
          >
            delete
          </span>
        </template>
      </Column>
    </Datatable>
  </AuthenticatedLayout>
</template>
