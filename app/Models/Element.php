<?php

namespace App\Models;

use App\Enums\ElementType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['type', 'name', 'config', 'created_by'])]
class Element extends Model
{
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->using(ElementGroup::class)
            ->withPivot(['id', 'consumer_can_read_via_api'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'type' => ElementType::class,
            'config' => 'array',
        ];
    }
}
