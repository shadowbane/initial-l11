@extends(backpack_view('blank'))
@php
    $breadcrumbs = [
        'dashboard' => url(config('backpack.base.route_prefix'), 'dashboard'),
    ];
@endphp

@section('header')
    <div class="container-fluid pt-3 mb-1">
        <div class="animated fadeIn">
            @include('dashboard.widgets.header')
        </div>
    </div>
@endsection

@section('content')
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-sm-12 col-md-8 my-md-4 my-2 order-2 order-md-0">
                        @hasanyrole('System Administrators')
                        @include('dashboard.widgets.sysadmin')
                        @endhasanyrole
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('after_scripts')
    <script>
        system.ready(() => {
        });

    </script>
@endpush
