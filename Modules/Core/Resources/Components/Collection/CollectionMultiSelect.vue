<script setup>
import { computed, useAttrs } from 'vue';
import VueMultiselect from 'vue-multiselect';

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
    placeholder: {
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
    loading: {
        type: Boolean,
        default: false,
    },
});

// vue-multiselect is a fragment component, so $attrs fallthrough is
// unreliable — disable it and forward the bits we care about manually.
defineOptions({ inheritAttrs: false });
const attrs = useAttrs();

const emit = defineEmits(['change', 'blur']);

const isInvalid = computed(
    () => props.message !== '' && props.message !== undefined && props.message !== null
);

const isGrouped = computed(
    () => Boolean(props.optionGroupLabel) && Boolean(props.optionGroupChildren)
);

/**
 * vue-multiselect's `label` prop renders the visible text. With the
 * project's `{value, text}` convention, we always resolve through
 * `option[optionLabel]` via `customLabel` so nested/missing fields
 * don't blow up at runtime.
 */
const customLabel = (option) => {
    if (option === null || option === undefined) return '';
    if (typeof option === 'object') return option[props.optionLabel] ?? '';
    return String(option);
};

function onUpdate(val) {
    model.value = val ?? [];
    // Mirror the native `<select multiple>` `@change` event so existing
    // consumer code (`@change="handler"`) keeps working.
    emit('change', val ?? []);
}

function onBlur() {
    emit('blur');
}
</script>

<template>
    <CollectionFieldWrapper
        :classWrapper="props.classWrapper"
        :label="props.label"
        :message="props.message"
    >
        <VueMultiselect
            :id="attrs.id"
            :model-value="model"
            :options="props.options"
            :track-by="props.optionValue"
            :custom-label="customLabel"
            :placeholder="props.placeholder"
            :disabled="props.disabled"
            :loading="props.loading"
            :multiple="true"
            :close-on-select="false"
            :clear-on-select="false"
            :allow-empty="true"
            :internal-search="true"
            :show-no-options="false"
            :show-no-results="true"
            :group-values="isGrouped ? props.optionGroupChildren : null"
            :group-label="isGrouped ? props.optionGroupLabel : null"
            :group-select="false"
            :class="['terra-multiselect', 'terra-multiselect--multiple', { 'terra-multiselect--invalid': isInvalid }]"
            @update:model-value="onUpdate"
            @blur="onBlur"
        >
            <template #noResult>
                <span>{{ __('generics.form.multiselect.not_found') }}</span>
            </template>
        </VueMultiselect>
    </CollectionFieldWrapper>
</template>
