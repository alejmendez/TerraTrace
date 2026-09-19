<script setup>
import { computed, useAttrs, ref, onMounted } from 'vue';

import CollectionFieldWrapper from './CollectionFieldWrapper.vue';

const model = defineModel();

const props = defineProps({
    classWrapper: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
    message: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    optionLabel: {
        type: String,
        default: 'text',
    },
    optionValue: {
        type: String,
        default: 'value',
    },
});

defineOptions({ inheritAttrs: false });
const attrs = useAttrs();

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

const input = ref(null);

onMounted(() => {
    if (props.autofocus && input.value) {
        input.value.focus();
    }
});

function getOptionValue(option) {
    if (option === null || option === undefined) return '';
    if (typeof option === 'object') {
        return option[props.optionValue] ?? '';
    }
    return option;
}

function getOptionLabel(option) {
    if (option === null || option === undefined) return '';
    if (typeof option === 'object') {
        return option[props.optionLabel] ?? '';
    }
    return String(option);
}
</script>

<template>
    <CollectionFieldWrapper
        :classWrapper="props.classWrapper"
        :label="props.label"
        :message="props.message"
    >
        <select
            ref="input"
            v-bind="attrs"
            v-model="model"
            :disabled="props.disabled"
            :aria-invalid="isInvalid"
            :class="fieldClasses"
        >
            <option v-if="props.placeholder !== ''" value="">{{ props.placeholder }}</option>
            <option
                v-for="(opt, idx) in props.options"
                :key="`${getOptionValue(opt)}-${idx}`"
                :value="getOptionValue(opt)"
            >
                {{ getOptionLabel(opt) }}
            </option>
        </select>
    </CollectionFieldWrapper>
</template>
