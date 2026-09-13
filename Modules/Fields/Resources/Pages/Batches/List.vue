<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import VSelect from '@Core/Components/Form/VSelect.vue';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import Datatable from '@Core/Components/Table/Datatable.vue';
import BatchService from '@Fields/Services/BatchService.js';
import { stringToFormat } from '@Core/Utils/date';
import { formatNumber } from '@Core/Utils/format';
import { defaultDeleteHandler } from '@Core/Utils/table.js';
import { can } from '@Auth/Services/Auth';
import jsPDF from 'jspdf';

const props = defineProps({
  toast: Object,
  importers: Array,
});

const toast = useToast();
const confirm = useConfirm();

const datatable = ref(null);
const filter_importer_options = props.importers;

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  batch_number: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  delivery_date: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  importer_id: { value: null, matchMode: FilterMatchMode.EQUALS },
};

const canEdit = can('batches.edit');
const canDestroy = can('batches.destroy');

const headerLinks = [];
if (can('batches.create')) {
  headerLinks.push({ to: 'batches.create', text: 'generics.new' });
}

const fetchHandler = async (params) => {
  return await BatchService.list(params);
};

const deleteHandler = (record) => {
  defaultDeleteHandler(confirm, datatable, toast, () => BatchService.del(record.id));
};

const grToKg = (gr) => {
  return formatNumber(gr / 1000);
};

const printHandler = async (record) => {
  const batch = await BatchService.getOne(record.id);
  console.log(batch);
  // Datos de ejemplo, reemplazar por los datos reales del record

  const doc = new jsPDF();
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(18);
  doc.text('RESUMEN DE LOTE DE ENTREGA', 15, 20);
  doc.setFontSize(12);
  doc.setFont('helvetica', 'normal');
  doc.text(batch.field_name, 15, 28);

  // Lote y datos principales
  doc.setFont('helvetica', 'normal');
  doc.text('Lote', 15, 40);
  doc.setFont('helvetica', 'bold');
  doc.text(batch.batch_number, 60, 40);
  doc.setFont('helvetica', 'normal');
  doc.text('Fecha de entrega', 15, 48);
  doc.setFont('helvetica', 'bold');
  doc.text(stringToFormat(batch.delivery_date), 60, 48);
  doc.setFont('helvetica', 'normal');
  doc.text('Transportista', 15, 56);
  doc.setFont('helvetica', 'bold');
  doc.text(batch.carrier, 60, 56);
  doc.setFont('helvetica', 'normal');
  doc.text('Exportador', 15, 64);
  doc.setFont('helvetica', 'bold');
  doc.text(batch.importer_name, 60, 64);

  // Resumen de Batch
  doc.setFont('helvetica', 'normal');
  doc.text('Resumen de Batch', 15, 76);
  doc.setFont('helvetica', 'bold');
  doc.text('N°', 15, 84);
  doc.text('Batch', 30, 84);
  doc.text('Fecha', 90, 84);
  doc.text('Peso', 140, 84);
  doc.setFont('helvetica', 'normal');
  let y = 92;
  let n = 1;
  batch.harvests_elements.forEach((item) => {
    doc.text(String(n++), 15, y);
    doc.text(item.batch, 30, y);
    doc.text(item.date, 90, y);
    doc.text(grToKg(item.weight) + ' Kgs', 140, y);
    y += 8;
  });
  doc.setLineWidth(0.5);
  doc.line(15, y, 195, y);
  y += 8;
  doc.setFont('helvetica', 'bold');
  doc.text('TOTAL', 30, y);
  doc.text(grToKg(batch.total_weight) + ' Kgs', 140, y);

  // Peso actual del lote
  y += 20;
  doc.setFont('helvetica', 'normal');
  doc.text('Peso actual del Lote', 15, y);
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(22);
  doc.text(grToKg(batch.current_weight) + '', 70, y);
  doc.setFontSize(12);
  doc.setFont('helvetica', 'normal');
  doc.text('Kgs', 110, y);

  window.open(doc.output('bloburl'), '_blank');
};

onMounted(async () => {
  if (props.toast) {
    toast.add(props.toast);
  }
});
</script>

<template>
  <AuthenticatedLayout :title="__('batch.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('batch.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'batches.index', text: __('batch.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <Datatable
      ref="datatable"
      :filters="filters"
      :fetchHandler="fetchHandler"
      sortField="batch_number"
      :sortOrder="1"
    >
      <Column field="batch_number" :header="__('batch.table.batch_number')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.batch_number }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por número" />
        </template>
      </Column>

      <Column field="delivery_date" :header="__('batch.table.delivery_date')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ stringToFormat(data.delivery_date) }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Fecha de Entrega" />
        </template>
      </Column>

      <Column field="importer_id" :header="__('batch.table.importer_id')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.importer.name }}
        </template>
        <template #filter="{ filterModel }">
          <VSelect v-model="filterModel.value" :options="filter_importer_options" placeholder="Buscar por Exportador" />
        </template>
      </Column>

      <Column :exportable="false" style="min-width: 130px">
        <template #body="slotProps">
          <span
            class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-sky-600"
            @click="printHandler(slotProps.data)"
          >
            print
          </span>
          <Link :href="route('batches.edit', slotProps.data.id)" v-if="canEdit">
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
