<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import { Mention, MentionBlot } from 'quill-mention';
import 'quill-mention/dist/quill.mention.min.css';

// Register mention module ONCE globally. Quill.imports is keyed by the
// module/blot path; checking before registering avoids "Blot already
// registered" warnings when multiple VEditor instances mount on a page.
if (!Quill.imports['modules/mention']) {
    Quill.register({ 'blots/mention': MentionBlot, 'modules/mention': Mention });
}

const model = defineModel({ default: '' });

const props = defineProps({
    id: {
        type: String,
        default: null,
    },
    editorStyle: {
        type: String,
        default: 'height: 220px',
    },
    options: {
        // Array of {value, text} used to populate @mention suggestions.
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const editorRef = ref(null);

let quill = null;
// Guards against the Quill → Vue → Quill feedback loop. Quill's
// text-change fires synchronously during dangerouslyPasteHTML; the Vue
// watcher would otherwise re-sync that back into Quill and the cursor
// would jump to the end on every programmatic update.
let suppressNextSync = false;

const buildModules = () => ({
    toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ script: 'sub' }, { script: 'super' }],
        [{ indent: '-1' }, { indent: '+1' }],
        [{ direction: 'rtl' }],
        [{ size: ['small', false, 'large', 'huge'] }],
        [{ header: [1, 2, 3, 4, 5, 6, false] }],
        [{ color: [] }, { background: [] }],
        [{ font: [] }],
        ['clean'],
    ],
    mention: {
        allowedChars: /^[A-Za-z\s]*$/,
        mentionDenotationChars: ['@'],
        source: (searchTerm, renderList) => {
            const matches = props.options
                .filter((option) => option.text?.toLowerCase().includes(searchTerm.toLowerCase()))
                .map((option) => ({ id: option.value, value: option.text }));
            renderList(matches, searchTerm);
        },
    },
});

onMounted(() => {
    quill = new Quill(editorRef.value, {
        theme: 'snow',
        modules: buildModules(),
        placeholder: props.placeholder,
        readOnly: props.disabled,
    });

    // Initial sync model → Quill.
    if (model.value) {
        suppressNextSync = true;
        quill.clipboard.dangerouslyPasteHTML(model.value);
    }

    // Quill → model. Only emit when the change came from the user (not
    // from our own dangerouslyPasteHTML sync, which fires with 'api').
    quill.on('text-change', (_delta, _oldContents, source) => {
        if (source === 'user') {
            model.value = quill.root.innerHTML;
        }
    });
});

// model → Quill. Triggered when the parent resets or programmatically
// updates the bound value.
watch(model, (newValue) => {
    if (!quill) return;
    const current = quill.root.innerHTML;
    if (newValue === current) return;
    suppressNextSync = true;
    quill.clipboard.dangerouslyPasteHTML(newValue ?? '');
});

watch(() => props.disabled, (next) => {
    quill?.enable(!next);
});

watch(() => props.options, () => {
    // Quill's mention source is captured at init; rebuild the editor so
    // the new options take effect. Cheap because the page rarely mutates
    // the user list while a comment box is open.
    if (!quill || !editorRef.value) return;
    quill.enable(true);
    const previousHtml = quill.root.innerHTML;
    quill = new Quill(editorRef.value, {
        theme: 'snow',
        modules: buildModules(),
        placeholder: props.placeholder,
        readOnly: props.disabled,
    });
    suppressNextSync = true;
    quill.clipboard.dangerouslyPasteHTML(previousHtml);
    quill.on('text-change', (_d, _o, source) => {
        if (source === 'user') {
            model.value = quill.root.innerHTML;
        }
    });
}, { deep: true });

onBeforeUnmount(() => {
    quill = null;
});
</script>

<template>
    <div :id="props.id" ref="editorRef" class="terra-editor" :style="props.editorStyle" />
</template>

<style>
.terra-editor .ql-editor {
    min-height: 6rem;
}
.terra-editor .ql-editor.ql-blank::before {
    color: #94a3b8;
    font-style: normal;
}
</style>
