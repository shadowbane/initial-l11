<?php

if (! function_exists('stringEncryption')) {
    /**
     * Encryption.
     */
    function stringEncryption(string $action, string $string): false|string
    {
        $output = false;

        $encrypt_method = 'AES-256-CBC';                                    // Default
        $secret_key = config('system.default.encryption_key');         // Change the key!
        $secret_iv = config('system.default.encryption_iv');           // Change the init vector!

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        return match ($action) {
            'encrypt' => base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv)),
            'decrypt' => openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv),
            default => false,
        };
    }
}

if (! function_exists('mime2ext')) {
    /**
     * @param  mixed  $mime
     *
     * @throws JsonException
     *
     * @return false|int|string
     */
    function mime2ext(mixed $mime): false|int|string
    {
        $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp","image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp","image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp","application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg","image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],"wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],"ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg","video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],"kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],"rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application","application\/x-jar"],"zip":["application\/x-zip","application\/zip","application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],"7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],"svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],"mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],"webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],"pdf":["application\/pdf","application\/octet-stream"],"pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],"ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office","application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],"xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],"xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel","application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],"xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo","video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],"log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],"wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],"tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop","image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],"mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar","application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40","application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],"cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary","application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],"ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],"wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],"dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php","application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],"swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],"mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],"rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],"jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],"eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],"p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],"p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
        $all_mimes = json_decode($all_mimes, true, 512, JSON_THROW_ON_ERROR);
        foreach ($all_mimes as $key => $value) {
            if (in_array($mime, $value, true)) {
                return $key;
            }
        }

        return false;
    }
}

if (! function_exists('formatUrl')) {
    /**
     * Generate and format file URL.
     * Removes double slashes, add current scheme and host if not defined.
     *
     * @param  string  $url
     * @return string
     */
    function formatUrl(string $url): string
    {
        // remove double slash
        $url = preg_replace('/([^:])(\/{2,})/', '$1/', $url);

        // returns, if already starts with http
        if (Str::startsWith($url, 'http')) {
            return $url;
        }

        // if the url had download url, then we only need to add scheme.
        // if not, we need to add scheme and host.
        $storageUrl = config('system.parameters.ms_file_manager_download_url');

        // remove double slash from the beginning of $storageUrl, if exists
        $storageUrl = preg_replace('/(\/{2,})/', '', $storageUrl);

        // remove double slash from the beginning of the URL, if exists
        if (Str::startsWith($url, '//')) {
            $url = Str::replaceFirst('//', '', $url);
        }

        // remove single slash from the beginning of the URL, if exists
        if (Str::startsWith($url, '/')) {
            $url = Str::replaceFirst('/', '', $url);
        }

        // if the url already has storage url, then we only need to add scheme.
        if (Str::contains($url, $storageUrl)) {
            $storageUrl = '';
        } else {
            // append trailing slash if not exists
            $storageUrl = Str::endsWith($storageUrl, '/') ? $storageUrl : $storageUrl.'/';
        }

        return request()->getScheme().'://'.$storageUrl.$url;
    }
}

if (! function_exists('normalizeUrl')) {
    /**
     * Normalize a URL by adding the current scheme (i.e. "http" or "https") to the beginning of the URL.
     * If the current scheme is "https", URLs with the "http" scheme will be converted to the "https" scheme.
     *
     * @param  string  $url
     * @return string
     */
    function normalizeUrl(string $url): string
    {
        // Remove any leading "//" from the URL
        $url = ltrim($url, '//');

        // Check if the URL begins with "http://" or "https://"
        if (! preg_match('/^https?:\/\//', $url)) {
            // If not, add the current scheme (i.e. "http" or "https") to the beginning of the URL
            $url = request()->getScheme().'://'.$url;
        }

        // Return the normalized URL
        return $url;
    }
}

if (! function_exists('jsonObjDecoder')) {
    /**
     * @param  string|null  $json
     *
     * @throws \JsonException
     *
     * @return object|mixed
     */
    function jsonObjDecoder(?string $json): mixed
    {
        if (blank($json)) {
            return null;
        }

        return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }
}

if (! function_exists('generate_password')) {
    /**
     * Generate random password.
     *
     * @param  int  $length
     * @return array
     */
    function generate_password(int $length = 8): array
    {
        $unencrypted = Str::password($length, true, true, false, false);

        return [
            'password' => $unencrypted,
            'encrypted' => Hash::make($unencrypted),
        ];
    }
}

if (! function_exists('num2rom')) {
    /**
     * Numeric to roman numeral.
     *
     * @param  int  $number
     * @return string
     */
    function num2rom(int $number)
    {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;

                    break;
                }
            }
        }

        return $returnValue;
    }
}

if (! function_exists('setPhoneNumber')) {
    /**
     * Set Phone Number.
     *
     * @param  $value
     * @return string|null
     */
    function setPhoneNumber($value)
    {
        if ($value == null) {
            return null;
        }

        // make sure it's integer.
        // will remove leading zero or + sign.
        $value = (int) $value;

        // if user input +62 first
        if (substr($value, 0, 1) == 6) {
            if (substr($value, 0, 2) == 62) {
                $value = substr($value, 2, $value - 2);
            }
        }

        // append 0 before value
        return '0'.$value;
    }
}

if (! function_exists('getMonths')) {
    #[ArrayShape([
        '01' => 'string',
        '02' => 'string',
        '03' => 'string',
        '04' => 'string',
        '05' => 'string',
        '06' => 'string',
        '07' => 'string',
        '08' => 'string',
        '09' => 'string',
        '10' => 'string',
        '11' => 'string',
        '12' => 'string',
    ])]
    function getMonths(): array
    {
        return [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
    }
}

if (! function_exists('formatLetterNumber')) {
    function formatLetterNumber(
        string $format,
        int $number,
        string $name,
        bool $show = false,
        ?string $numbering = null,
        string $type = '',
        string $date = ''
    ): string {
        $formats = explode('/', $format);
        if ($date) {
            $now = Carbon::parse($date);
        } else {
            $now = now();
        }

        foreach ($formats as $key => $val) {
            $k = preg_replace("/(?:(?:\{))|(?:(?:\}))/", '', $val);

            if ($val[0] == '{') {
                if (strtolower($val[1]) == 'n' && $show == true) {
                    $formats[$key] = str_pad($number + 1, strlen($k), 0, STR_PAD_LEFT);
                } else {
                    $formats[$key] = '%';
                }

                if (strtolower($val[1]) == 't') {
                    $numbering == null ? $formats[$key] = $type : $formats[$key] = $type.'-'.$numbering;
                }

                if (strtolower($k) == 'cym') {
                    $formats[$key] = $name.'-'.$now->format('ym');
                } elseif (strtolower($val[1]) == 'c') {
                    $formats[$key] = $name;
                }

                if (strtolower($k) == 'ynnnnn') {
                    $formats[$key] = $now->format('y').str_pad($number + 1, 5, 0, STR_PAD_LEFT);
                } elseif (strtolower($val[1]) == 'y') {
                    $formats[$key] = $now->format($k);
                }

                if (strtolower($k) == 'mnnn') {
                    if ($show == true) {
                        $formats[$key] = $now->format('m').str_pad($number + 1, 3, 0, STR_PAD_LEFT);
                    } else {
                        $formats[$key] = $now->format('m').'%';
                    }
                } elseif (strtolower($val[1]) == 'm') {
                    $formats[$key] = $now->format($k);
                }
            }
        }

        return strtoupper(implode('/', $formats));
    }
}
