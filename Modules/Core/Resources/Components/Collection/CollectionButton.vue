<script setup>
import { computed, useAttrs } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    label: {
        type: String,
        default: '',
    },
    severity: {
        type: String,
        default: 'primary',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    href: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'button',
    },
});

defineOptions({ inheritAttrs: false });
const attrs = useAttrs();

const isLink = computed(() => Boolean(props.href));
const tag = computed(() => (isLink.value ? 'a' : 'button'));

/**
 * Disabled state. `attrs.disabled` is set via `:disabled="..."` on
 * the consumer side; it can be either a boolean or `''` (Vue truthy
 * shorthand for `true`).
 */
const isDisabled = computed(
    () => props.loading || attrs.disabled === '' || attrs.disabled === true
);

const classes = computed(() => {
    const base = [
        'inline-flex',
        'items-center',
        'justify-center',
        'gap-2',
        'rounded-lg',
        'px-4',
        'py-2.5',
        'font-semibold',
        'transition',
        'focus:outline-none',
        'focus:ring-2',
        'focus:ring-offset-2',
        'disabled:cursor-not-allowed',
        'disabled:opacity-60',
    ];

    const variants = {
        primary: [
            'bg-[#17663a]',
            'text-white',
            'shadow-sm',
            'hover:bg-[#11522e]',
            'focus:ring-[#17663a]',
        ],
        secondary: [
            'bg-white',
            'text-[#284238]',
            'border',
            'border-[#d7e0d9]',
            'hover:bg-[#edf5ed]',
            'hover:text-[#17663a]',
            'focus:ring-[#17663a]',
        ],
        danger: [
            'bg-[#be123c]',
            'text-white',
            'shadow-sm',
            'hover:bg-[#9c0e2f]',
            'focus:ring-[#be123c]',
        ],
        success: [
            'bg-[#17663a]',
            'text-white',
            'shadow-sm',
            'hover:bg-[#11522e]',
            'focus:ring-[#17663a]',
        ],
    };

    return [...base, ...(variants[props.severity] ?? variants.primary)];
});

/**
 * Click handler. Matches the legacy `Button` wrapper's contract:
 *   - if `href` is set and no explicit onClick, navigate via Inertia.
 *   - if onClick is set, call it (allowing `@click.prevent` etc.).
 *   - always short-circuit when disabled.
 */
function handleClick(event) {
    if (isDisabled.value) {
        event.preventDefault();
        return;
    }
    if (typeof attrs.onClick === 'function') {
        attrs.onClick(event);
        return;
    }
    if (props.href) {
        router.visit(props.href);
    }
}
</script>

<template>
    <component
        :is="tag"
        :type="!isLink ? type : undefined"
        :href="isLink ? href : undefined"
        :disabled="!isLink ? isDisabled : undefined"
        :aria-disabled="isDisabled ? 'true' : undefined"
        :aria-busy="loading ? 'true' : undefined"
        :class="classes"
        @click="handleClick"
    >
        <span
            v-if="loading"
            class="inline-block size-4 rounded-full border-2 border-current border-t-transparent animate-spin"
            aria-hidden="true"
        />
        <slot>{{ label }}</slot>
    </component>
</template>
