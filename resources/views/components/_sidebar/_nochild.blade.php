@if(stripos($m->link, '{id}'))
    @php
        $m->link = str_replace('{id}', backpack_auth()->user()->uuid, $m->link)
    @endphp
@endif
@if(stripos($m->link, '{active}'))
    @php
        $m->link = str_replace('{active}', config('siakad.global.semester_berlaku'), $m->link);
    @endphp
@endif

@if(stripos($m->link, '{active}'))
    @php
        $m->link = str_replace('{active}', config('siakad.global.semester_berlaku'), $m->link);
    @endphp
@endif

@if ($level > 0)
    <a class="dropdown-item" href="{{ url(config('backpack.base.route_prefix') . '/' . $m->link) }}">
        <i class="la {{ $m->icon }}"></i> {!! __($m->name) !!}
    </a>
@else
    <li class="nav-item">
        <a class="nav-link" href="{{ url(config('backpack.base.route_prefix') . '/' . $m->link) }}">
            <i class="la {{ $m->icon }} nav-icon"></i> {!! __($m->name) !!}
        </a>
    </li>
@endif
{{--<div class="dropdown-menu show" data-bs-popper="static">--}}

{{--<li class="nav-item">--}}
{{--    <a class="nav-link" href="{{ url(config('backpack.base.route_prefix') . '/' . $m->link) }}">--}}
{{--        <i class="la {{ $m->icon }}"></i> {{ __($m->name) }}--}}
{{--    </a>--}}
{{--</li>--}}
