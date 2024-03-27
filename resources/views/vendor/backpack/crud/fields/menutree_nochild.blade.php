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
