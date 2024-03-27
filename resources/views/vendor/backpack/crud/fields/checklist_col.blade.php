<!-- select2 -->
@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

@php
    $permissions = $field['model']::selectRaw("`id`, SUBSTRING_INDEX(`name`, '@', 1) AS `namespace`, SUBSTRING_INDEX(`name`, '@', -1) as `valid_action`, `shortname`")->orderBy('namespace', 'ASC')->get();
@endphp
<div class="tableFixHead">
    <table class="box table table-bordered table-striped table-hover display responsive nowrap m-t-0">
        <tr>
            <th style="width: 20px">Permission</th>
            <th style="width: 20px">View</th>
            <th style="width: 20px">Create</th>
            <th style="width: 20px">Update</th>
            <th style="width: 20px">Delete</th>
        </tr>
        <tbody>
        @foreach ($permissions->sortBy('shortname')->unique('shortname')->pluck('shortname') as $entry)
            <tr>
                <td>{{ $entry }}</td>

                @php($p = $permissions->where('shortname', $entry)->where('valid_action', 'view')->first())
                <td style="text-align: center">
                    <input type="checkbox" name="{{ $field['name'] }}[]" value="{{ $p->id }}"
                           @if( ( old( $field["name"] ) && in_array($p->id, old( $field["name"])) ) || (isset($field['value']) && in_array($p->id, $field['value']->pluck('id', 'id')->toArray())))
                               checked = "checked"
                        @endif
                    >
                </td>

                @php($p = $permissions->where('shortname', $entry)->where('valid_action', 'create')->first())
                <td style="text-align: center">
                    <input type="checkbox" name="{{ $field['name'] }}[]" value="{{ $p->id }}"
                           @if( ( old( $field["name"] ) && in_array($p->id, old( $field["name"])) ) || (isset($field['value']) && in_array($p->id, $field['value']->pluck('id', 'id')->toArray())))
                               checked = "checked"
                        @endif
                    >
                </td>

                @php($p = $permissions->where('shortname', $entry)->where('valid_action', 'update')->first())
                <td style="text-align: center">
                    <input type="checkbox" name="{{ $field['name'] }}[]" value="{{ $p->id }}"
                           @if( ( old( $field["name"] ) && in_array($p->id, old( $field["name"])) ) || (isset($field['value']) && in_array($p->id, $field['value']->pluck('id', 'id')->toArray())))
                               checked = "checked"
                        @endif
                    >
                </td>

                @php($p = $permissions->where('shortname', $entry)->where('valid_action', 'delete')->first())
                <td style="text-align: center">
                    <input type="checkbox" name="{{ $field['name'] }}[]" value="{{ $p->id }}"
                           @if( ( old( $field["name"] ) && in_array($p->id, old( $field["name"])) ) || (isset($field['value']) && in_array($p->id, $field['value']->pluck('id', 'id')->toArray())))
                               checked = "checked"
                        @endif
                    >
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')


@if ($crud->checkIfFieldIsFirstOfItsType($field))
    @push('crud_fields_styles')

    @endpush

    @push('crud_fields_scripts')

    @endpush
@endif
