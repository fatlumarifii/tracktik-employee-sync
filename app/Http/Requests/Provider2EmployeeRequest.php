<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Provider2EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_number' => ['required', 'string', 'max:255'],
            'personal_info' => ['required', 'array'],
            'personal_info.given_name' => ['required', 'string', 'max:255'],
            'personal_info.family_name' => ['required', 'string', 'max:255'],
            'personal_info.email' => ['required', 'email', 'max:255'],
            'personal_info.mobile' => ['nullable', 'string', 'max:50'],
            'work_info' => ['required', 'array'],
            'work_info.role' => ['nullable', 'string', 'max:255'],
            'work_info.division' => ['nullable', 'string', 'max:255'],
            'work_info.start_date' => ['nullable', 'date'],
            'work_info.current_status' => ['nullable', 'string', 'in:employed,terminated,on_leave'],
        ];
    }
}
