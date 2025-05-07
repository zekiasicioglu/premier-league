<?php

namespace App\Http\Requests;

use App\Models\Team;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateFixtureRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'strategy' => ['nullable', 'in:round-robin,swiss'],
            'teams' => ['required', 'array', 'size:4'],
            'teams.*' => ['required', 'distinct', 'exists:teams,id', 'integer']
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $teamIds = $this->input('teams', []);

            if (count($teamIds) === 4) {
                $cities = Team::whereIn('id', $teamIds)->pluck('city')->all();
                $uniqueCities = array_unique($cities);

                if (count($uniqueCities) !== 4) {
                    $validator->errors()->add('teams', 'Each selected team must be from a different city.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'teams.size' => 'Exactly 4 teams must be selected.',
            'teams.*.distinct' => 'Team selections must be unique.',
            'teams.*.exists' => 'One or more selected teams do not exist.',
        ];
    }
}
