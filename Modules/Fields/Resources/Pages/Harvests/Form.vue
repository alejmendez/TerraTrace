<script setup>
import { ref, computed, watch } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { format, getWeek, endOfWeek, startOfWeek } from 'date-fns';

import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';

import CardSection from '@Core/Components/CardSection.vue';
import VElementFormWrapper from '@Core/Components/Form/VElementFormWrapper.vue';
import VSelectMultiple from '@Core/Components/Form/VSelectMultiple.vue';
import VSelect from '@Core/Components/Form/VSelect.vue';
import Button from '@Core/Components/Form/Button.vue';
import VInput from '@Core/Components/Form/VInput.vue';

const props = defineProps({
  form: Object,
  quarters: Array,
  dogs: Array,
  users: Array,
  plant_codes: Array,
  qualities: Array,
  details: Boolean,
  date_rendered: String,
  submitHandler: Function,
});

const form = props.form;

const dateRenderText = (m) => {
  const start = format(startOfWeek(m, { weekStartsOn: 1 }), 'dd/MM/yyyy');
  const end = format(endOfWeek(m, { weekStartsOn: 1 }), 'dd/MM/yyyy');
  const week = getWeek(m, { weekStartsOn: 1 });
  return trans('harvest.form.date.renderText', { week, start, end });
};

const date_rendered = ref(props.date_rendered ?? '');

const add_detail = () => {
  form.details.push({
    id: null,
    plant_code: form.details[form.details.length - 1].plant_code,
    quality: null,
    weight: null,
  });
};

const remove_detail = (index) => {
  form.details.splice(index, 1);
};

const handler_date_selected = () => {
  date_rendered.value = dateRenderText(form.date);
};

const handler_input_batch = (e) => {
  const filteredValue = e.target.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
  e.target.value = filteredValue;
  form.batch = filteredValue;
};

const totalWeight = computed(() => {
  if (!form.details) {
    return 0;
  }

  return form.details.reduce((sum, detail) => {
    return sum + (Number(detail.weight) || 0);
  }, 0);
});

watch(totalWeight, (newValue) => {
  form.weight = newValue;
});
</script>

<template>
  <form @submit.prevent="props.submitHandler">
    <CardSection>
      <div class="grid grid-cols-2 gap-x-2 gap-y-4">
        <VElementFormWrapper :label="__('harvest.form.date.label_rendered')">
          <InputText
            fluid
            class="mt-1"
            v-model="date_rendered"
            variant="filled"
          />
        </VElementFormWrapper>
        <VInput
          id="date"
          v-model="form.date"
          type="date"
          :label="__('harvest.form.date.label')"
          :message="form.errors.date"
          @change="handler_date_selected"
        />
      </div>

      <VSelectMultiple
        v-model="form.quarter_ids"
        optionGroupLabel="text"
        optionGroupChildren="items"
        :options="props.quarters"
        :label="__('harvest.form.quarter_ids.label')"
        :placeholder="__('generics.please_select')"
        :message="form.errors.quarter_ids"
      />

      <VInput
        id="batch"
        v-model="form.batch"
        maxlength="2"
        @input="handler_input_batch"
        :label="__('harvest.form.batch.label')"
        :message="form.errors.batch"
      />

      <VSelect
        id="dog_id"
        v-model="form.dog_id"
        :placeholder="__('generics.please_select')"
        :options="props.dogs"
        :label="__('harvest.form.dog_id.label')"
        :message="form.errors.dog_id"
      />

      <VSelect
        id="farmer_id"
        v-model="form.farmer_id"
        :placeholder="__('generics.please_select')"
        :options="props.users"
        :label="__('harvest.form.farmer_id.label')"
        :message="form.errors.farmer_id"
      />

      <VSelect
        id="assistant_id"
        v-model="form.assistant_id"
        :placeholder="__('generics.please_select')"
        :options="props.users"
        :label="__('harvest.form.assistant_id.label')"
        :message="form.errors.assistant_id"
      />

      <VInput
        id="note"
        type="textarea"
        v-model="form.note"
        classWrapper="col-span-2"
        :label="__('harvest.form.note.label')"
        :message="form.errors.note"
      />
    </CardSection>

    <CardSection
      wrapperClass=""
      v-if="props.details"
    >
      <template #header>
        <header class="flex items-center justify-between gap-x-3 overflow-hidden px-6 py-4">
          <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100">
            {{ __('harvest.sections.harvest', { batch: form.batch.toUpperCase(), week: getWeek(form.date, { weekStartsOn: 1 })}) }}
          </h3>

          <div class="flex items-center gap-2">
            {{ __('harvest.form.weight.label') }}
            <InputNumber
              v-model="form.weight"
              :message="form.errors.weight"
              showButtons
              :min="0"
              :step="1"
              suffix=" grs"
            />
          </div>
        </header>
      </template>

      <div
        class="px-6 py-3 grid grid-cols-2 gap-x-16 gap-y-4"
        v-for="(detail, index) in form.details"
      >
        <VInput
          :id="`details_plant_code_${index}`"
          v-model="detail.plant_code"
          :label="__('harvest.form.details.plant_code.label')"
          :message="form.errors[`details.${index}.plant_code`]"
        />

        <div class="grid grid-cols-9 gap-x-16 gap-y-4">
          <VSelect
            :id="`details_quality_${index}`"
            class="col-span-4"
            v-model="detail.quality"
            :placeholder="__('generics.please_select')"
            :options="props.qualities"
            :label="__('harvest.form.details.quality.label')"
            :message="form.errors[`details.${index}.quality`]"
          />

          <VInput
            :id="`details_weight_${index}`"
            type="number"
            class="col-span-4"
            v-model="detail.weight"
            :min="0"
            :max="20000"
            :step="1"
            :label="__('harvest.form.details.weight.label')"
            :message="form.errors[`details.${index}.weight`]"
          />
          <div
            class="pt-8 text-black dark:text-white hover:text-red-500 cursor-pointer dark:hover:text-red-500"
            @click="remove_detail(index)"
            v-if="index !== 0"
          >
            <span class="material-symbols-rounded">delete</span>
          </div>
        </div>
      </div>
      <div class="px-6 py-3">
        <Button
          severity="secondary"
          @click.prevent="add_detail"
          :label="__('harvest.buttons.add_detail')"
        />

        <Button
          class="ms-3"
          severity="secondary"
          :href="route('harvests.create.bulk', { id: form.id })"
          :label="__('generics.bulk.button')"
        />
      </div>
    </CardSection>
  </form>
</template>
