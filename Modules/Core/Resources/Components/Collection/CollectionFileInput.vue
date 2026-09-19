<script setup>
import { ref, computed } from 'vue';

import CollectionButton from './CollectionButton.vue';

const props = defineProps({
    multiple: {
        type: Boolean,
        default: false,
    },
    files: {
        type: Array,
        default: () => [],
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
const filesServer = ref([...props.files]);

const preview = computed(() => {
    if (fileRemove.value) return null;
    if (filePreview.value) return filePreview.value;
    return props.image;
});

const changeMultipleFileHandler = (e) => {
    fileList.value = Array.from(e.target.files);
    emit('change', {
        fileRemove: fileListRemove.value,
        fileInput: fileList.value,
    });
};

const changeFileHandler = (e) => {
    if (props.multiple) {
        changeMultipleFileHandler(e);
        return;
    }
    const files = e.target.files;
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
    if (fileInput.value) fileInput.value.value = null;
    filePreview.value = null;
    emit('change', {
        fileRemove: true,
        fileInput: null,
    });
};

const selectFile = () => {
    if (fileInput.value) fileInput.value.click();
};

const removeServerFile = (id) => {
    const index = filesServer.value.findIndex((item) => item.id === id);
    if (index !== -1) filesServer.value.splice(index, 1);
    fileListRemove.value.push(id);
    emit('change', {
        fileRemove: fileListRemove.value,
        fileInput: fileList.value,
    });
};
</script>

<template>
    <div class="md:flex md:flex-row gap-x-5">
        <div v-if="props.imagePreview" class="min-h-[150px]">
            <div
                class="w-32 min-h-[150px] border rounded-md"
                :class="{ 'h-full': !preview }"
            >
                <img v-if="preview" class="w-full" :src="preview" alt="" />
            </div>
        </div>
        <input
            ref="fileInput"
            type="file"
            class="hidden"
            :accept="props.accept"
            :multiple="props.multiple"
            @change="changeFileHandler"
        />

        <div v-if="props.multiple" class="w-full">
            <div class="mb-1 w-full text-[#284238]">{{ props.label }}</div>

            <CollectionButton
                severity="secondary"
                :label="__('generics.form.file.upload_file')"
                @click.prevent="selectFile"
            />

            <div
                v-for="file in filesServer"
                :key="file.id"
                class="max-w-full mt-2"
                :title="file.name"
            >
                <button
                    type="button"
                    class="material-symbols-rounded cursor-pointer mt-1 me-1 !text-md text-black hover:text-[#be123c]"
                    aria-label="Eliminar archivo"
                    @click="removeServerFile(file.id)"
                >
                    delete
                </button>
                <a :href="file.url" target="_blank">{{ file.name }}</a>
            </div>

            <div
                v-for="file in fileList"
                :key="file.name"
                class="max-w-full mt-2 text-[#284238]"
                :title="file.name"
            >
                {{ file.name }}
            </div>

            <p class="text-[#61716c] text-sm mt-2">
                Los archivos no debe superar 5 mb
            </p>
        </div>

        <div v-else class="w-full">
            <div class="mb-1 w-full text-[#284238]">{{ props.label }}</div>
            <div class="flex items-stretch">
                <div
                    class="grow truncate rounded-s-lg border border-r-0 border-[#d7e0d9] bg-white px-3 py-2 text-[#102f27]"
                    :title="filePath"
                >
                    {{ filePath }}
                </div>
                <CollectionButton
                    severity="secondary"
                    :label="__('generics.form.file.upload_file')"
                    @click.prevent="selectFile"
                />
            </div>
            <p class="text-[#61716c] text-sm mt-2">
                Los archivos no debe superar 5 mb
            </p>
            <CollectionButton
                v-if="props.withRemove"
                severity="secondary"
                class="mt-4"
                :label="__('generics.form.file.remove_image')"
                @click.prevent="fileRemoveHandler"
            />
        </div>
    </div>
</template>
