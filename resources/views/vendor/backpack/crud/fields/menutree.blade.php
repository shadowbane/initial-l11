@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

@php
    $trees = $field['model']::getTree()->groupBy('grouping');
@endphp

<div class="row">
    @foreach ($trees as $key=>$val)
        @php($key = !blank($key) ? $key : "No Group")
        <div class="col-sm-6 col-md-3">
            <div class="card">
                <div class="card-header bg-light"><strong>{{ $key }}</strong></div>
                <div class="card-body">
                    @foreach ($val as $k => $connected_entity_entry)
                        @php($level = 0)
                        @if ($connected_entity_entry->children->count() > 0)
                            @include('crud::fields.menutree_withchild', [
                                'connected_entity_entry'=>$connected_entity_entry,
                                'field' => $field,
                                'level' => $level
                            ])
                        @else
                            @include('crud::fields.menutree_nochild', [
                                'connected_entity_entry'=>$connected_entity_entry,
                                'field' => $field,
                                'level' => $level
                            ])
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
@include('crud::fields.inc.wrapper_end')
