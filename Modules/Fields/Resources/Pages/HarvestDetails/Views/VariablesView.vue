<script setup>
import { ref } from 'vue';

import CollectionFieldWrapper from '@Core/Components/Collection/CollectionFieldWrapper.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionButton from '@Core/Components/Collection/CollectionButton.vue';

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
    <CollectionInput
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

    <CollectionInput
      id="notes_height"
      class="mb-2"
      v-model="form.notes.height"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.height.label')})`"
    />

    <CollectionInput
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

    <CollectionInput
      id="notes_crown_diameter"
      class="mb-2"
      v-model="form.notes.crown_diameter"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.crown_diameter.label')})`"
    />

    <CollectionInput
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

    <CollectionInput
      id="notes_trunk_diameter"
      class="mb-2"
      v-model="form.notes.trunk_diameter"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.trunk_diameter.label')})`"
    />

    <CollectionInput
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

    <CollectionInput
      id="notes_root_diameter"
      class="mb-2"
      v-model="form.notes.root_diameter"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.root_diameter.label')})`"
    />

    <CollectionInput
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

    <CollectionInput
      id="notes_invasion_radius"
      class="mb-2"
      v-model="form.notes.invasion_radius"
      :label="__('harvest_details.form.notes.label') + ` (${__('harvest_details.form.invasion_radius.label')})`"
    />

    <CollectionFieldWrapper :label="__('harvest_details.form.foliage_sanitation.label')" :message="form.errors.foliage_sanitation">
      <div class="mb-2 flex items-stretch gap-2">
        <CollectionInput
          id="foliage_sanitation"
          v-model="form.foliage_sanitation"
          class="grow"
        />
        <CollectionButton
          icon="add"
          @click="openFoliageSanitationPhoto"
        />
      </div>
    </CollectionFieldWrapper>

    <CollectionFieldWrapper :label="__('harvest_details.form.trunk_sanitation.label')" :message="form.errors.trunk_sanitation">
      <div class="mb-2 flex items-stretch gap-2">
        <CollectionInput
          id="trunk_sanitation"
          v-model="form.trunk_sanitation"
          class="grow"
        />
        <CollectionButton
          icon="add"
          @click="openTrunkSanitationPhoto"
        />
      </div>
    </CollectionFieldWrapper>

    <CollectionFieldWrapper :label="__('harvest_details.form.soil_sanitation.label')" :message="form.errors.soil_sanitation">
      <div class="mb-2 flex items-stretch gap-2">
        <CollectionInput
          id="soil_sanitation"
          v-model="form.soil_sanitation"
          class="grow"
        />
        <CollectionButton
          icon="add"
          @click="openSoilSanitationPhoto"
        />
      </div>
    </CollectionFieldWrapper>

    <CollectionInput
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
      <CollectionButton
        class="w-full mt-3 text-xl h-16"
        :loading="form.processing"
        :disabled="hasError || form.processing"
        @click="$emit('submit')"
        label="Guardar"
      />

      <CollectionButton
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
