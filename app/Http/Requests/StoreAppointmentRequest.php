<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
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
            'pet_id' => 'required|exists:pets,id',
            'veterinarian_id' => 'nullable|exists:veterinarians,veterinarianId',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'appointment_date' => 'required|date|after:now',
            'veterinarian_name' => 'required|string|max:255',
            'clinic_name' => 'required|string|max:255',
            'clinic_address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'urgency' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'appointment_type' => ['nullable', Rule::in(['consultation', 'vaccination', 'surgery', 'emergency', 'checkup', 'other'])],
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'pet_id.required' => 'Please select a pet for this appointment.',
            'pet_id.exists' => 'The selected pet does not exist.',
            'title.required' => 'Please provide a title for the appointment.',
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_date.after' => 'The appointment date must be in the future.',
            'veterinarian_name.required' => 'Please provide the veterinarian\'s name.',
            'clinic_name.required' => 'Please provide the clinic name.',
            'urgency.in' => 'Please select a valid urgency level.',
            'cost.numeric' => 'The cost must be a valid number.',
            'cost.min' => 'The cost cannot be negative.'
        ];
    }
}
