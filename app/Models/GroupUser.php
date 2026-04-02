<?php

namespace App\Models;

use App\Enums\GroupMembershipRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupUser extends Pivot
{
    public $incrementing = true;

    protected $table = 'group_user';

    protected function casts(): array
    {
        return [
            'role' => GroupMembershipRole::class,
        ];
    }
}
