<?php

namespace App\Domain\Contests\Models;

use App\Domain\Runners\Models\Runner;
use Carbon\CarbonImmutable;
use Database\Factories\ContestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property CarbonImmutable $date
 * @property int $available_places
 * @property bool $registrations_open
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property int|null $runners_count
 */
#[Fillable(['name', 'date', 'available_places', 'registrations_open'])]
class Contest extends Model
{
    /** @use HasFactory<ContestFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'available_places' => 'integer',
            'registrations_open' => 'boolean',
        ];
    }

    /**
     * The runners registered to the contest.
     *
     * @return BelongsToMany<Runner, $this>
     */
    public function runners(): BelongsToMany
    {
        return $this->belongsToMany(Runner::class)
            ->withPivot(['registered_at', 'checked_in_at']);
    }

    /**
     * Limit the query to the contests not held yet, the ones of the given day included.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeUpcoming(Builder $query, CarbonImmutable $today): void
    {
        $query->whereDate('date', '>=', $today->toDateString());
    }

    /**
     * Tell whether the contest took place before the given day. On the day of the race it has not.
     */
    public function hasBeenHeld(CarbonImmutable $today): bool
    {
        return $this->date->startOfDay()->lessThan($today->startOfDay());
    }

    /**
     * Places still free, given the number of runners already registered.
     */
    public function placesLeft(int $registeredRunners): int
    {
        return max(0, $this->available_places - $registeredRunners);
    }

    /**
     * Tell whether a runner born on the given day is of age (18) by the day of the contest, that day included.
     */
    public function admitsRunnerBornOn(CarbonImmutable $birthDate): bool
    {
        return $birthDate->startOfDay()->addYears(18)->lessThanOrEqualTo($this->date->startOfDay());
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ContestFactory
    {
        return ContestFactory::new();
    }
}
