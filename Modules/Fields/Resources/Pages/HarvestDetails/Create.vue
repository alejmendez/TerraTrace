<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import QrCodeReader from '@Fields/Pages/HarvestDetails/QrCodeReader.vue';
import HarvestView from '@Fields/Pages/HarvestDetails/Views/HarvestView.vue';
import VariablesView from '@Fields/Pages/HarvestDetails/Views/VariablesView.vue';

import Button from 'primevue/button';

import { findByCode } from '@Fields/Services/Plant.js';
import HarvestDetailService from '@Fields/Services/HarvestDetailService.js';
import PlantDetailService from '@Fields/Services/PlantDetailService.js';

const props = defineProps({
  qualities: Array,
  plant_code: String,
});

const properties = {
  height: null,
  crown_diameter: null,
  invasion_radius: null,
  trunk_diameter: null,
  root_diameter: null,
  foliage_sanitation: null,
  foliage_sanitation_photo: null,
  trunk_sanitation: null,
  trunk_sanitation_photo: null,
  soil_sanitation: null,
  soil_sanitation_photo: null,
  irrigation_system: null,
};

const form = useForm({
  plant_id: null,
  plant_code: props.plant_code || null,
  keep_plant_code: false,
  quality: null,
  weight: null,
  ...properties,
  notes: {
    ...properties,
  },
});

const DEFAULT_OPTION_VIEW = 'main';

const optionViews = ['harvest', 'variables', 'logbook', 'location'];

const plantCodeToFind = ref('');
const optionView = ref(props.plant_code === null ? '' : optionViews[0]);

const _submitHandler = async (keep_plant_code = false) => {
  form.keep_plant_code = keep_plant_code;
  form.processing = true;

  if (optionView.value === optionViews[0]) {
    await HarvestDetailService.store(form);
    optionView.value = keep_plant_code ? optionViews[0] : '';
    form.weight = null;
  }

  if (optionView.value === optionViews[1]) {
    await PlantDetailService.store(form);
  }

  if (!keep_plant_code) {
    resetQr();
  }
  form.processing = false;
};

const submitHandler = () => _submitHandler(false);
const submitAndLoadAnother = () => _submitHandler(true);

const paused = ref(!!form.plant_code);
const loading = ref(false);
const hasError = ref(false);

const onDetect = async (detectedCodes) => {
  await findPlantByCode(detectedCodes[0].rawValue);
};

const findPlantByCode = async (plant_code) => {
  loading.value = true;
  hasError.value = false;
  plantCodeToFind.value = plant_code;
  form.plant_code = plant_code;
  paused.value = true;
  try {
    const { plant } = await findByCode(plant_code);
    optionView.value = DEFAULT_OPTION_VIEW;
    form.plant_id = plant.id;
  } catch (error) {
    optionView.value = '';
    hasError.value = true;
  } finally {
    loading.value = false;
  }
};

const resetQr = () => {
  paused.value = false;
  hasError.value = false;
  loading.value = false;
  form.reset();
  form.plant_id = null;
  form.plant_code = null;
  plantCodeToFind.value = '';
  optionView.value = '';
};

const optionViewHandler = (option) => {
  optionView.value = option;
  if (option === optionViews[2]) {
    router.visit(route('plants.show', form.plant_id) + '?current_tab=logs');
  }

  if (option === optionViews[3]) {
    // TODO: Implement location view
  }
};
</script>

<template>
  <AuthenticatedLayout :title="__('harvest.titles.entity_breadcrumb')">
    <HeaderCrud
      :breadcrumbs="[{ to: 'harvests.index', text: __('harvest.titles.entity_breadcrumb') }]"
    />
    <h3 class="text-2xl mb-3 input-label">
      {{ __('harvest_details.titles.find', { code: form.plant_code || '' }) }}
    </h3>

    <QrCodeReader
      :paused="paused"
      :has-error="hasError"
      :loading="loading"
      :plant-code="form.plant_code"
      v-model:plant-code-to-find="plantCodeToFind"
      @detect="onDetect"
      @find-plant="findPlantByCode"
      @reset-qr="resetQr"
    />

    <div v-show="form.errors.plant_code">
      <p class="text-sm text-red-600">
        {{ form.errors.plant_code }}
      </p>
    </div>

    <div v-show="optionView === DEFAULT_OPTION_VIEW">
      <Button
        v-for="option in optionViews"
        class="w-full mt-3 text-xl h-16"
        :loading="form.processing"
        :disabled="hasError"
        :label="__(`harvest_details.buttons.${option}`)"
        @click="optionViewHandler(option)"
      />
    </div>
    <form @submit.prevent="_submitHandler">
      <div v-show="optionView === optionViews[0]">
        <HarvestView
          :form="form"
          :qualities="props.qualities"
          :has-error="hasError"
          @submit="submitHandler"
          @submit-and-load-another="submitAndLoadAnother"
          @reset-qr="resetQr"
        />
      </div>

      <div v-show="optionView === optionViews[1]">
        <VariablesView
          :form="form"
          :has-error="hasError"
          @submit="submitHandler"
          @cancel="resetQr"
        />
      </div>
    </form>
  </AuthenticatedLayout>
</template>

<style>
.feedback {
  position: absolute;
  width: 100%;
  height: 100%;

  background-color: rgba(255, 255, 255, 0.9);
  padding: 10px;
  text-align: center;
  font-weight: bold;
  font-size: 1.8rem;

  display: flex;
  flex-flow: column nowrap;
  justify-content: center;
}
</style>
