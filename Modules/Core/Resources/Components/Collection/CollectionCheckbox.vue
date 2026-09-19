<script setup>
import { computed, useAttrs } from 'vue';

import CollectionLabel from './CollectionLabel.vue';

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

const wrapperClasses = computed(() => ['flex items-center gap-2', props.classWrapper]);
</script>

<template>
    <label v-if="props.label !== ''" :class="wrapperClasses">
        <input
            v-bind="attrs"
            v-model="model"
            type="checkbox"
            :disabled="props.disabled"
            class="size-4 rounded border-[#d7e0d9] text-[#17663a] focus:ring-2 focus:ring-[#17663a] disabled:opacity-60 disabled:cursor-not-allowed"
        />
        <CollectionLabel invalid="false">
            <span class="text-sm text-[#284238]">{{ props.label }}</span>
        </CollectionLabel>
    </label>
    <input
        v-else
        v-bind="attrs"
        v-model="model"
        type="checkbox"
        :disabled="props.disabled"
        class="size-4 rounded border-[#d7e0d9] text-[#17663a] focus:ring-2 focus:ring-[#17663a] disabled:opacity-60 disabled:cursor-not-allowed"
    />
</template>
