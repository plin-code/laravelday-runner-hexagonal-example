<?php

namespace App\Domain\Runners\Models;

use App\Domain\Contests\Models\Contest;
use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\RunnerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use PlinCode\CustomFields\Concerns\HasCustomFields;

/**
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property CarbonImmutable $birth_date
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'first_name', 'last_name', 'birth_date'])]
class Runner extends Model
{
    use HasCustomFields;

    /** @use HasFactory<RunnerFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    /**
     * The account that owns the runner profile and holds its email address.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The contests the runner is registered to.
     *
     * @return BelongsToMany<Contest, $this>
     */
    public function contests(): BelongsToMany
    {
        return $this->belongsToMany(Contest::class)
            ->withPivot(['registered_at', 'checked_in_at']);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): RunnerFactory
    {
        return RunnerFactory::new();
    }
}
