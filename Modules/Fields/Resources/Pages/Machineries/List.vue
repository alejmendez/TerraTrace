<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import Datatable from '@Core/Components/Table/Datatable.vue';
import MachineryService from '@Fields/Services/MachineryService.js';
import { stringToFormat } from '@Core/Utils/date';
import { defaultDeleteHandler } from '@Core/Utils/table.js';
import { can } from '@Auth/Services/Auth';
const props = defineProps({
  toast: Object,
});

const toast = useToast();
const confirm = useConfirm();

const datatable = ref(null);

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  purchase_date: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  last_maintenance: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  purchase_location: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  contact: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
};

const canEdit = can('machineries.edit');
const canDestroy = can('machineries.destroy');
const canCreate = can('machineries.create');

const headerLinks = [];
if (canCreate) {
  headerLinks.push({ to: 'machineries.create', text: 'generics.new' });
}
const fetchHandler = async (params) => {
  return await MachineryService.list(params);
};

const deleteHandler = (record) => {
  defaultDeleteHandler(confirm, datatable, toast, () => TaskService.del(record.id));
};

onMounted(() => {
  if (props.toast) {
    toast.add(props.toast);
  }
});
</script>
<template>
  <AuthenticatedLayout :title="__('machinery.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('machinery.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'machineries.index', text: __('machinery.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <Datatable
      ref="datatable"
      :filters="filters"
      :fetchHandler="fetchHandler"
      sortField="name"
      :sortOrder="1"
    >
      <Column field="name" :header="__('machinery.table.name')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.name }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por nombre" />
        </template>
      </Column>

      <Column field="purchase_date" :header="__('machinery.table.purchase_date')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ stringToFormat(data.purchase_date) }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Fecha de Compra" />
        </template>
      </Column>

      <Column field="last_maintenance" :header="__('machinery.table.last_maintenance')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.last_maintenance ? stringToFormat(data.last_maintenance) : 's/i' }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por última Mantención" />
        </template>
      </Column>

      <Column field="purchase_location" :header="__('machinery.table.purchase_location')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.purchase_location }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Lugar de Compra" />
        </template>
      </Column>

      <Column field="contact" :header="__('machinery.table.contact')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.contact }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Contacto" />
        </template>
      </Column>

      <Column :exportable="false" style="min-width: 130px">
        <template #body="slotProps">
          <Link :href="route('machineries.edit', slotProps.data.id)" v-if="canEdit">
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
