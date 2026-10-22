<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $l = config('bgremover.limits');

        return [
            'image' => ['required', 'file', 'max:'.$l['max_file_kb'], 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp'],
        ];
    }

    public function messages(): array
    {
        $mb = round(config('bgremover.limits.max_file_kb') / 1024);

        return [
            'image.required' => 'Please choose an image to upload.',
            'image.max' => "That file is too large. The maximum size is {$mb} MB.",
            'image.mimes' => 'Unsupported file type. Please upload a JPG, PNG or WebP image.',
            'image.mimetypes' => 'Unsupported file type. Please upload a JPG, PNG or WebP image.',
            'image.uploaded' => 'The upload failed. The file may be too large or the connection dropped.',
        ];
    }

    /** Verify the file is a real, decodable image within dimension limits (checked from the header, before any full decode). */
    public function after(): array
    {
        return [function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $l = config('bgremover.limits');
            $info = @getimagesize($this->file('image')->getRealPath());

            if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
                $v->errors()->add('image', 'This file is not a valid image.');
            } elseif ($info[0] < $l['min_dimension'] || $info[1] < $l['min_dimension']) {
                $v->errors()->add('image', 'The image is too small to process.');
            } elseif ($info[0] > $l['max_dimension'] || $info[1] > $l['max_dimension'] || $info[0] * $info[1] > $l['max_pixels']) {
                $v->errors()->add('image', "The image dimensions are too large (max {$l['max_dimension']}px per side, ".round($l['max_pixels'] / 1e6).' megapixels).');
            }
        }];
    }
}
