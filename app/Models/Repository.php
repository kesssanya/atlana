<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Date;

/**
 * @method static Paginator paginate(int $count)
 * @method static Builder where(string $column, string $operator,  $values)
 * @method static Builder whereIn(string $column, array $values)
 * @method static Builder orderBy(string $column, string $direction = 'asc')
 * @method static self find(int $id)
 * @method static self firstOrNew(array $conditions, array $data = [])
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property int $forks
 * @property int $stars
 * @property Date $created_at
 * @property Date $updated_at
 */
class Repository extends Model
{

    protected $fillable = [
        'id'
    ];

    /**
     * Связь с юзером
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

}
