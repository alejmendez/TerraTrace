<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import Datatable from '@Core/Components/Table/Datatable.vue';
import PlantService from '@Fields/Services/PlantService.js';
import { defaultDeleteHandler } from '@Core/Utils/table.js';
import { can } from '@Auth/Services/Auth';
import { formatNumber } from '@Core/Utils/format';

const props = defineProps({
  toast: Object,
  fields: Array,
  quarters: Array,
  plant_types: Array,
  responsible: Array,
});

const toast = useToast();
const confirm = useConfirm();

const datatable = ref(null);
const filter_field_options = ref(props.fields);
const filter_quarter_options = ref(props.quarters);
const filter_plant_type_options = ref(props.plant_types);
const filter_responsible_options = ref(props.responsible);

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  code: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  quarter_id: { value: null, matchMode: FilterMatchMode.EQUALS },
  'quarter.field_id': { value: null, matchMode: FilterMatchMode.EQUALS },
  plant_type_id: { value: null, matchMode: FilterMatchMode.EQUALS },
  age: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  'quarter.responsible_id': { value: null, matchMode: FilterMatchMode.EQUALS },
};

const canCreate = can('plants.create');
const canShow = can('plants.show');
const canEdit = can('plants.edit');
const canDestroy = can('plants.destroy');

const headerLinks = [];
if (canCreate) {
  headerLinks.push({ to: 'plants.create', text: 'generics.new' });
}

const fetchHandler = async (params) => {
  return await PlantService.list(params);
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
  <AuthenticatedLayout :title="__('plant.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('plant.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'plants.index', text: __('plant.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <Datatable
      ref="datatable"
      :filters="filters"
      :fetchHandler="fetchHandler"
      sortField="code"
      :sortOrder="1"
    >
      <Column field="code" filterField="code" :header="__('plant.table.name')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.code }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Codigo" />
        </template>
      </Column>
      <Column field="quarter.name" filterField="quarter_id" :showFilterMatchModes="false" :header="__('plant.table.quarter_id')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.quarter?.name }}
        </template>
        <template #filter="{ filterModel }">
          <Select v-model="filterModel.value" :options="filter_quarter_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>
      <Column field="field.name" filterField="quarter.field_id" :showFilterMatchModes="false" :header="__('plant.table.field_id')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.quarter?.field?.name }}
        </template>
        <template #filter="{ filterModel }">
          <Select v-model="filterModel.value" :options="filter_field_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>
      <Column field="plant_type.name" filterField="plant_type_id" :showFilterMatchModes="false" :header="__('plant.table.type')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.plant_type?.name }}
        </template>
        <template #filter="{ filterModel }">
          <Select v-model="filterModel.value" :options="filter_plant_type_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>
      <Column field="age" :header="__('plant.table.age')" sortable style="min-width: 100px">
        <template #body="{ data }">
          {{ formatNumber(data.age) }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por edad" />
        </template>
      </Column>
      <Column field="quarter.responsible.name" filterField="quarter.responsible_id" :showFilterMatchModes="false" :header="__('plant.table.manager')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.quarter?.responsible?.full_name }}
        </template>
        <template #filter="{ filterModel }">
          <Select v-model="filterModel.value" :options="filter_responsible_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>

      <Column :exportable="false" style="min-width: 130px">
        <template #body="slotProps">
          <!--
            <Button icon="pi pi-eye" class="me-2" severity="secondary" size="small" v-if="canShow" />
            <Button icon="pi pi-pencil" class="me-2" severity="info" size="small" v-if="canEdit" />
            <Button icon="pi pi-trash" class="me-2" severity="danger" size="small" v-if="canDestroy" @click="deleteHandler(slotProps.data)" />
          -->

          <Link :href="route('plants.show', slotProps.data.id)" v-if="canShow">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-sky-600">visibility</span>
          </Link>
          <Link :href="route('plants.edit', slotProps.data.id)" v-if="canEdit">
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
