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
import QuarterService from '@Fields/Services/QuarterService.js';
import { defaultDeleteHandler } from '@Core/Utils/table.js';
import { can } from '@Auth/Services/Auth';
import { formatNumber } from '@Core/Utils/format';

const props = defineProps({
  toast: Object,
  fields: Array,
});

const toast = useToast();
const confirm = useConfirm();

const datatable = ref(null);
const filter_field_options = ref(props.fields);

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  'quarters.name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  field_id: { value: null, matchMode: FilterMatchMode.EQUALS },
  area: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
};

const canShow = can('quarters.show');
const canEdit = can('quarters.edit');
const canDestroy = can('quarters.destroy');
const canCreate = can('quarters.create');

const headerLinks = [];
if (canCreate) {
  headerLinks.push({ to: 'quarters.create', text: 'generics.new' });
}

const fetchHandler = async (params) => {
  return await QuarterService.list(params);
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
  <AuthenticatedLayout :title="__('quarter.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('quarter.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'quarters.index', text: __('quarter.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <Datatable
      ref="datatable"
      :filters="filters"
      :fetchHandler="fetchHandler"
      sortField="name"
      :sortOrder="1"
    >
      <Column field="name" filterField="quarters.name" :header="__('quarter.table.name')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.name }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por nombre" />
        </template>
      </Column>
      <Column field="field_name" filterField="field_id" :showFilterMatchModes="false" :header="__('quarter.table.field_id')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.field_name }}
        </template>
        <template #filter="{ filterModel }">
          <Select v-model="filterModel.value" :options="filter_field_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>
      <Column field="area" :header="__('quarter.table.area')" sortable style="min-width: 100px">
        <template #body="{ data }">
          {{ formatNumber(data.area) }} ha
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por area" />
        </template>
      </Column>
      <Column field="plants_count" :header="__('quarter.table.plants_count')" sortable style="min-width: 100px">
        <template #body="{ data }">
          {{ formatNumber(data.plants_count, 0) }}
        </template>
      </Column>

      <Column :exportable="false" style="min-width: 130px">
        <template #body="slotProps">
          <Link :href="route('quarters.show', slotProps.data.id)" v-if="canShow">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-sky-600">visibility</span>
          </Link>
          <Link :href="route('quarters.edit', slotProps.data.id)" v-if="canEdit">
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
