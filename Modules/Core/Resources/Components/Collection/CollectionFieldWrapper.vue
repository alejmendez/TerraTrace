<script setup>
import { computed, useAttrs } from 'vue';

import CollectionLabel from './CollectionLabel.vue';

const props = defineProps({
    classWrapper: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    message: {
        type: String,
        default: '',
    },
});

const attrs = useAttrs();

const isInvalid = computed(
    () => props.message !== '' && props.message !== undefined && props.message !== null
);
</script>

<template>
    <div :class="props.classWrapper">
        <CollectionLabel
            v-if="props.label !== ''"
            :for="attrs.id"
            :invalid="isInvalid"
        >
            {{ props.label }}
        </CollectionLabel>

        <slot />

        <p
            v-if="props.message"
            class="mt-1 text-sm text-[#be123c]"
            role="alert"
        >
            {{ props.message }}
        </p>
    </div>
</template>
