{{-- Profile card of an account on the admin user/admin view pages. $base is the module route prefix, e.g. admin/user. --}}
@php($viewer = auth()->user())
<x-ui.card>
    <x-ui.card-content class="space-y-6">
        <div class="flex flex-col items-center gap-2 text-center">
            <img class="size-24 rounded-lg border object-cover"
                src="{{ $general->getFileUrl($model->image, 'profile') }}" alt="{{ $model->first_name }}">
            <h4 class="text-lg font-semibold">{{ $model->first_name.' '.$model->last_name }}</h4>
            <x-ui.badge variant="secondary">{{ $model->role }}</x-ui.badge>
        </div>

        <dl class="grid gap-3 text-sm">
            @foreach ([
                'Email' => $model->email,
                'Phone' => $model->phone,
                'Country' => config('countries')[$model->country] ?? $model->country,
                'Timezone' => $model->timezone,
                'Registered IP' => $model->registered_ip,
                'Created' => $general->dateFormat($model->created_at),
                'Updated' => $general->dateFormat($model->updated_at),
            ] as $label => $value)
                <div class="flex justify-between gap-4">
                    <dt class="text-muted-foreground">{{ $label }}</dt>
                    <dd class="break-all text-right font-medium">{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
            <div class="flex justify-between gap-4">
                <dt class="text-muted-foreground">Status</dt>
                <dd>@include('modules.admin.partials.status-badge', ['status' => $model->status])</dd>
            </div>
            @if ($loginLocked ?? false)
                <div class="flex justify-between gap-4">
                    <dt class="text-muted-foreground">Login</dt>
                    <dd><x-ui.badge variant="destructive">Locked</x-ui.badge></dd>
                </div>
            @endif
        </dl>

        <div class="flex flex-wrap justify-center gap-2">
            @if ($viewer->hasPermission($base.'/update'))
                <x-ui.button :href="route($base.'/update', ['id' => $model->id])" class="pjax">Edit</x-ui.button>
            @endif
            @if ($viewer->hasPermission($base.'/delete'))
                <x-ui.button variant="destructive" onclick="app.confirmAction(this);"
                    data-action="{{ route($base.'/delete') }}" data-id="{{ $model->id }}"
                    data-next="load" data-next-url="{{ route($base) }}">Delete</x-ui.button>
            @endif
            {{ $actions ?? '' }}
        </div>
    </x-ui.card-content>
</x-ui.card>
