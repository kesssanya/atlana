<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

/**
 * @method static Paginator paginate(int $count)
 * @method static Builder whereIn(string $column, array $values)
 * @method static Builder orderBy(string $column, string $direction = 'asc')
 * @method static self find(int $id)
 * @method static self findOrFail(int $id)
 * @method static self firstOrNew(array $conditions, array $data = [])
 * @property int $id
 * @property string $login
 * @property string $name
 * @property string $email
 * @property string $location
 * @property string $bio
 * @property string $avatar
 * @property int $popularity
 * @property int $followers
 * @property int $following
 * @property int $repositories_count
 * @property Date $created_at
 * @property Date $updated_at
 */
class User extends Model
{

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
    ];

    protected function avatar(): Attribute
    {
        return Attribute::make(
            fn($path) => $path ? asset($path) : null
        );
    }

    /**
     * Инкрементация популярности
     *
     * @return void
     */
    public function incrementPopularity(): void
    {
        $this->popularity++;
    }

    /**
     * связь с репозиториями
     *
     * @return HasMany
     */
    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }
}
