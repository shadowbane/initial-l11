@php
    $field['prefix'] = $field['prefix'] ?? '';
    $field['disk'] = $field['disk'] ?? null;
    $value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';

    $value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';
    $image_url = $value
        ? preg_match('/^data\:image\//', $value)
            ? $value
            : (isset($field['disk'])
                ? ( isset($field['temporary']) ? \Storage::disk($field['disk'])->temporaryUrl(array_get($field, 'prefix', '').$field['value'], Carbon\Carbon::now()->addMinutes($field['temporary'])) : Storage::disk($field['disk'])->url($prefix.$value))
                : url($field['prefix'].$value))
        :''; // if validation failed, tha value will be base64, so no need to create a URL for it

    if (! function_exists('getDiskUrl')) {
        function getDiskUrl($disk, $path, $temporary = null) {
            try {
                // make sure the value don't have disk base path on it, this is the same as `Storage::disk($disk)->url($prefix);`,
                // we need this solution to deal with `S3` not supporting getting empty urls
                // that could happen when there is no $prefix set.
                $origin = substr(Storage::disk($disk)->url('/'), 0, -1);
                $path = str_replace($origin, '', $path);

                if (isset($temporary)) {
                    return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes($temporary));
                }

                return Storage::disk($disk)->url($path);
            }
            catch (Exception $e) {
                // the driver does not support retrieving URLs (eg. SFTP)
                return url($path);
            }
        }
    }

    if (! function_exists('maximumServerUploadSizeInBytes')) {
        function maximumServerUploadSizeInBytes() {

            $val = trim(ini_get('upload_max_filesize'));
            $last = strtolower($val[strlen($val)-1]);

            switch($last) {
                // The 'G' modifier is available since PHP 5.1.0
                case 'g':
                    $val = (int)$val * 1073741824;
                    break;
                case 'm':
                    $val = (int)$val * 1048576;
                    break;
                case 'k':
                    $val = (int)$val * 1024;
                    break;
            }

            return $val;
        }
    }

    // if value isn't a base 64 image, generate URL
    if($value && !preg_match('/^data\:image\//', $value)) {
        // make sure to append prefix once to value
        $value = Str::start($value, $field['prefix']);

        // generate URL
        $value = $field['disk']
            ? getDiskUrl($field['disk'], $value, $field['temporary'] ?? null)
            : url($value);
    }

    $max_image_size_in_bytes = $field['max_file_size'] ?? (int)maximumServerUploadSizeInBytes();

    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['class'] = $field['wrapper']['class'] ?? "form-group col-sm-12 image";
    $field['wrapper']['class'] = $field['wrapper']['class'].' cropperImage';
    $field['wrapper']['data-aspectRatio'] = $field['aspect_ratio'] ?? 0;
    $field['wrapper']['data-crop'] = $field['crop'] ?? false;
    $field['wrapper']['data-field-name'] = $field['wrapper']['data-field-name'] ?? $field['name'];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitCropperImageElement';
@endphp

@include('crud::fields.inc.wrapper_start')
<div>
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
</div>

<label for="cameraFileInput">
    {{--    <button type="button" class="btn btn-primary btn-file" data-toggle="modal" data-target="#myModal">--}}
    {{--        <i class="la la-camera"> Capture Image</i>--}}
    {{--    </button>--}}

    <span class="btn btn-primary btn-file">
        <i class="la la-camera"></i> Capture Image
    </span>

    <!-- The hidden file `input` for opening the native camera -->
    <input
        id="cameraFileInput"
        type="file"
        accept="image/*"
        capture="environment"
    />
</label>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Foto</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Wrap the image or canvas element with a block element (container) -->
                <div class="row">
                    <!-- Stream video via webcam -->
                    {{--                    <div id="videoCam" class="video-wrap col-sm-12">--}}
                    {{--                        <span id="aspectRatio"></span>--}}
                    {{--                        <video id="video" playsinline autoplay width="144" height="192"></video>--}}
                    {{--                        <input class="btn btn-primary" style="margin-left: 5px; margin-bottom: 20px;" data-handle="snap"--}}
                    {{--                               type="button" value="capture"/>--}}
                    {{--                    </div>--}}

                    <!-- Webcam video snapshot -->
                    <canvas id="canvas" width="144" height="192" style="display: none"></canvas>

                    <div class="col-sm-6" style="margin-bottom: 20px; margin-right: 20px;">
                        <img data-handle="mainImage" src="">
                    </div>
                    @if(isset($field['crop']) && $field['crop'])
                        <div class="col-sm-3">
                            <div class="docs-preview clearfix">
                                <div id="{{ $field['name'] }}" class="img-preview preview-lg">
                                    <img src=""
                                         style="display: block; min-width: 0px !important; min-height: 0px !important; max-width: none !important; max-height: none !important; margin-left: -32.875px; margin-top: -18.4922px; transform: none;">
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="btn-group">
                    @if(isset($field['crop']) && $field['crop'])
                        <button class="btn btn-light btn-sm" data-handle="rotateLeft" type="button"
                                style="display: none;"><i class="la la-rotate-left"></i></button>
                        <button class="btn btn-light btn-sm" data-handle="rotateRight" type="button"
                                style="display: none;"><i class="la la-rotate-right"></i></button>
                        <button class="btn btn-light btn-sm" data-handle="zoomIn" type="button" style="display: none;">
                            <i class="la la-search-plus"></i></button>
                        <button class="btn btn-light btn-sm" data-handle="zoomOut" type="button" style="display: none;">
                            <i class="la la-search-minus"></i></button>
                        <button class="btn btn-light btn-sm" data-handle="reset" type="button" style="display: none;"><i
                                class="la la-times"></i></button>
                    @endif
                    <button class="btn btn-light btn-sm" data-handle="remove" type="button"><i class="la la-trash"></i>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Upload</button>
            </div>
        </div>
    </div>
</div>
{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <link href="{{ asset('packages/cropperjs/dist/cropper.min.css') }}" rel="stylesheet" type="text/css"/>
        <style>
            #cameraFileInput {
                display: none;
            }

            .hide {
                display: none;
            }

            .image .btn-group {
                margin-top: 10px;
            }

            img {
                max-width: 100%; /* This rule is very important, please do not ignore this! */
            }

            .modal-backdrop {
                z-index: -1;
            }

            .img-container, .img-preview {
                width: 100%;
                text-align: center;
            }

            .img-preview {
                float: left;
                margin-right: 10px;
                margin-bottom: 10px;
                overflow: hidden;
            }

            .preview-lg {
                width: 144px;
                height: 192px;
            }

            .btn-file {
                position: relative;
                overflow: hidden;
            }

            .btn-file input[type=file] {
                position: absolute;
                top: 0;
                right: 0;
                min-width: 100%;
                min-height: 100%;
                font-size: 100px;
                text-align: right;
                filter: alpha(opacity=0);
                opacity: 0;
                outline: none;
                background: white;
                cursor: inherit;
                display: block;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script src="{{ asset('packages/cropperjs/dist/cropper.min.js') }}"></script>
        <script src="{{ asset('packages/jquery-cropper/dist/jquery-cropper.min.js') }}"></script>
        <script>
            // const video = document.getElementById('video');
            //
            // let constraints = {
            //     video: {
            //         height: {
            //             min: 192,
            //             ideal: 472,
            //             max: 960,
            //         },
            //         width: {
            //             min: 144,
            //             ideal: 354,
            //             max: 720,
            //         },
            //
            //         // width: 144,
            //         // height: 192,
            //
            //         // width: 192,
            //         // height: 144,
            //
            //         facingMode: "environment",
            //     },
            // };
            // let environmentCamInitialized = false;

            // Access webcam
            // async function init() {
            //     const stream = await navigator.mediaDevices.getUserMedia(constraints);
            //
            //     if (stream.getVideoTracks()[0].getSettings().facingMode === 'environment' && environmentCamInitialized === false) {
            //         constraints = {
            //             video: {
            //                 width: {
            //                     min: 192,
            //                     ideal: 472,
            //                     max: 960,
            //                 },
            //                 height: {
            //                     min: 144,
            //                     ideal: 354,
            //                     max: 720,
            //                 },
            //
            //                 // width: 144,
            //                 // height: 192,
            //
            //                 // width: 192,
            //                 // height: 144,
            //
            //                 facingMode: "environment",
            //             },
            //         };
            //
            //         // constraints.video.width = stream.getVideoTracks()[0].getSettings().height;
            //         // constraints.video.height = stream.getVideoTracks()[0].getSettings().width;
            //
            //         stream.getTracks().forEach(function(track) {
            //             track.stop();
            //         });
            //
            //         environmentCamInitialized = true;
            //
            //         init();
            //
            //         return;
            //     }
            //
            //     handleSuccess(stream);
            // }

            // Success
            // function handleSuccess(stream) {
            //     window.stream = stream;
            //     video.srcObject = stream;
            //
            //     $('#aspectRatio').html(JSON.stringify(stream.getVideoTracks()[0].getSettings()))
            // }

            // Load init
            // init();

            function bpFieldInitCropperImageElement(element) {
                // Find DOM elements under this form-group element
                var $mainImage = element.find('[data-handle=mainImage]');
                var $uploadImage = element.find("[data-handle=uploadImage]");
                var $hiddenImage = element.find("[data-handle=hiddenImage]");
                var $rotateLeft = element.find("[data-handle=rotateLeft]");
                var $rotateRight = element.find("[data-handle=rotateRight]");
                var $zoomIn = element.find("[data-handle=zoomIn]");
                var $zoomOut = element.find("[data-handle=zoomOut]");
                var $reset = element.find("[data-handle=reset]");
                var $remove = element.find("[data-handle=remove]");
                var $previews = element.find("[data-handle=previewArea]");
                var $snap = element.find("[data-handle=snap]");
                // Options either global for all image type fields, or use 'data-*' elements for options passed in via the CRUD controller
                var options = {
                    viewMode: 2,
                    checkOrientation: false,
                    autoCropArea: 1,
                    responsive: true,
                    preview: element.find('.img-preview'),
                    aspectRatio: element.attr('data-aspectRatio')
                };
                var crop = element.attr('data-crop');

                // Hide 'Remove' button if there is no image saved
                if (!$hiddenImage.val()) {
                    $previews.hide();
                    $remove.hide();
                }
                // Make the main image show the image in the hidden input
                $mainImage.attr('src', $hiddenImage.val());

                // Only initialize cropper plugin if crop is set to true
                if (crop) {
                    $remove.click(function () {
                        $mainImage.cropper('destroy');
                        $mainImage.attr('src', '');
                        $hiddenImage.val('');
                        $rotateLeft.hide();
                        $rotateRight.hide();
                        $zoomIn.hide();
                        $zoomOut.hide();
                        $reset.hide();
                        $remove.hide();
                        $previews.hide();
                        $snap.show();
                    });
                } else {
                    $remove.click(function () {
                        $mainImage.attr('src', '');
                        $hiddenImage.val('');
                        $remove.hide();
                        $previews.hide();
                    });
                }

                $snap.click(function () {
                    let context = canvas.getContext('2d');
                    context.drawImage(video, 0, 0, 144, 192);

                    let canvasImage = document.getElementById('canvas').toDataURL('image/png');
                    $mainImage.attr("src", canvasImage);

                    $uploadImage.val("");
                    if (crop) {
                        $mainImage.cropper(options).cropper("reset", true).cropper("replace", this.result);
                        window.img = $mainImage[0];

                        // Override form submit to copy canvas to hidden input before submitting
                        $('form').submit(function () {
                            var imageURL = $mainImage.cropper('getCroppedCanvas').toDataURL(canvasImage);
                            $hiddenImage.val(imageURL);
                            return true; // return false to cancel form action
                        });
                        $rotateLeft.click(function () {
                            $mainImage.cropper("rotate", 90);
                        });
                        $rotateRight.click(function () {
                            $mainImage.cropper("rotate", -90);
                        });
                        $zoomIn.click(function () {
                            $mainImage.cropper("zoom", 0.1);
                        });
                        $zoomOut.click(function () {
                            $mainImage.cropper("zoom", -0.1);
                        });
                        $reset.click(function () {
                            $mainImage.cropper("reset");
                        });
                        $rotateLeft.show();
                        $rotateRight.show();
                        $zoomIn.show();
                        $zoomOut.show();
                        $reset.show();
                        $remove.show();
                    } else {
                        $mainImage.attr('src', this.result);
                        $hiddenImage.val(this.result);
                        $remove.show();
                    }
                });

                $uploadImage.change(function () {
                    var fileReader = new FileReader(),
                        files = this.files,
                        file;

                    if (!files.length) {
                        return;
                    }
                    file = files[0];

                    const maxImageSize = {{ $max_image_size_in_bytes }};
                    if (maxImageSize > 0 && file.size > maxImageSize) {

                        alert(`Please pick an image smaller than ${maxImageSize} bytes.`);
                    } else if (/^image\/\w+$/.test(file.type)) {

                        fileReader.readAsDataURL(file);
                        fileReader.onload = function () {

                            $uploadImage.val("");
                            $previews.show();
                            if (crop) {
                                $mainImage.cropper(options).cropper("reset", true).cropper("replace", this.result);
                                // Override form submit to copy canvas to hidden input before submitting
                                // update the hidden input after selecting a new item or cropping
                                $mainImage.on('ready cropstart cropend', function () {
                                    var imageURL = $mainImage.cropper('getCroppedCanvas').toDataURL(file.type);
                                    $hiddenImage.val(imageURL);
                                    return true;
                                });

                                $rotateLeft.show();
                                $rotateRight.show();
                                $zoomIn.show();
                                $zoomOut.show();
                                $reset.show();
                                $remove.show();

                            } else {
                                $mainImage.attr('src', this.result);
                                $hiddenImage.val(this.result);
                                $remove.show();
                            }
                        };
                    } else {
                        new Noty({
                            type: "error",
                            text: "<strong>Please choose an image file</strong><br>The file you've chosen does not look like an image."
                        }).show();
                    }
                });

                //moved the click binds outside change event, or we would register as many click events for the same amout of times
                //we triggered the image change
                if (crop) {
                    $rotateLeft.click(function () {
                        $mainImage.cropper("rotate", 90);
                    });

                    $rotateRight.click(function () {
                        $mainImage.cropper("rotate", -90);
                    });

                    $zoomIn.click(function () {
                        $mainImage.cropper("zoom", 0.1);
                    });

                    $zoomOut.click(function () {
                        $mainImage.cropper("zoom", -0.1);
                    });

                    $reset.click(function () {
                        $mainImage.cropper("reset");
                    });
                }
            }
        </script>

    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
