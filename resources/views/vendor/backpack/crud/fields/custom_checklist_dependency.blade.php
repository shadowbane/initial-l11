<!-- dependencyJson -->
@php
    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['class'] = $field['wrapper']['class'] ?? 'form-group col-sm-12';
    $field['wrapper']['class'] = $field['wrapper']['class'].' checklist_dependency';
    $field['wrapper']['data-entity'] = $field['wrapper']['data-entity'] ?? $field['field_unique_name'];
    $field['wrapper']['data-init-function'] = $field['wrapper']['init-function'] ?? 'bpFieldInitChecklistDependencyElement';
@endphp

@include('crud::fields.inc.wrapper_start')

<label>{!! $field['label'] !!}</label>
<?php
$entity_model = $crud->getModel();

//short name for dependency fields
$primary_dependency = $field['subfields']['primary'];
$secondary_dependency = $field['subfields']['secondary'];

//all items with relation
$dependencies = $primary_dependency['model']::with($primary_dependency['entity_secondary'])->get();

$dependencyArray = [];

//convert dependency array to simple matrix ( prymary id as key and array with secondaries id )
foreach ($dependencies as $primary) {
    $dependencyArray[$primary->id] = [];
    foreach ($primary->{$primary_dependency['entity_secondary']} as $secondary) {
        $dependencyArray[$primary->id][] = $secondary->id;
    }
}

//for update form, get initial state of the entity
if (isset($id) && $id) {
    //get entity with relations for primary dependency
    $entity_dependencies = $entity_model->with($primary_dependency['entity'])
        ->with($primary_dependency['entity'].'.'.$primary_dependency['entity_secondary'])
        ->find($id);

    $secondaries_from_primary = [];

    //convert relation in array
    $primary_array = $entity_dependencies->{$primary_dependency['entity']}->toArray();

    $secondary_ids = [];

    //create secondary dependency from primary relation, used to check what chekbox must be check from second checklist
    if (old($primary_dependency['name'])) {
        foreach (old($primary_dependency['name']) as $primary_item) {
            foreach ($dependencyArray[$primary_item] as $second_item) {
                $secondary_ids[$second_item] = $second_item;
            }
        }
    } else { //create dependecies from relation if not from validate error
        foreach ($primary_array as $primary_item) {
            foreach ($primary_item[$secondary_dependency['entity']] as $second_item) {
                $secondary_ids[$second_item['id']] = $second_item['id'];
            }
        }
    }
}

//json encode of dependency matrix
$dependencyJson = json_encode($dependencyArray);
?>
<script>
    var {{ $field['field_unique_name'] }} = {!! $dependencyJson !!};
</script>

<div class="row">
    <div class="col-md-2 col-xs-12">
        <label><h4>{!! $primary_dependency['label'] !!}</h4></label>
    </div>

    <div class="hidden_fields_primary" data-name="{{ $primary_dependency['name'] }}">
        @if(isset($field['value']))
            @if(old($primary_dependency['name']))
                @foreach( old($primary_dependency['name']) as $item )
                    <input type="hidden" class="primary_hidden" name="{{ $primary_dependency['name'] }}[]"
                           value="{{ $item }}">
                @endforeach
            @else
                @foreach( $field['value'][0]->pluck('id', 'id')->toArray() as $item )
                    <input type="hidden" class="primary_hidden" name="{{ $primary_dependency['name'] }}[]"
                           value="{{ $item }}">
                @endforeach
            @endif
        @endif
    </div>

    <div class="col-md-10 col-xs-12">
        <div class="row">
            @foreach ($primary_dependency['model']::all() as $connected_entity_entry)
                <div class="col">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   data-id="{{ $connected_entity_entry->id }}"
                                   class='primary_list'
                            @foreach ($primary_dependency as $attribute => $value)
                                @if (is_string($attribute) && $attribute != 'value')
                                    @if ($attribute=='name')
                                        {{ $attribute }}="{{ $value }}_show[]"
                                    @else
                                        {{ $attribute }}="{{ $value }}"
                                    @endif
                                @endif
                            @endforeach
                            value="{{ $connected_entity_entry->id }}"

                            @if(
                                (
                                    isset($field['value'])
                                    && is_array($field['value'])
                                    && in_array($connected_entity_entry->id, $field['value'][0]->pluck('id', 'id')->toArray())
                                ) || (
                                    old($primary_dependency["name"])
                                    && in_array($connected_entity_entry->id, old( $primary_dependency["name"]))
                                )
                            )
                                checked = "checked"
                            @endif >
                            {{ $connected_entity_entry->{$primary_dependency['attribute']} }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row pt-3">
    <div class="col-md-2 col-xs-12">
        <label><h4>{!! $secondary_dependency['label'] !!}</h4></label>
    </div>

    <div class="hidden_fields_secondary" data-name="{{ $secondary_dependency['name'] }}">
        @if(isset($field['value']))
            @if(old($secondary_dependency['name']))
                @foreach( old($secondary_dependency['name']) as $item )
                    <input type="hidden" class="secondary_hidden"
                           name="{{ $secondary_dependency['name'] }}[]"
                           value="{{ $item }}">
                @endforeach
            @else
                @foreach( $field['value'][1]->pluck('id', 'id')->toArray() as $item )
                    <input type="hidden" class="secondary_hidden"
                           name="{{ $secondary_dependency['name'] }}[]"
                           value="{{ $item }}">
                @endforeach
            @endif
        @endif
    </div>

    {{-- Custom Dependency --}}
    @php
        $permissions = $secondary_dependency['model']::selectRaw("`id`, SUBSTRING_INDEX(`name`, '@', 1) AS `namespace`, SUBSTRING_INDEX(`name`, '@', -1) as `valid_action`, `shortname`")->orderBy('namespace', 'ASC')->get();
    @endphp
    <div class="tableFixHead col-md-10 col-xs-12">
        <table
            class="box table table-bordered table-striped table-hover table-responsive-sm table-sm display nowrap m-t-0">
            <tr>
                <th style="width: 40%">Permission</th>
                <th style="width: 15%; text-align: center;">View</th>
                <th style="width: 15%; text-align: center;">Create</th>
                <th style="width: 15%; text-align: center;">Update</th>
                <th style="width: 15%; text-align: center;">Delete</th>
            </tr>
            <tbody>
            @foreach ($permissions->unique('shortname')->pluck('shortname') as $entry)
                <tr>
                    <td>{{ $entry }}</td>
                    @php($p = $permissions->where('shortname', $entry)->where('valid_action', 'view')->first())
                    <td style="text-align: center">
                        <input
                            type="checkbox"
                            class='secondary_list'
                            data-id="{{ $p->id }}"
                            value="{{ $p->id }}"

                        @foreach ($secondary_dependency as $attribute => $value)
                            @if (is_string($attribute) && $attribute != 'value')
                                @if ($attribute=='name')
                                    {{ $attribute }}="{{ $value }}_show[]"
                                @else
                                    {{ $attribute }}="{{ $value }}"
                                @endif
                            @endif
                        @endforeach
                        @if( ( isset($field['value']) && is_array($field['value']) && (  in_array($p->id, $field['value'][1]->pluck('id', 'id')->toArray()) || isset( $secondary_ids[$p->id])) || ( old($secondary_dependency['name']) &&   in_array($p->id, old($secondary_dependency['name'])) )))
                            checked = "checked"
                            @if (isset($secondary_ids[$p->id]))
                                disabled = disabled
                            @endif
                        @endif
                        >
                    </td>

                    @php($p = $permissions->where('shortname', $entry)->where('valid_action', 'create')->first())
                    <td style="text-align: center">
                        <input
                            type="checkbox"
                            class='secondary_list'
                            data-id="{{ $p->id }}"
                            value="{{ $p->id }}"

                        @foreach ($secondary_dependency as $attribute => $value)
                            @if (is_string($attribute) && $attribute != 'value')
                                @if ($attribute=='name')
                                    {{ $attribute }}="{{ $value }}_show[]"
                                @else
                                    {{ $attribute }}="{{ $value }}"
                                @endif
                            @endif
                        @endforeach
                        @if( ( isset($field['value']) && is_array($field['value']) && (  in_array($p->id, $field['value'][1]->pluck('id', 'id')->toArray()) || isset( $secondary_ids[$p->id])) || ( old($secondary_dependency['name']) &&   in_array($p->id, old($secondary_dependency['name'])) )))
                            checked = "checked"
                            @if (isset($secondary_ids[$p->id]))
                                disabled = disabled
                            @endif
                        @endif
                        >
                    </td>

                    @php($p = $permissions->where('shortname', $entry)->where('valid_action', 'update')->first())
                    <td style="text-align: center">
                        <input
                            type="checkbox"
                            class='secondary_list'
                            data-id="{{ $p->id }}"
                            value="{{ $p->id }}"

                        @foreach ($secondary_dependency as $attribute => $value)
                            @if (is_string($attribute) && $attribute != 'value')
                                @if ($attribute=='name')
                                    {{ $attribute }}="{{ $value }}_show[]"
                                @else
                                    {{ $attribute }}="{{ $value }}"
                                @endif
                            @endif
                        @endforeach
                        @if( ( isset($field['value']) && is_array($field['value']) && (  in_array($p->id, $field['value'][1]->pluck('id', 'id')->toArray()) || isset( $secondary_ids[$p->id])) || ( old($secondary_dependency['name']) &&   in_array($p->id, old($secondary_dependency['name'])) )))
                            checked = "checked"
                            @if (isset($secondary_ids[$p->id]))
                                disabled = disabled
                            @endif
                        @endif
                        >
                    </td>

                    @php($p = $permissions->where('shortname', $entry)->where('valid_action', 'delete')->first())
                    <td style="text-align: center">
                        <input
                            type="checkbox"
                            class='secondary_list'
                            data-id="{{ $p->id }}"
                            value="{{ $p->id }}"

                        @foreach ($secondary_dependency as $attribute => $value)
                            @if (is_string($attribute) && $attribute != 'value')
                                @if ($attribute=='name')
                                    {{ $attribute }}="{{ $value }}_show[]"
                                @else
                                    {{ $attribute }}="{{ $value }}"
                                @endif
                            @endif
                        @endforeach
                        @if( ( isset($field['value']) && is_array($field['value']) && (  in_array($p->id, $field['value'][1]->pluck('id', 'id')->toArray()) || isset( $secondary_ids[$p->id])) || ( old($secondary_dependency['name']) &&   in_array($p->id, old($secondary_dependency['name'])) )))
                            checked = "checked"
                            @if (isset($secondary_ids[$p->id]))
                                disabled = disabled
                            @endif
                        @endif
                        >
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{-- .Custom Dependency --}}
</div>

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif

@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include checklist_dependency js-->
        <script>
            jQuery(document).ready(function ($) {

                $('.checklist_dependency').each(function (index, item) {

                    var unique_name = $(this).data('entity');
                    var dependencyJson = window[unique_name];

                    var thisField = $(this);
                    thisField.find('.primary_list').change(function () {

                        var idCurrent = $(this).data('id');
                        if ($(this).is(':checked')) {

                            //add hidden field with this value
                            var nameInput = thisField.find('.hidden_fields_primary').data('name');
                            var inputToAdd = $('<input type="hidden" class="primary_hidden" name="' + nameInput + '[]" value="' + idCurrent + '">');

                            thisField.find('.hidden_fields_primary').append(inputToAdd);

                            $.each(dependencyJson[idCurrent], function (key, value) {
                                //check and disable secondies checkbox
                                thisField.find('input.secondary_list[value="' + value + '"]').prop("checked", true);
                                thisField.find('input.secondary_list[value="' + value + '"]').prop("disabled", true);
                                //remove hidden fields with secondary dependency if was setted
                                var hidden = thisField.find('input.secondary_hidden[value="' + value + '"]');
                                if (hidden)
                                    hidden.remove();
                            });

                        } else {
                            //remove hidden field with this value
                            thisField.find('input.primary_hidden[value="' + idCurrent + '"]').remove();

                            // uncheck and active secondary checkboxs if are not in other selected primary.

                            var secondary = dependencyJson[idCurrent];

                            var selected = [];
                            thisField.find('input.primary_hidden').each(function (index, input) {
                                selected.push($(this).val());
                            });

                            $.each(secondary, function (index, secondaryItem) {
                                var ok = 1;

                                $.each(selected, function (index2, selectedItem) {
                                    if (dependencyJson[selectedItem].indexOf(secondaryItem) != -1) {
                                        ok = 0;
                                    }
                                });

                                if (ok) {
                                    thisField.find('input.secondary_list[value="' + secondaryItem + '"]').prop('checked', false);
                                    thisField.find('input.secondary_list[value="' + secondaryItem + '"]').prop('disabled', false);
                                }
                            });

                        }
                    });


                    thisField.find('.secondary_list').click(function () {

                        var idCurrent = $(this).data('id');
                        if ($(this).is(':checked')) {
                            //add hidden field with this value
                            var nameInput = thisField.find('.hidden_fields_secondary').data('name');
                            var inputToAdd = $('<input type="hidden" class="secondary_hidden" name="' + nameInput + '[]" value="' + idCurrent + '">');

                            thisField.find('.hidden_fields_secondary').append(inputToAdd);

                        } else {
                            //remove hidden field with this value
                            thisField.find('input.secondary_hidden[value="' + idCurrent + '"]').remove();
                        }
                    });

                });
            });
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
