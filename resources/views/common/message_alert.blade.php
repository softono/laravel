@if ($message = Session::get('success'))
<div class="alert-success mb-4">
    <strong class="flex-1">{!! $message !!}</strong>
    <button type="button" class="alert-dismiss" aria-label="Close"><i class="bx bx-x text-lg"></i></button>
</div>
@endif
@if ($message = Session::get('error'))
<div class="alert-danger mb-4">
    <strong class="flex-1">{!! $message !!}</strong>
    <button type="button" class="alert-dismiss" aria-label="Close"><i class="bx bx-x text-lg"></i></button>
</div>
@endif
@if ($message = Session::get('warning'))
<div class="alert-warning mb-4">
    <strong class="flex-1">{!! $message !!}</strong>
    <button type="button" class="alert-dismiss" aria-label="Close"><i class="bx bx-x text-lg"></i></button>
</div>
@endif
@if ($message = Session::get('info'))
<div class="alert-info mb-4">
    <strong class="flex-1">{!! $message !!}</strong>
    <button type="button" class="alert-dismiss" aria-label="Close"><i class="bx bx-x text-lg"></i></button>
</div>
@endif
@if ($errors->any())
<div class="alert-danger mb-4">
    <span class="flex-1">{!! $errors->first() !!}</span>
    <button type="button" class="alert-dismiss" aria-label="Close"><i class="bx bx-x text-lg"></i></button>
</div>
@endif
