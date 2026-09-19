<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { getWeek } from 'date-fns';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import CollectionPageHeader from '@Core/Components/Collection/CollectionPageHeader.vue';

import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';

import CollectionButton from '@Core/Components/Collection/CollectionButton.vue';

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


const quarters = ref(data.quarters.map((q) => q.name).join(', '));

const canEdit = can('harvests.edit');
const canDestroy = can('harvests.destroy');

const deleteHandler = async (id) => {
  await deleteRowTable(() => {
    router.delete(route('harvests.destroy', id));
  });
};
</script>

<template>
  <AuthenticatedLayout :title="__('harvest.titles.entity_breadcrumb')">
    <CollectionPageHeader
      :title="data.date_rendered"
      :breadcrumbs="[{ to: 'harvests.index', text: __('harvest.titles.entity_breadcrumb') }, { text: __('generics.actions.show') }]"
    >
      <CollectionButton
        severity="secondary"
        @click="deleteHandler(data.id)"
        :label="__('generics.actions.delete')"
        v-if="canDestroy"
      />
      <CollectionButton
        :href="route('harvests.edit', data.id)"
        :label="__('generics.actions.edit')"
        v-if="canEdit"
      />
    </CollectionPageHeader>

    <CollectionCardSection>
      <CollectionInput :label="__('harvest.form.date.label')" :value="data.date_rendered" readonly />
      <CollectionInput :label="__('harvest.form.quarter_ids.label')" :value="quarters" readonly />

      <CollectionInput :label="__('harvest.form.batch.label')" :value="data.batch" readonly />
      <CollectionInput :label="__('harvest.form.dog_id.label')" :value="data.dog.name" readonly />

      <CollectionInput :label="__('harvest.form.farmer_id.label')" :value="data.farmer.name" readonly />
      <CollectionInput :label="__('harvest.form.assistant_id.label')" :value="data.assistant.name" readonly />

      <CollectionInput
        id="note"
        type="textarea"
        v-model="data.note"
        classWrapper="col-span-2"
        :label="__('harvest.form.note.label')"
        readonly
      />
    </CollectionCardSection>
    <CollectionCardSection
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
            <CollectionInput
              :value="formatNumber(data.weight)"
              readonly
            />
          </div>
        </header>
      </template>

      <table class="w-full text-left text-sm">
        <thead class="bg-[#edf5ed] text-[#284238] uppercase text-xs">
          <tr>
            <th class="px-4 py-3">{{ __('harvest.form.details.plant_code.label') }}</th>
            <th class="px-4 py-3">{{ __('harvest.form.details.quality.label') }}</th>
            <th class="px-4 py-3">{{ __('harvest.form.details.weight.label') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="detail in data.details"
            :key="detail.id"
            class="border-b border-[#e1e9e3] hover:bg-[#f7faf7]"
          >
            <td class="px-4 py-3">{{ detail.plant_code }}</td>
            <td class="px-4 py-3">{{ detail.quality }}</td>
            <td class="px-4 py-3">{{ formatNumber(detail.weight) }}</td>
          </tr>
        </tbody>
      </table>
    </CollectionCardSection>
  </AuthenticatedLayout>
</template>
