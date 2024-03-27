@if ($level === 0)
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="false">
            <i class="la {{ $mi->icon }} nav-icon"></i> {!! __($mi->name) !!}
        </a>
        <div class="dropdown-menu" data-bs-popper="static">
            <div class="dropdown-menu-columns">
                <div class="dropdown-menu-column">
                    @php($defaultLevel = $level)
                    @foreach ($mi->childrens as $i => $child)
                        @if($child->childrens->count() > 0)
                            @include('components._sidebar._withchild', ['mi'=>$child, 'level' => $defaultLevel+1])
                        @else
                            @include('components._sidebar._nochild', ['m'=>$child, 'level' => $defaultLevel+1])
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </li>

@elseif($level > 0)
    <div class="dropend">
        <a
            class="nav-link dropdown-item dropdown-toggle"
            href="#"
            data-bs-toggle="dropdown"
            data-bs-auto-close="false"
            role="button"
        >
            <i class="la {{ $mi->icon }} nav-icon"></i> {!! __($mi->name) !!}
        </a>
        <div class="dropdown-menu" data-bs-popper="static">
            @php($defaultLevel = $level)
            @foreach ($mi->childrens as $i => $child)
                @if($child->childrens->count() > 0)
                    @include('components._sidebar._withchild', ['mi'=>$child, 'level' => $defaultLevel+1])
                @else
                    @include('components._sidebar._nochild', ['m'=>$child, 'level' => $defaultLevel+1])
                @endif
            @endforeach
        </div>
    </div>
@endif
