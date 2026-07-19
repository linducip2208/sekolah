<?php

namespace App\Http\Requests\PPDB;

use Illuminate\Foundation\Http\FormRequest;

class PpdbRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ppdb_period_id'  => 'required|integer',
            'jalur'           => 'required|in:zonasi,prestasi,afirmasi,undian,reguler',
            'student_name'    => 'required|string|max:200',
            'nisn'            => 'nullable|regex:/^\d{10}$/',
            'date_of_birth'   => 'required|date|before:today',
            'gender'          => 'required|in:male,female',
            'address'         => 'required|string|max:500',
            'district'        => 'required|string|max:100',
            'city'            => 'required|string|max:100',
            'home_lat'        => 'nullable|numeric|between:-90,90',
            'home_lng'        => 'nullable|numeric|between:-180,180',
            'previous_school' => 'nullable|string|max:200',
            'parent_name'     => 'required|string|max:200',
            'parent_phone'    => 'required|string|max:30',
            'parent_email'    => 'required|email|max:200',
            'documents'       => 'nullable|array',
            'achievements'    => 'nullable|array',
            'average_score'   => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nisn.regex' => 'NISN harus 10 digit angka.',
        ];
    }
}
