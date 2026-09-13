<script setup>
import { ref, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

import slugify from '@Core/Utils/slugify';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import Datatable from '@Core/Components/Table/Datatable.vue';
import UserService from '@Users/Services/UserService.js';
import { deleteRowDatatable } from '@Core/Utils/table.js';
import { can } from '@Auth/Services/Auth';

const props = defineProps({
  toast: Object,
  roles: Array,
});

const toast = useToast();
const confirm = useConfirm();

const page = usePage();
const current_user_id = page.props.auth.user.id;

const datatable = ref(null);
const filter_role_options = ref(props.roles);

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  dni: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  phone: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  'roles.name': { value: null, matchMode: FilterMatchMode.EQUALS },
  email: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
};

const canShow = can('users.show');
const canEdit = can('users.edit');
const canDestroy = can('users.destroy');

const headerLinks = [];
if (can('users.create')) {
  headerLinks.push({ to: 'users.create', text: 'generics.new' });
}

const fetchHandler = async (params) => {
  return await UserService.list(params);
};

const deleteHandler = async (record) => {
  const options = {
    datatable,
    confirm,
    toast,
    handler: () => UserService.del(record.id),
  };

  deleteRowDatatable(options);
};

onMounted(async () => {
  if (props.toast) {
    toast.add(props.toast);
  }
});
</script>

<template>
  <AuthenticatedLayout :title="__('user.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('user.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'users.index', text: __('user.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <Datatable
      ref="datatable"
      :filters="filters"
      :fetchHandler="fetchHandler"
      sortField="full_name"
      :sortOrder="1"
    >
      <Column field="name" :header="__('user.table.name')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.full_name }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por nombre" />
        </template>
      </Column>

      <Column field="dni" :header="__('user.table.dni')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.dni }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Dni" />
        </template>
      </Column>

      <Column field="phone" :header="__('user.table.phone')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.phone }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Telefono" />
        </template>
      </Column>

      <Column field="roles.name" :header="__('user.table.role')" :showFilterMatchModes="false" style="min-width: 200px">
        <template #body="{ data }">
          <span
            v-for="role in data.roles"
            class="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-bold leading-none border rounded-full role"
            :class="slugify(role.name)"
          >
            {{ role.name }}
          </span>
        </template>
        <template #filter="{ filterModel }">
          <Select v-model="filterModel.value" :options="filter_role_options" optionLabel="text" placeholder="Todos" />
        </template>
      </Column>

      <Column field="email" :header="__('user.table.email')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.email }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Email" />
        </template>
      </Column>

      <Column :exportable="false" style="min-width: 130px">
        <template #body="slotProps">
          <Link :href="route('users.show', slotProps.data.id)" v-if="canShow">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-sky-600">visibility</span>
          </Link>
          <Link :href="route('users.edit', slotProps.data.id)" v-if="canEdit">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-emerald-600">edit</span>
          </Link>
          <span
            class="material-symbols-rounded mr-4 cursor-pointer transition-all text-slate-500 hover:text-pink-600"
            @click="deleteHandler(slotProps.data)"
            v-if="canDestroy && slotProps.data.id !== current_user_id"
          >
            delete
          </span>
        </template>
      </Column>
    </Datatable>
  </AuthenticatedLayout>
</template>
