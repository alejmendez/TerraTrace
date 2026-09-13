<script setup>
import { useAttrs } from 'vue';

import VInput from '@Core/Components/Form/VInput.vue';

const model = defineModel();
const attrs = useAttrs();

const formatRut = (input) => {
  if (!input) return '';

  let str = input.toString();
  let formatted = '';

  let dv = str.slice(-1).toLowerCase();
  str = str.slice(0, -1);

  if (str.length <= 2) {
    return str + (dv ? '-' + dv : '');
  }

  for (let i = str.length - 1, j = 1; i >= 0; i--, j++) {
    formatted = str.charAt(i) + formatted;
    if (j % 3 === 0 && i !== 0) {
      formatted = '.' + formatted;
    }
  }

  return formatted + '-' + dv;
};

const handlerInput = (e) => {
  let value = e.target.value;

  const hasK = value.includes('-k') || value.includes('-K');

  let rutValue = value.replace(/[^\dk]/gi, '');
  if (hasK) {
    rutValue = rutValue.slice(0, -1) + 'k';
  }

  const rutFormated = formatRut(rutValue);

  e.target.value = rutFormated;
  model.value = rutFormated;
};

const handlerKeyPress = (e) => {
  const char = String.fromCharCode(e.keyCode);
  const currentValue = e.target.value;

  // Permitir números y 'k' solo después del guión
  if (!/[\d]/.test(char) && !(currentValue.includes('-') && /[kK]/.test(char))) {
    e.preventDefault();
  }
};
</script>

<template>
  <VInput
    v-bind="attrs"
    v-model="model"
    @input="handlerInput"
    @keypress="handlerKeyPress"
  />
</template>
