<style>
    [x-cloak] {
        display: none !important;
    }

    .confirm-dialog__cancel {
        border-radius: 1rem;
        background: #f1f5f9;
        color: #334155;
        font-size: 0.875rem;
        font-weight: 700;
        padding: 0.625rem 1rem;
    }

    .confirm-dialog__cancel:hover {
        background: #e2e8f0;
    }

    .confirm-dialog__primary {
        border-radius: 1rem;
        background: #dc2626;
        color: #ffffff;
        font-size: 0.875rem;
        font-weight: 700;
        padding: 0.625rem 1rem;
    }

    .confirm-dialog__primary:hover {
        background: #b91c1c;
    }
</style>

<div
    x-data="{
        open: false,
        form: null,
        title: 'Konfirmasi',
        message: 'Lanjutkan aksi ini?',
        confirmText: 'Ya',
        cancelText: 'Tidak',
        init() {
            document.addEventListener('submit', (event) => {
                const form = event.target.closest('form[data-confirm]');

                if (! form || form.dataset.confirmSubmitting === 'true') {
                    return;
                }

                event.preventDefault();
                this.form = form;
                this.title = form.dataset.confirmTitle || 'Konfirmasi';
                this.message = form.dataset.confirm || 'Lanjutkan aksi ini?';
                this.confirmText = form.dataset.confirmYes || 'Ya';
                this.cancelText = form.dataset.confirmNo || 'Tidak';
                this.open = true;
                this.$nextTick(() => this.$refs.cancelButton?.focus());
            }, true);
        },
        cancel() {
            this.open = false;
            this.form = null;
        },
        submit() {
            if (! this.form) {
                return;
            }

            const form = this.form;
            this.open = false;
            this.form = null;
            form.dataset.confirmSubmitting = 'true';
            form.submit();
        },
    }"
    x-cloak
    x-show="open"
    x-on:keydown.escape.window="open && cancel()"
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    aria-modal="true"
    role="dialog"
>
    <div
        x-show="open"
        x-transition.opacity
        x-on:click="cancel()"
        class="absolute inset-0 bg-slate-950/45"
    ></div>

    <div
        x-show="open"
        x-transition
        class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
    >
        <h2 class="text-lg font-extrabold text-slate-900" x-text="title"></h2>
        <p class="mt-2 text-sm leading-6 text-slate-600" x-text="message"></p>

        <div class="mt-6 flex justify-end gap-2">
            <button
                type="button"
                x-ref="cancelButton"
                x-on:click="cancel()"
                class="confirm-dialog__cancel"
                x-text="cancelText"
            ></button>
            <button
                type="button"
                x-on:click="submit()"
                class="confirm-dialog__primary"
                x-text="confirmText"
            ></button>
        </div>
    </div>
</div>
