<script setup>
import { computed, useAttrs } from 'vue';

import CollectionFieldWrapper from './CollectionFieldWrapper.vue';

const model = defineModel();

const props = defineProps({
    classWrapper: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    label: {
        type: String,
        default: '',
    },
    message: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    rows: {
        type: Number,
        default: 5,
    },
    step: {
        type: [String, Number],
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    showButtons: {
        type: Boolean,
        default: false,
    },
});

defineOptions({ inheritAttrs: false });
const attrs = useAttrs();

const emit = defineEmits(['change', 'input', 'focus', 'blur', 'keydown']);

const isInvalid = computed(
    () => props.message !== '' && props.message !== undefined && props.message !== null
);

const fieldClasses = computed(() => {
    const base = [
        'w-full',
        'rounded-lg',
        'border',
        'border-[#d7e0d9]',
        'bg-white',
        'px-3',
        'py-2',
        'text-[#102f27]',
        'placeholder:text-[#9ba8a0]',
        'focus:outline-none',
        'focus:ring-2',
        'focus:ring-[#17663a]',
        'focus:border-[#17663a]',
        'disabled:opacity-60',
        'disabled:cursor-not-allowed',
        'transition',
    ];
    const invalid = isInvalid.value ? ['border-[#be123c]', 'focus:ring-[#be123c]', 'focus:border-[#be123c]'] : [];
    return [...base, ...invalid].join(' ');
});

const isNumber = computed(() => props.type === 'number');
const isTextarea = computed(() => props.type === 'textarea');

function onInput(e) {
    if (isNumber.value && e.target.value === '') {
        model.value = null;
        return;
    }
    emit('input', e);
}

function onChange(e) {
    emit('change', e);
}

function bump(delta) {
    if (model.value === null || model.value === undefined || model.value === '') {
        model.value = 0;
    }
    const step = props.step !== null ? Number(props.step) : 1;
    model.value = Number(model.value) + delta * step;
    emit('change', model.value);
}
</script>

<template>
    <CollectionFieldWrapper
        :classWrapper="props.classWrapper"
        :label="props.label"
        :message="props.message"
    >
        <div v-if="isNumber && props.showButtons" class="flex items-center gap-2">
            <button
                type="button"
                class="flex size-10 items-center justify-center rounded-lg border border-[#d7e0d9] bg-white text-[#284238] hover:bg-[#edf5ed] disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="props.disabled"
                aria-label="Decrementar"
                @click="bump(-1)"
            >
                −
            </button>
            <input
                v-bind="attrs"
                v-model="model"
                :type="props.type"
                :step="props.step"
                :placeholder="props.placeholder"
                :disabled="props.disabled"
                :readonly="props.readonly"
                :aria-invalid="isInvalid"
                :class="fieldClasses"
                @change="onChange"
                @input="onInput"
                @focus="emit('focus', $event)"
                @blur="emit('blur', $event)"
                @keydown="emit('keydown', $event)"
            />
            <button
                type="button"
                class="flex size-10 items-center justify-center rounded-lg border border-[#d7e0d9] bg-white text-[#284238] hover:bg-[#edf5ed] disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="props.disabled"
                aria-label="Incrementar"
                @click="bump(1)"
            >
                +
            </button>
        </div>

        <textarea
            v-else-if="isTextarea"
            v-bind="attrs"
            v-model="model"
            :placeholder="props.placeholder"
            :disabled="props.disabled"
            :readonly="props.readonly"
            :rows="props.rows"
            :aria-invalid="isInvalid"
            :class="fieldClasses"
            @change="onChange"
            @input="onInput"
            @focus="emit('focus', $event)"
            @blur="emit('blur', $event)"
            @keydown="emit('keydown', $event)"
        />

        <input
            v-else
            v-bind="attrs"
            v-model="model"
            :type="props.type"
            :step="props.step"
            :placeholder="props.placeholder"
            :disabled="props.disabled"
            :readonly="props.readonly"
            :aria-invalid="isInvalid"
            :class="fieldClasses"
            @change="onChange"
            @input="onInput"
            @focus="emit('focus', $event)"
            @blur="emit('blur', $event)"
            @keydown="emit('keydown', $event)"
        />
    </CollectionFieldWrapper>
</template>
