<script setup>
import { computed, useAttrs } from 'vue';

import CollectionFieldWrapper from './CollectionFieldWrapper.vue';

const model = defineModel({ default: () => [] });

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
    optionGroupLabel: {
        type: String,
        default: null,
    },
    optionGroupChildren: {
        type: String,
        default: null,
    },
});

defineOptions({ inheritAttrs: false });
const attrs = useAttrs();

const emit = defineEmits(['change', 'blur']);

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
        'min-h-24',
    ];
    const invalid = isInvalid.value ? ['border-[#be123c]', 'focus:ring-[#be123c]', 'focus:border-[#be123c]'] : [];
    return [...base, ...invalid].join(' ');
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

function onChange(e) {
    emit('change', e);
}

function onBlur(e) {
    emit('blur', e);
}
</script>

<template>
    <CollectionFieldWrapper
        :classWrapper="props.classWrapper"
        :label="props.label"
        :message="props.message"
    >
        <select
            v-bind="attrs"
            v-model="model"
            multiple
            :disabled="props.disabled"
            :aria-invalid="isInvalid"
            :class="fieldClasses"
            @change="onChange"
            @blur="onBlur"
        >
            <template v-if="props.optionGroupLabel && props.optionGroupChildren">
                <optgroup
                    v-for="(group, gIdx) in props.options"
                    :key="gIdx"
                    :label="getOptionLabel(group)"
                >
                    <option
                        v-for="(opt, idx) in (group[props.optionGroupChildren] || [])"
                        :key="`${getOptionValue(opt)}-${gIdx}-${idx}`"
                        :value="getOptionValue(opt)"
                    >
                        {{ getOptionLabel(opt) }}
                    </option>
                </optgroup>
            </template>
            <template v-else>
                <option
                    v-for="(opt, idx) in props.options"
                    :key="`${getOptionValue(opt)}-${idx}`"
                    :value="getOptionValue(opt)"
                >
                    {{ getOptionLabel(opt) }}
                </option>
            </template>
        </select>
    </CollectionFieldWrapper>
</template>
