<div class="modal fixed inset-0 z-50 hidden items-center justify-center p-4" id="sendmail">
    <div class="modal-backdrop" data-modal-dismiss></div>
    <div class="modal-dialog relative z-10 w-full max-w-2xl">
        <x-ui.card class="gap-4">
            <x-ui.card-header>
                <x-ui.card-title class="text-lg">Send Mail</x-ui.card-title>
            </x-ui.card-header>
            <x-ui.card-content>
                <form method="POST" action="{{ route('admin/user/mail') }}" id="mail-form" class="space-y-4">
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
                    <div class="flex gap-2">
                        <x-ui.button type="submit">Send</x-ui.button>
                        <x-ui.button variant="outline" data-modal-dismiss>Cancel</x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
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
