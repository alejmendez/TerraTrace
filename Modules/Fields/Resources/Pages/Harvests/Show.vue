<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { getWeek } from 'date-fns';
import { useConfirm } from 'primevue/useconfirm';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';

import CardSection from '@Core/Components/CardSection.vue';
import VInput from '@Core/Components/Form/VInput.vue';

import Button from '@Core/Components/Form/Button.vue';

import { can } from '@Auth/Services/Auth';
import { deleteRowTable } from '@Core/Utils/table';
import { formatNumber } from '@Core/Utils/format';

const props = defineProps({
  data: Object,
  quarters: Array,
  dogs: Array,
  users: Array,
  plant_codes: Array,
  qualities: Array,
  date_rendered: String,
});

const { data } = props.data;

const confirm = useConfirm();

const quarters = ref(data.quarters.map((q) => q.name).join(', '));

const canEdit = can('harvests.edit');
const canDestroy = can('harvests.destroy');

const deleteHandler = async (id) => {
  await deleteRowTable(confirm, () => {
    router.delete(route('harvests.destroy', id));
  });
};
</script>

<template>
  <AuthenticatedLayout :title="__('harvest.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="data.date_rendered"
      :breadcrumbs="[{ to: 'harvests.index', text: __('harvest.titles.entity_breadcrumb') }, { text: __('generics.actions.show') }]"
    >
      <Button
        severity="secondary"
        @click="deleteHandler(data.id)"
        :label="__('generics.actions.delete')"
        v-if="canDestroy"
      />
      <Button
        :href="route('harvests.edit', data.id)"
        :label="__('generics.actions.edit')"
        v-if="canEdit"
      />
    </HeaderCrud>

    <CardSection>
      <VInput :label="__('harvest.form.date.label')" :value="data.date_rendered" readonly />
      <VInput :label="__('harvest.form.quarter_ids.label')" :value="quarters" readonly />

      <VInput :label="__('harvest.form.batch.label')" :value="data.batch" readonly />
      <VInput :label="__('harvest.form.dog_id.label')" :value="data.dog.name" readonly />

      <VInput :label="__('harvest.form.farmer_id.label')" :value="data.farmer.name" readonly />
      <VInput :label="__('harvest.form.assistant_id.label')" :value="data.assistant.name" readonly />

      <VInput
        id="note"
        type="textarea"
        v-model="data.note"
        classWrapper="col-span-2"
        :label="__('harvest.form.note.label')"
        readonly
      />
    </CardSection>
    <CardSection
      wrapperClass=""
      v-if="data.details"
    >
      <template #header>
        <header class="flex items-center justify-between gap-x-3 overflow-hidden px-6 py-4">
          <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100">
            {{ __('harvest.sections.harvest', { batch: data.batch.toUpperCase(), week: getWeek(data.date, { weekStartsOn: 1 })}) }}
          </h3>

          <div class="flex items-center gap-2">
            {{ __('harvest.form.weight.label') }}
            <VInput
              :value="formatNumber(data.weight)"
              readonly
            />
          </div>
        </header>
      </template>

      <DataTable :value="data.details" tableStyle="min-width: 50rem">
        <Column field="plant_code" :header="__('harvest.form.details.plant_code.label')"></Column>
        <Column field="quality" :header="__('harvest.form.details.quality.label')"></Column>
        <Column :header="__('harvest.form.details.weight.label')">
          <template #body="{ data }">
            {{ formatNumber(data.weight) }}
          </template>
        </Column>
      </DataTable>
    </CardSection>
  </AuthenticatedLayout>
</template>
