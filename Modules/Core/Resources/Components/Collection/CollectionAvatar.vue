<script setup>
import { computed, ref, useAttrs } from 'vue';

const props = defineProps({
    /**
     * Image URL. When empty/null/falsy, or when the image fails to
     * load, the component falls back to the user's initials.
     */
    src: {
        type: String,
        default: '',
    },
    /**
     * Display name used to derive the initials and as the default
     * `alt` text when `alt` is not provided.
     */
    name: {
        type: String,
        default: '',
    },
    alt: {
        type: String,
        default: '',
    },
    /**
     * Pixel size of the avatar (width = height). Tailwind arbitrary
     * value `[Npx]` is generated for the wrapper, the inner image
     * fills it, and the initials text is sized proportionally.
     */
    size: {
        type: [Number, String],
        default: 64,
    },
});

defineOptions({ inheritAttrs: false });
const attrs = useAttrs();

/**
 * Local flag toggled by the native `@error` event of the underlying
 * `<img>`. We track it separately from `src` so a previously-working
 * URL that 404s at runtime (e.g. avatar removed from storage but the
 * record was cached) still degrades gracefully into initials instead
 * of leaving a broken image icon on screen.
 */
const imageError = ref(false);

const hasImage = computed(() => Boolean(props.src) && !imageError.value);

const altText = computed(() => props.alt || props.name || '');

/**
 * Derive the 1–2 letter initials from `name`.
 *
 * Rules:
 *   - "Juan Pérez"   → "JP" (first letter of first + last word)
 *   - "María José"   → "MJ"
 *   - "Madonna"      → "MA" (first two letters of the single word)
 *   - "A"            → "A"  (single letter word)
 *   - ""             → "?"  (placeholder so the circle is never empty)
 *
 * Diacritics are normalized (NFD + strip combining marks) so names
 * like "Ángela" still produce a clean "A" instead of "Á".
 */
const initials = computed(() => {
    const cleaned = (props.name ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    if (!cleaned) return '?';

    const parts = cleaned.split(/\s+/).filter(Boolean);

    if (parts.length === 1) {
        const word = parts[0];
        return word.length >= 2 ? word.slice(0, 2).toUpperCase() : word.slice(0, 1).toUpperCase();
    }

    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

/**
 * Approximate font size for the initials inside the circle. Empirically
 * the legibility sweet-spot is ~31% of the avatar's pixel size, which
 * keeps the letters well clear of the border at common list-view sizes
 * (40, 48, 64, 96 px). Floored at 10px so a 24px avatar still renders.
 */
const initialsFontSize = computed(() => {
    const px = Math.max(Number(props.size) || 64, 24);
    return `${Math.max(Math.round(px * 0.31), 10)}px`;
});

const sizePx = computed(() => `${Number(props.size) || 64}px`);

/**
 * Wrapper class. The `attrs.class` merge lets consumers extend the
 * avatar with layout utilities (e.g. `class="ms-2"`) without losing
 * the default rounded-full + border + fallback bg.
 */
const wrapperClass = computed(() => [
    'inline-flex',
    'shrink-0',
    'items-center',
    'justify-center',
    'overflow-hidden',
    'rounded-full',
    'border',
    'border-[#d7e0d9]',
    'bg-[#e7f3e9]',
    'text-[#102f27]',
    'font-bold',
    'uppercase',
    'leading-none',
    'select-none',
    attrs.class,
]);

const onImgError = () => {
    imageError.value = true;
};
</script>

<template>
    <span
        :class="wrapperClass"
        :style="{ width: sizePx, height: sizePx }"
        :aria-label="altText"
        role="img"
    >
        <img
            v-if="hasImage"
            :src="src"
            :alt="altText"
            class="size-full object-cover"
            loading="lazy"
            @error="onImgError"
        >
        <span
            v-else
            class="flex size-full items-center justify-center"
            :style="{ fontSize: initialsFontSize }"
            aria-hidden="true"
        >
            {{ initials }}
        </span>
    </span>
</template>
