@if ($navigations->count())
    @foreach ($navigations as $k => $navigation)
        @php($level = 0)
        @if ($navigation->childrens->count() > 0)
            @include('components._sidebar._withchild', ['mi'=>$navigation, 'level' => $level++])
        @else
            @include('components._sidebar._nochild', ['m'=>$navigation, 'level' => $level++])
        @endif
    @endforeach
@endif
