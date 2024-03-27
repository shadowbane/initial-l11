@php
    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitUploadElement';
    $field['wrapper']['data-field-name'] = $field['wrapper']['data-field-name'] ?? $field['name'];
@endphp

{{-- text input --}}
@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>

<div class="backstrap-file">
    <label for="cameraFileInput">
            <span class="btn btn-primary btn-file ">
                <i class="la la-camera"></i> Capture Image
            </span>

        <!-- The hidden file `input` for opening the native camera -->
        <input
            id="cameraFileInput"
            name="{{ $field['name'] }}"
            type="file"
            accept="image/*"
            capture="environment"
        />
    </label>
</div>

<!-- displays the picture uploaded from the native camera -->
{{--    <div class="row">--}}
{{--        <div class="col-12">--}}
{{--        </div>--}}
{{--    </div>--}}

<img id="pictureFromCamera" alt="captured image">

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')

@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    @push('crud_fields_styles')
        <style>
            #cameraFileInput {
                display: none;
            }

            #pictureFromCamera {
                border: black solid 1px;
                width: 50%;
                height: auto;
                margin-top: 16px;
                display: none;
            }

            #pictureInModal {
                border: black solid 1px;
                width: 100%;
                height: auto;
            }
        </style>
    @endpush

    @push('crud_fields_scripts')

        <div class="modal fade" id="{{ $field['name'] }}_modal" data-backdrop="static" data-keyboard="false"
             tabindex="-1"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <img id="pictureInModal" alt="captured image">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- camera script -->
        <script>
            let camInput = document.querySelector("#cameraFileInput"),
                cameraPic = document.querySelector('#pictureFromCamera'),
                modalPic = document.querySelector('#pictureInModal');

            camInput.addEventListener("change", (val) => {
                cameraPic.src = window.URL.createObjectURL(camInput.files[0]);
                modalPic.src = window.URL.createObjectURL(camInput.files[0]);
                cameraPic.style.display = 'block';
            });

            system.ready(() => {
                cameraPic.addEventListener('click', () => {
                    $('#{{ $field['name'] }}_modal').modal('show');
                });
            });
        </script>
    @endpush

@endif
