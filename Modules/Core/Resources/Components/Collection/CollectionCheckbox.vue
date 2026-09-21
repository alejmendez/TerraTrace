<script setup>
import { computed, useAttrs } from 'vue';

const model = defineModel({ default: false });

const attrs = useAttrs();

const props = defineProps({
    classWrapper: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineOptions({ inheritAttrs: false });

const checkboxClasses = [
    'size-4 shrink-0 cursor-pointer rounded-sm border border-[#b8c9bd] bg-white accent-[#17663a]',
    'transition-colors hover:border-[#17663a]',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#c9e8d1] focus-visible:ring-offset-2',
    'disabled:cursor-not-allowed disabled:opacity-50',
];

const wrapperClasses = computed(() => [
    'inline-flex max-w-full items-center gap-2',
    props.disabled ? 'cursor-not-allowed' : 'cursor-pointer',
    props.classWrapper,
]);
</script>

<template>
    <label v-if="props.label !== ''" :class="wrapperClasses">
        <input
            v-bind="attrs"
            v-model="model"
            type="checkbox"
            :disabled="props.disabled"
            :class="checkboxClasses"
        />
        <span class="select-none text-sm font-medium leading-5 text-[#284238]">{{ props.label }}</span>
    </label>
    <input
        v-else
        v-bind="attrs"
        v-model="model"
        type="checkbox"
        :disabled="props.disabled"
        :class="checkboxClasses"
    />
</template>
