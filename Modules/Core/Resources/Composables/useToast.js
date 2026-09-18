import { ref } from 'vue';

/**
 * Module-level singleton state for transient toasts. One toast at
 * a time (replaces by the next call). Mount <CollectionToast> at
 * the app root (see AuthenticatedLayout) and pass these refs down.
 */
const state = {
    visible: ref(false),
    message: ref(''),
    tone: ref('success'),
};

let hideTimer = null;

export function useToast() {
    return {
        visible: state.visible,
        message: state.message,
        tone: state.tone,

        /**
         * Show a toast. Replaces any previous toast.
         *
         * @param {object}  opts
         * @param {string}  opts.message
         * @param {string}  [opts.tone='success']   'success' | 'error'
         * @param {string}  [opts.summary]          PrimeVue-compat alias for message
         * @param {string}  [opts.detail]           PrimeVue-compat alias for message
         * @param {string}  [opts.severity]         PrimeVue-compat: 'success' | 'error'
         * @param {number}  [opts.life=3000]        ms before auto-hide
         */
        show(opts) {
            state.message.value = opts.summary ?? opts.detail ?? opts.message ?? '';
            state.tone.value = opts.tone ?? (opts.severity === 'error' ? 'error' : 'success');
            state.visible.value = true;

            if (hideTimer) clearTimeout(hideTimer);
            hideTimer = setTimeout(() => {
                state.visible.value = false;
            }, opts.life ?? 3000);
        },

        dismiss() {
            if (hideTimer) clearTimeout(hideTimer);
            state.visible.value = false;
        },
    };
}
