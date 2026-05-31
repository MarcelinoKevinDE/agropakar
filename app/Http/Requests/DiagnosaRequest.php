<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DiagnosaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_user' => ['nullable', 'string', 'max:100'],

            // 'required' ensures the key exists in the POST body.
            // 'array'    ensures it is an array (not a scalar).
            // 'min:1'    ensures at least one checkbox was checked.
            'gejala'    => ['required', 'array', 'min:1'],

            // Each submitted ID must exist in the gejala table.
            // This prevents spoofed IDs from reaching the CF engine.
            'gejala.*'  => ['required', 'integer', 'exists:gejala,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'gejala.required' => 'Pilih minimal satu gejala sebelum menjalankan diagnosis.',
            'gejala.min'      => 'Pilih minimal :min gejala untuk melanjutkan.',
            'gejala.*.exists' => 'Salah satu gejala yang dipilih tidak valid.',
        ];
    }

    // Redirect back with errors for standard form submissions.
    // Return JSON 422 for AJAX / fetch requests.
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], 422)
            );
        }

        throw new HttpResponseException(
            redirect()->back()->withErrors($validator)->withInput()
        );
    }
}