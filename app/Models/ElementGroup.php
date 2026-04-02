<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ElementGroup extends Pivot
{
    public $incrementing = true;

    protected $table = 'element_group';

    protected function casts(): array
    {
        return [
            'consumer_can_read_via_api' => 'boolean',
        ];
    }
}
