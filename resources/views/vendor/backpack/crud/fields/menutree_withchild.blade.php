<div class="row">
    <div class="col-sm-12" style="margin-left: {{$level+1}}rem">
        <label>
            <input
                type="checkbox"
                name="{{ $field['name'] }}[]"
                value="{{ $connected_entity_entry->id }}"
                @if( ( old( $field["name"] ) && in_array($connected_entity_entry->id, old( $field["name"])) ) || (isset($field['value']) && in_array($connected_entity_entry->id, $field['value']->pluck('id', 'id')->toArray())))
                    checked = "checked"
                @endif
            >
            <a target="_blank" href="{{ route('menu-item.edit', $connected_entity_entry->id) }}">
                <i class="la {{ $connected_entity_entry->icon }}"></i>
                {!! __($connected_entity_entry->{$field['attribute']}) !!}
            </a>
        </label>
    </div>
</div>

@foreach ($connected_entity_entry->children as $i => $child)
    @if($child->children->count() > 0)
        @include('crud::fields.menutree_withchild', [
                    'connected_entity_entry'=>$child,
                    'field' => $field,
                    'level' => $level+1
                ])
    @else
        @include('crud::fields.menutree_nochild', [
                    'connected_entity_entry'=>$child,
                    'field' => $field,
                    'level' => $level+1
                ])
    @endif
@endforeach
