<script setup>
import { useForm } from '@inertiajs/vue3';
import { getWeek } from 'date-fns';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormHarvest from '@Fields/Pages/Harvests/Form.vue';

import Button from '@Core/Components/Form/Button.vue';

import { stringToDate } from '@Core/Utils/date';

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

const form = useForm({
  _method: 'PATCH',
  id: data.id,
  date: stringToDate(data.date),
  batch: data.batch,
  quarter_ids: data.quarters.map((q) => ({ text: q.name, value: q.id })),
  dog_id: props.dogs.find((a) => a.value == data.dog.id),
  farmer_id: props.users.find((a) => a.value == data.farmer.id),
  assistant_id: props.users.find((a) => a.value == data.assistant.id),
  weight: data.weight,
  note: data.note,
  details: data.details.length
    ? data.details.map((d) => ({
        ...d,
        weight: d.weight ? parseFloat(d.weight) : null,
        quality: props.qualities.find((q) => q.value == d.quality),
      }))
    : [
        {
          id: null,
          plant_code: null,
          quality: null,
          weight: null,
        },
      ],
});

const submitHandler = () => {
  form
    .transform((data) => ({
      ...data,
      details: data.details.map((d) => ({ ...d, quality: d.quality?.value })),
    }))
    .post(route('harvests.update', data.id));
};
</script>

<template>
  <AuthenticatedLayout :title="__('harvest.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('harvest.titles.edit', { batch: form.batch.toUpperCase(), week: getWeek(form.date, { weekStartsOn: 1 })})"
      :breadcrumbs="[{ to: 'harvests.index', text: __('harvest.titles.entity_breadcrumb') }, { text: __('generics.actions.edit') }]"
    >
      <Button
        class="btn btn-secondary border-gray-800"
        :loading="form.processing"
        :label="__('generics.buttons.save_edit')"
        @click="submitHandler"
        v-if="submitHandler"
      />

      <Button
        severity="secondary"
        :disabled="form.processing"
        :href="route('harvests.index')"
        :label="__('generics.buttons.cancel')"
      />
    </HeaderCrud>

    <FormHarvest
      :form="form"
      :quarters="props.quarters"
      :dogs="props.dogs"
      :users="props.users"
      :plant_codes="props.plant_codes"
      :qualities="props.qualities"
      :details="true"
      :submitHandler="submitHandler"
      :date_rendered="data.date_rendered"
    />
  </AuthenticatedLayout>
</template>
