{{-- Confirmation dialog behind app.showConfirmationPopup() (used by app.confirmAction). Text and button labels are filled by app.js. --}}
<x-ui.modal id="confirm-modal" size="md" role="alertdialog" aria-labelledby="confirm-modal-title">
    <x-ui.dialog-header>
        <x-ui.dialog-title id="confirm-modal-title">Are you sure?</x-ui.dialog-title>
        <p class="text-muted-foreground text-sm" id="confirm-modal-text"></p>
    </x-ui.dialog-header>
    <x-ui.dialog-footer>
        <x-ui.button variant="outline" data-modal-dismiss id="confirm-modal-cancel">No</x-ui.button>
        <x-ui.button variant="destructive" id="confirm-modal-yes">Yes</x-ui.button>
    </x-ui.dialog-footer>
</x-ui.modal>
