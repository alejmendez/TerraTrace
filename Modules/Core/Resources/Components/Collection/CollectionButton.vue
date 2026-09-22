<script setup>
import { computed, useAttrs } from 'vue';
import { Link } from '@inertiajs/vue3';

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

    /**
     * `attrs.class` lets the consumer extend or override the rendered
     * classes (e.g. `class="w-full"` on a full-width submit button).
     * Without this merge, `inheritAttrs: false` + `useAttrs()` swallows
     * the user-provided class because we never spread `v-bind="attrs"`
     * onto the rendered <component>. Position at the end so it's the
     * last word in the class list, matching Vue's fallthrough convention.
     */
    return [...base, ...(variants[props.severity] ?? variants.primary), attrs.class];
});

/**
 * Click handler. Matches the legacy `Button` wrapper's contract:
 *   - short-circuit when disabled.
 *   - if onClick is set, call it (allowing `@click.prevent` etc.);
 *     otherwise let <Link> (or the native <button> submit) drive
 *     navigation itself — no manual `router.visit` here so middle /
 *     right-click and modifier-keys fall through to the browser.
 */
function handleClick(event) {
    if (isDisabled.value) {
        event.preventDefault();
        return;
    }
    if (typeof attrs.onClick === 'function') {
        attrs.onClick(event);
    }
}
</script>

<template>
    <component
        :is="isLink ? Link : 'button'"
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
