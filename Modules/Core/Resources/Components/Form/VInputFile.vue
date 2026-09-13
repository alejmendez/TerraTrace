<script setup>
import { ref, computed } from 'vue';

import InputGroup from 'primevue/inputgroup';
import Button from '@Core/Components/Form/Button.vue';

const props = defineProps({
  multiple: {
    type: Boolean,
    default: false,
  },
  files: {
    type: Array,
    default: [],
  },
  imagePreview: {
    type: Boolean,
    default: false,
  },
  accept: {
    type: String,
    default: 'image/*',
  },
  image: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  withRemove: {
    type: Boolean,
    default: true,
  },
  showPathFile: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['change']);

const fileInput = ref(null);
const fileRemove = ref(false);
const filePreview = ref(null);
const filePath = ref('');
const fileList = ref([]);
const fileListRemove = ref([]);
const filesServer = ref(props.files);

const preview = computed(() => {
  if (fileRemove.value) {
    return null;
  }
  if (filePreview.value) {
    return filePreview.value;
  }

  return props.image;
});

const changeMultipleFileHandler = (e) => {
  const { files } = e.target;

  fileList.value = files;

  emit('change', {
    fileRemove: fileListRemove,
    fileInput: fileList,
  });
};

const changeFileHandler = (e) => {
  if (props.multiple) {
    changeMultipleFileHandler(e);
    return;
  }

  const { files } = e.target;
  const firstFile = files[0];

  emit('change', {
    fileRemove: false,
    fileInput: firstFile,
  });

  if (firstFile) {
    fileRemove.value = false;
    filePreview.value = URL.createObjectURL(firstFile);
    filePath.value = firstFile.name;
  }
};

const fileRemoveHandler = () => {
  fileRemove.value = true;
  fileInput.value.value = null;
  filePreview.value = null;
  emit('change', {
    fileRemove: true,
    fileInput: null,
  });
};

const selectFile = () => {
  fileInput.value.click();
};

const remove_element = (id) => {
  const index = filesServer.value.findIndex((item) => item.id === id);
  if (index !== -1) {
    filesServer.value.splice(index, 1);
  }

  fileListRemove.value.push(id);
  emit('change', {
    fileRemove: fileListRemove,
    fileInput: fileList,
  });
};
</script>

<template>
  <div class="md:flex md:flex-row gap-x-5">
    <div class="min-h-[150px]" v-if="props.imagePreview">
      <div
        class="w-32 min-h-[150px] border rounded-md"
        :class="{ 'h-full': !preview }"
      >
        <img
          class="w-full"
          :src="preview"
          v-if="preview"
        />
      </div>
    </div>
    <input
      ref="fileInput"
      type="file"
      class="hidden"
      :accept="accept"
      :multiple="multiple"
      @change="changeFileHandler"
    />
    <div class="w-full" v-if="multiple">
      <div class="mb-1 w-full text-gray-900 dark:text-gray-100">{{ props.label }}</div>

      <Button
        severity="secondary"
        @click.prevent="selectFile"
        :label="__('generics.form.file.upload_file')"
      />

      <div class="max-w-full mt-2" :title="file.name" v-for="file in filesServer">
        <span class="material-symbols-rounded cursor-pointer mt-1 me-1 !text-md text-black hover:text-red-500" @click="remove_element(file.id)">delete</span>

        <a :href="file.url" target="_blank">
          {{ file.name }}
        </a>
      </div>

      <div class="max-w-full mt-2 dark:text-gray-100" :title="file.name" v-for="file in fileList">
        {{ file.name }}
      </div>

      <div class="text-slate-500 dark:text-slate-300 text-sm mt-2">Los archivos no debe superar 5 mb</div>
    </div>
    <div class="w-full" v-else>
      <div class="mb-1 w-full text-gray-900 dark:text-gray-100">{{ props.label }}</div>
      <div class="md:w-[420px] max-w-full sm:hidden md:block">
        <InputGroup>
          <div
            class="border border-gray-300 dark:border-(--p-surface-700) dark:text-(--p-surface-400) dark:bg-(--p-surface-950) p-2 grow truncate rounded-s border-e-0 dark:text-gray-100"
            :title="filePath"
          >
            {{ filePath }}
          </div>
          <Button
            severity="secondary"
            @click.prevent="selectFile"
            :label="__('generics.form.file.upload_file')"
          />
        </InputGroup>
      </div>
      <div class="md:w-[420px] max-w-full sm:block md:hidden">
        <InputGroup>
          <div
            class="border dark:border-(--p-surface-700) dark:text-(--p-surface-400) dark:bg-(--p-surface-950) p-2 grow truncate rounded-s border-e-0 dark:text-gray-100"
            :title="filePath"
          >
            {{ filePath }}
          </div>
          <Button
            severity="secondary"
            @click.prevent="selectFile"
            icon="pi pi-cloud-upload"
          />
        </InputGroup>
      </div>
      <div class="text-slate-500 dark:text-slate-300 text-sm">Los archivos no debe superar 5 mb</div>
      <Button
        severity="secondary"
        v-if="props.withRemove"
        class="mt-4"
        @click.prevent="fileRemoveHandler"
        :label="__('generics.form.file.remove_image')"
      />
    </div>
  </div>
</template>
