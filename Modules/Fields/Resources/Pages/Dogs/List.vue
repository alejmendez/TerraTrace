<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import VSelect from '@Core/Components/Form/VSelect.vue';

import { getAge } from '@Core/Utils/date';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import Datatable from '@Core/Components/Table/Datatable.vue';
import DogService from '@Fields/Services/DogService.js';
import { defaultDeleteHandler } from '@Core/Utils/table.js';

import { can } from '@Auth/Services/Auth';

const props = defineProps({
  toast: Object,
  fields: Array,
  couples: Array,
  genders: Object,
});

const toast = useToast();
const confirm = useConfirm();

const datatable = ref(null);
const filter_field_options = props.fields;
const filter_couple_options = props.couples;
const filter_gender_options = props.genders;

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  field_id: { value: null, matchMode: FilterMatchMode.EQUALS },
  gender: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  breed: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  veterinary: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  couple_id: { value: null, matchMode: FilterMatchMode.EQUALS },
};

const canShow = can('dogs.show');
const canEdit = can('dogs.edit');
const canDestroy = can('dogs.destroy');
const canCreate = can('dogs.create');

const headerLinks = [];
if (canCreate) {
  headerLinks.push({ to: 'dogs.create', text: 'generics.new' });
}

const fetchHandler = async (params) => {
  return await DogService.list(params);
};

const deleteHandler = (record) => {
  defaultDeleteHandler(confirm, datatable, toast, () => DogService.del(record.id));
};

onMounted(async () => {
  if (props.toast) {
    toast.add(props.toast);
  }
});
</script>

<template>
  <AuthenticatedLayout :title="__('dog.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('dog.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'dogs.index', text: __('dog.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <Datatable
      ref="datatable"
      :filters="filters"
      :fetchHandler="fetchHandler"
      sortField="name"
      :sortOrder="1"
    >
      <Column field="name" :header="__('dog.table.name')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.name }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por nombre" />
        </template>
      </Column>

      <Column field="field_id" :header="__('dog.table.field')" :showFilterMatchModes="false" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.field.name }}
        </template>
        <template #filter="{ filterModel }">
          <VSelect v-model="filterModel.value" :options="filter_field_options" placeholder="Buscar por Campo" />
        </template>
      </Column>

      <Column field="gender" :header="__('dog.table.gender')" :showFilterMatchModes="false" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.gender }}
        </template>
        <template #filter="{ filterModel }">
          <VSelect v-model="filterModel.value" :options="filter_gender_options" placeholder="Buscar por Género" />
        </template>
      </Column>

      <Column field="breed" :header="__('dog.table.breed')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.breed }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" :placeholder="'Buscar por ' + __('dog.table.breed')" />
        </template>
      </Column>

      <Column field="age" :header="__('dog.table.age')" style="min-width: 100px">
        <template #body="{ data }">
          {{ getAge(data.birthdate) }}
        </template>
      </Column>

      <Column field="veterinary" :header="__('dog.table.veterinary')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.veterinary }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Veterinario" />
        </template>
      </Column>

      <Column field="couple_id" :header="__('dog.table.couple')" :showFilterMatchModes="false" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.couple?.full_name }}
        </template>
        <template #filter="{ filterModel }">
          <VSelect v-model="filterModel.value" :options="filter_couple_options" placeholder="Buscar por Pareja" />
        </template>
      </Column>

      <Column :exportable="false" style="min-width: 130px">
        <template #body="slotProps">
          <Link :href="route('dogs.show', slotProps.data.id)" v-if="canShow">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-sky-600">visibility</span>
          </Link>
          <Link :href="route('dogs.edit', slotProps.data.id)" v-if="canEdit">
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
