<script setup>
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputGroup from 'primevue/inputgroup';

import VElementFormWrapper from '@Core/Components/Form/VElementFormWrapper.vue';
import VInput from '@Core/Components/Form/VInput.vue';

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  hasError: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['submit', 'cancel']);

const foliageSanitationPhotoInput = ref(null);
const trunkSanitationPhotoInput = ref(null);
const soilSanitationPhotoInput = ref(null);

const foliageSanitationPhotoHandler = (event) => {
  props.form.foliage_sanitation_photo = event.target.files[0];
};

const trunkSanitationPhotoHandler = (event) => {
  props.form.trunk_sanitation_photo = event.target.files[0];
};

const soilSanitationPhotoHandler = (event) => {
  props.form.soil_sanitation_photo = event.target.files[0];
};

const openTrunkSanitationPhoto = () => {
  trunkSanitationPhotoInput.value.click();
};

const openFoliageSanitationPhoto = () => {
  foliageSanitationPhotoInput.value.click();
};

const openSoilSanitationPhoto = () => {
  soilSanitationPhotoInput.value.click();
};
</script>

<template>
  <div class="grid md:grid-cols-2 sm:grid-cols-1 gap-x-16 gap-y-4 mb-5">
    <VInput
      id="height"
      class="mb-2"
      v-model="form.height"
      type="number"
      :min="0"
      :max="2000"
      :step="0.1"
      sufix="mts"
      :label="__('harvest_details.form.height.label') + ` (${__('harvest_details.form.height.unit')})`"
      :message="form.errors.height"
    />

    <VInput
      id="notes_height"
      class="mb-2"
      v-model="form.notes.height"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.height.label')})`"
    />

    <VInput
      id="crown_diameter"
      class="mb-2"
      v-model="form.crown_diameter"
      type="number"
      :min="0"
      :max="2000"
      :step="0.1"
      sufix="mts"
      :label="__('harvest_details.form.crown_diameter.label') + ` (${__('harvest_details.form.crown_diameter.unit')})`"
      :message="form.errors.crown_diameter"
    />

    <VInput
      id="notes_crown_diameter"
      class="mb-2"
      v-model="form.notes.crown_diameter"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.crown_diameter.label')})`"
    />

    <VInput
      id="trunk_diameter"
      class="mb-2"
      v-model="form.trunk_diameter"
      type="number"
      :min="0"
      :max="2000"
      :step="0.1"
      sufix="mm"
      :label="__('harvest_details.form.trunk_diameter.label') + ` (${__('harvest_details.form.trunk_diameter.unit')})`"
      :message="form.errors.trunk_diameter"
    />

    <VInput
      id="notes_trunk_diameter"
      class="mb-2"
      v-model="form.notes.trunk_diameter"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.trunk_diameter.label')})`"
    />

    <VInput
      id="root_diameter"
      class="mb-2"
      v-model="form.root_diameter"
      type="number"
      :min="0"
      :max="2000"
      :step="0.1"
      sufix="mm"
      :label="__('harvest_details.form.root_diameter.label') + ` (${__('harvest_details.form.root_diameter.unit')})`"
      :message="form.errors.root_diameter"
    />

    <VInput
      id="notes_root_diameter"
      class="mb-2"
      v-model="form.notes.root_diameter"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.root_diameter.label')})`"
    />

    <VInput
      id="invasion_radius"
      class="mb-2"
      v-model="form.invasion_radius"
      type="number"
      :min="0"
      :max="2000"
      :step="0.1"
      sufix="mts"
      :label="__('harvest_details.form.invasion_radius.label') + ` (${__('harvest_details.form.invasion_radius.unit')})`"
      :message="form.errors.invasion_radius"
    />

    <VInput
      id="notes_invasion_radius"
      class="mb-2"
      v-model="form.notes.invasion_radius"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.invasion_radius.label')})`"
    />

    <VElementFormWrapper :label="__('harvest_details.form.foliage_sanitation.label')" :message="form.errors.foliage_sanitation">
      <InputGroup>
        <InputText
          id="foliage_sanitation"
          class="mb-2"
          fluid
          v-model="form.foliage_sanitation"
        />
        <Button
          icon="pi pi-plus"
          @click="openFoliageSanitationPhoto"
        />
      </InputGroup>
    </VElementFormWrapper>

    <VElementFormWrapper :label="__('harvest_details.form.trunk_sanitation.label')" :message="form.errors.trunk_sanitation">
      <InputGroup>
        <InputText
          id="trunk_sanitation"
          class="mb-2"
          fluid
          v-model="form.trunk_sanitation"
        />
        <Button
          icon="pi pi-plus"
          @click="openTrunkSanitationPhoto"
        />
      </InputGroup>
    </VElementFormWrapper>

    <VElementFormWrapper :label="__('harvest_details.form.soil_sanitation.label')" :message="form.errors.soil_sanitation">
      <InputGroup>
        <InputText
          id="soil_sanitation"
          class="mb-2"
          fluid
          v-model="form.soil_sanitation"
        />
        <Button
          icon="pi pi-plus"
          @click="openSoilSanitationPhoto"
        />
      </InputGroup>
    </VElementFormWrapper>

    <VInput
      id="irrigation_system"
      class="mb-2"
      v-model="form.irrigation_system"
      :label="__('harvest_details.form.irrigation_system.label')"
      :message="form.errors.irrigation_system"
    />
    <input ref="foliageSanitationPhotoInput" class="hidden" type="file" accept="image/*" @change="foliageSanitationPhotoHandler" />
    <input ref="trunkSanitationPhotoInput" class="hidden" type="file" accept="image/*" @change="trunkSanitationPhotoHandler" />
    <input ref="soilSanitationPhotoInput" class="hidden" type="file" accept="image/*" @change="soilSanitationPhotoHandler" />
  </div>
  <div class="md:w-1/2 md:mx-auto sm:w-full">
    <div class="mt-5 mb-20">
      <Button
        class="w-full mt-3 text-xl h-16"
        :loading="form.processing"
        :disabled="hasError || form.processing"
        @click="$emit('submit')"
        label="Guardar"
      />

      <Button
        class="w-full mt-3 text-xl h-16"
        :loading="form.processing"
        :disabled="hasError || form.processing"
        severity="danger"
        @click="$emit('cancel')"
        label="Cancelar"
      />
    </div>
  </div>
</template>
