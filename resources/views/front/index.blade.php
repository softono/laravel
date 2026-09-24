@extends('layouts.main')
@section('title')
Home
@endsection
@section('content')

<div>
    <h4 class="text-xl font-bold py-3 mb-4">Home</h4>

    <div class="grid grid-cols-1 gap-6 mb-12 md:grid-cols-2 lg:grid-cols-3">
            <div class="card flex h-full flex-col">
                <div class="flex h-48 w-full items-center justify-center rounded-t-lg bg-slate-100">
                    <i class="bx bx-image text-5xl text-slate-400"></i>
                </div>
                <div class="card-body flex flex-1 flex-col">
                <h5 class="card-title">Card title</h5>
                <p class="mt-2 flex-1 text-sm text-slate-600">
                    Some quick example text to build on the card title and make up the bulk of the card's content.
                </p>
                <a href="javascript:void(0)" class="btn-outline mt-4 self-start">Go somewhere</a>
                </div>
            </div>
            <div class="card flex h-full flex-col">
                <div class="flex h-48 w-full items-center justify-center rounded-t-lg bg-slate-100">
                    <i class="bx bx-image text-5xl text-slate-400"></i>
                </div>
                <div class="card-body flex flex-1 flex-col">
                    <h5 class="card-title">Card title</h5>
                    <p class="mt-2 flex-1 text-sm text-slate-600">
                    Some quick example text to build on the card title and make up the bulk of the card's content.
                    </p>
                    <a href="javascript:void(0)" class="btn-outline mt-4 self-start">Go somewhere</a>
                </div>
            </div>
            <div class="card flex h-full flex-col">
                <div class="flex h-48 w-full items-center justify-center rounded-t-lg bg-slate-100">
                    <i class="bx bx-image text-5xl text-slate-400"></i>
                </div>
                <div class="card-body flex flex-1 flex-col">
                    <h5 class="card-title">Card title</h5>
                    <p class="mt-2 flex-1 text-sm text-slate-600">
                    Some quick example text to build on the card title and make up the bulk of the card's content.
                    </p>
                    <a href="javascript:void(0)" class="btn-outline mt-4 self-start">Go somewhere</a>
                </div>
            </div>
    </div>
</div>

@endsection
