<script setup>
import { computed, useAttrs } from 'vue';
import VueMultiselect from 'vue-multiselect';

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
    loading: {
        type: Boolean,
        default: false,
    },
});

// vue-multiselect is a fragment component, so $attrs fallthrough is
// unreliable — disable it and forward the bits we care about manually.
defineOptions({ inheritAttrs: false });
const attrs = useAttrs();

const emit = defineEmits(['change']);

const isInvalid = computed(
    () => props.message !== '' && props.message !== undefined && props.message !== null
);

/**
 * vue-multiselect uses `label` to render the visible text. The backend
 * convention here is `{value, text}`, so we build a `customLabel`
 * function that always resolves `option[optionLabel]`. Doing this
 * instead of `:label="optionLabel"` lets us support nested/missing
 * fields without changing every call site.
 */
const customLabel = (option) => {
    if (option === null || option === undefined) return '';
    if (typeof option === 'object') return option[props.optionLabel] ?? '';
    return String(option);
};

function onUpdate(val) {
    model.value = val;
    // Mirror the native `<select>` `@change` event so existing
    // consumer code (`@change="handler"`) keeps working.
    emit('change', val);
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
            :multiple="false"
            :close-on-select="true"
            :clear-on-select="true"
            :allow-empty="true"
            :internal-search="true"
            :show-no-options="false"
            :show-no-results="true"
            :class="['terra-multiselect', { 'terra-multiselect--invalid': isInvalid }]"
            @update:model-value="onUpdate"
        >
            <template #noResult>
                <span>{{ __('generics.form.multiselect.not_found') }}</span>
            </template>
        </VueMultiselect>
    </CollectionFieldWrapper>
</template>
