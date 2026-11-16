<?php

namespace App\Application\DTOs;

use Carbon\CarbonImmutable;
use PlinCode\LaravelEmailFixer\Facades\EmailFixer;
use PlinCode\LaravelNameFixer\Facades\NameFixer;

final readonly class RegistrationData
{
    private const string LOCALE = 'it';

    public function __construct(
        public string $firstName,
        public string $lastName,
        public CarbonImmutable $birthDate,
        public string $email,
    ) {}

    /**
     * Build the registration from validated input, repairing the names and the email address.
     *
     * @param  array{first_name: string, last_name: string, birth_date: string, email: string}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $nameFixer = NameFixer::locale(self::LOCALE);

        return new self(
            firstName: $nameFixer->fix($validated['first_name']),
            lastName: $nameFixer->fix($validated['last_name']),
            birthDate: CarbonImmutable::parse($validated['birth_date'])->startOfDay(),
            email: EmailFixer::locale(self::LOCALE)->fix($validated['email']),
        );
    }

    /**
     * The name stored on the user account.
     */
    public function fullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }
}
