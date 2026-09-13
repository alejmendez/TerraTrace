<script setup>
import { QrcodeStream } from 'vue-qrcode-reader';
import InputGroup from 'primevue/inputgroup';
import InputText from 'primevue/inputtext';
import ButtonPrime from 'primevue/button';

const props = defineProps({
  paused: {
    type: Boolean,
    required: true,
  },
  hasError: {
    type: Boolean,
    required: true,
  },
  plantCode: {
    type: String,
    required: false,
  },
  plantCodeToFind: {
    type: String,
    required: true,
  },
  loading: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['detect', 'findPlant', 'resetQr', 'update:plantCodeToFind']);

const onDetect = async (detectedCodes) => {
  emit('detect', detectedCodes);
};

const findPlantByCode = () => {
  emit('findPlant', props.plantCodeToFind);
};
</script>

<template>
  <div class="grid md:grid-cols-2 sm:grid-cols-1 gap-x-16 gap-y-4 mb-5">
    <div class="w-full">
      <div class="py-5 text-center dark:text-gray-100">
        {{ __('harvest_details.form.plant_code_to_find.label') }}
      </div>
      <div class="w-full overflow-hidden">
        <QrcodeStream
          :paused="paused"
          @detect="onDetect"
        >
          <div
            v-if="plantCode"
            @click="$emit('resetQr')"
            class="feedback"
            :class="{ 'text-red-800' : hasError }"
          >
            {{ hasError ? __('harvest.errors.details.plant_code_not_found', { plant_code: plantCode }) : plantCode }}
            <span class="material-symbols-rounded mt-3">refresh</span>
          </div>
        </QrcodeStream>
      </div>
    </div>
    <div class="w-full">
      <div class="py-5 text-center dark:text-gray-100">
        {{ __('harvest_details.form.plant_code_to_find.manual_label') }}
      </div>
      <div>
        <InputGroup>
          <InputText
            class="text-center"
            :value="plantCodeToFind"
            @input="$emit('update:plantCodeToFind', $event.target.value)"
            size="large"
          />
          <ButtonPrime icon="pi pi-search" class="!w-[60px]" @click="findPlantByCode" />
        </InputGroup>
      </div>
    </div>
  </div>
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
