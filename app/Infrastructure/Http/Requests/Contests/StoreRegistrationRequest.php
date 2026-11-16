<?php

namespace App\Infrastructure\Http\Requests\Contests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use PlinCode\LaravelEmailFixer\Rules\SanitizedEmail;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Letters joined by spaces, apostrophes (the typographic one too) or hyphens, plus one
     * trailing dot or comma that the name fixer strips. Anything else is refused because the
     * name is printed in a Markdown mail, where brackets, asterisks or a URL would be rendered.
     */
    private const string NAME_PATTERN = "/^[\\p{L}\\p{M}][\\p{L}\\p{M} '’\\-]*[.,]?$/u";

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100', 'regex:'.self::NAME_PATTERN],
            'last_name' => ['required', 'string', 'max:100', 'regex:'.self::NAME_PATTERN],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before:today'],
            'email' => ['bail', 'required', 'string', 'max:255', new SanitizedEmail(locale: 'it')],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Il campo :attribute è obbligatorio.',
            'string' => 'Il campo :attribute non è valido.',
            'max' => 'Il campo :attribute non può superare :max caratteri.',
            'regex' => 'Il campo :attribute può contenere solo lettere, spazi, apostrofi e trattini.',
            'birth_date.date_format' => 'La data di nascita non è valida.',
            'birth_date.before' => 'La data di nascita deve essere nel passato.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'nome',
            'last_name' => 'cognome',
            'birth_date' => 'data di nascita',
            'email' => 'email',
        ];
    }
}
