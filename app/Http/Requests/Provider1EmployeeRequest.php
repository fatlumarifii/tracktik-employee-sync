<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Provider1EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emp_id' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email_address' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'dept' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'employment_status' => ['nullable', 'string', 'in:active,inactive,terminated'],
        ];
    }
}
