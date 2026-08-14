<div x-data="confirmDialog()" x-show="open" x-cloak
     x-trap.inert.noscroll="open"
     @keydown.escape.window="cancel()"
     class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
    <div class="modal-panel" @click.outside="cancel()" style="max-width: 26rem;">
        <div class="modal-body">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                     :class="danger ? 'bg-[var(--color-danger-soft)] text-[var(--color-danger)]' : 'bg-[var(--color-accent-soft)] text-[var(--color-primary)]'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 id="confirm-title" class="text-lg font-bold" x-text="title"></h3>
                    <p class="text-sm text-[var(--color-text-secondary)] mt-1" x-text="message"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="cancel()" x-text="cancelLabel"></button>
            <button type="button" class="btn" :class="danger ? 'btn-danger' : ''" @click="confirm()" x-text="confirmLabel"></button>
        </div>
    </div>
</div>
