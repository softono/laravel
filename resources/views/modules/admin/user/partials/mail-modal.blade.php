<x-ui.modal id="sendmail" size="xl">
    <x-ui.dialog-header>
        <x-ui.dialog-title>Send Mail</x-ui.dialog-title>
    </x-ui.dialog-header>
    <form method="POST" action="{{ route('admin/user/mail') }}" id="mail-form" class="space-y-4" data-next="refresh">
        @csrf
        <input type="hidden" name="user_id" value="{{ $model->id }}">
        <div class="space-y-2">
            <x-ui.label for="to">To</x-ui.label>
            <x-ui.input type="email" id="to" :value="$model->email" readonly />
        </div>
        <div class="space-y-2">
            <x-ui.label for="subject">Subject</x-ui.label>
            <x-ui.input id="subject" name="subject" placeholder="Enter subject" required />
        </div>
        <div class="space-y-2">
            <x-ui.label for="message">Message</x-ui.label>
            <x-ui.textarea id="message" name="message" rows="5" placeholder="Enter message" required />
        </div>
        <x-ui.dialog-footer>
            <x-ui.button variant="outline" data-modal-dismiss>Cancel</x-ui.button>
            <x-ui.button type="submit">Send</x-ui.button>
        </x-ui.dialog-footer>
    </form>
</x-ui.modal>
@push('scripts')
    <script>
        documentReady(function() {
            $('#mail-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                },
            });
        });
    </script>
@endpush
