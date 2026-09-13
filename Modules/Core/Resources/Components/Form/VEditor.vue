<script setup>
import { useAttrs } from 'vue';

import Editor from 'primevue/editor';

const model = defineModel();
const attrs = useAttrs();

const props = defineProps({
  options: Array,
});

const emit = defineEmits(['change', 'input', 'focus', 'blur', 'keydown']);

const editorModules = {
  mention: {
    allowedChars: /^[A-Za-z\s]*$/,
    source: (searchTerm, renderList) => {
      const matches = props.options
        .filter((option) => option.text.toLowerCase().includes(searchTerm.toLowerCase()))
        .map((option) => ({ id: option.value, value: option.text }));
      renderList(matches, searchTerm);
    },
    toolbar: [
      ['bold', 'italic', 'underline', 'strike'],
      ['blockquote', 'code-block'],
      [{ list: 'ordered' }, { list: 'bullet' }],
      [{ script: 'sub' }, { script: 'super' }],
      [{ indent: '-1' }, { indent: '+1' }],
      [{ direction: 'rtl' }],
      [{ size: ['small', false, 'large', 'huge'] }],
      [{ header: [1, 2, 3, 4, 5, 6, false] }],
      [{ color: [] }, { background: [] }],
      [{ font: [] }],
      ['clean'],
    ],
  },
};
</script>

<template>
  <Editor
    v-bind="attrs"
    v-model="model"
    :modules="editorModules"
    @update:modelValue="emit('change', $event)"
    @input="emit('input', $event)"
    @focus="emit('focus', $event)"
    @blur="emit('blur', $event)"
    @keydown="emit('keydown', $event)"
  />
</template>
