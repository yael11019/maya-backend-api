<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pet_id' => 'sometimes|exists:pets,id',
            'veterinarian_id' => 'nullable|exists:veterinarians,veterinarianId',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'appointment_date' => 'sometimes|date',
            'veterinarian_name' => 'sometimes|string|max:255',
            'clinic_name' => 'sometimes|string|max:255',
            'clinic_address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'status' => ['sometimes', Rule::in(['pending', 'completed', 'cancelled'])],
            'urgency' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'appointment_type' => ['nullable', Rule::in(['consultation', 'vaccination', 'surgery', 'emergency', 'checkup', 'other'])],
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'next_steps' => 'nullable|string'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'pet_id.exists' => 'The selected pet does not exist.',
            'status.in' => 'Please select a valid status.',
            'urgency.in' => 'Please select a valid urgency level.',
            'cost.numeric' => 'The cost must be a valid number.',
            'cost.min' => 'The cost cannot be negative.'
        ];
    }
}
