{{-- Summernote rich-text editor on $selector, with image uploads posted to the $uploadRoute route. --}}
@push('scripts')
    <script>
        documentReady(function() {
            app.addCSS(['https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css']);
            app.loadScript('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js', function() {
                initEditorFull($('{{ $selector }}'), '{{ route($uploadRoute) }}');
            });
        });
    </script>
@endpush
