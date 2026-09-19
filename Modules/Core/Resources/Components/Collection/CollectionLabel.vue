<script setup>
const props = defineProps({
    invalid: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <label
        class="mb-1 block text-sm font-medium text-[#284238]"
        :class="{ 'text-[#be123c]': props.invalid }"
    >
        <slot />
    </label>
</template>
