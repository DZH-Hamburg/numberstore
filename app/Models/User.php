<?php

namespace App\Models;

use App\Enums\GroupMembershipRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'is_platform_admin', 'can_create_groups'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->using(GroupUser::class)
            ->withPivot(['id', 'role'])
            ->withTimestamps();
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function isGlobalGroupCreator(): bool
    {
        return (bool) $this->can_create_groups;
    }

    public function canCreatePlatformGroups(): bool
    {
        return $this->isPlatformAdmin() || $this->isGlobalGroupCreator();
    }

    public function membershipRoleFor(Group $group): ?GroupMembershipRole
    {
        $pivot = $this->groups()->where('groups.id', $group->id)->first()?->pivot;

        return $pivot?->role instanceof GroupMembershipRole
            ? $pivot->role
            : GroupMembershipRole::tryFrom((string) $pivot?->role);
    }

    public function isGroupCreatorIn(Group $group): bool
    {
        return $this->membershipRoleFor($group) === GroupMembershipRole::GroupCreator;
    }

    public function isMemberOf(Group $group): bool
    {
        return $this->groups()->where('groups.id', $group->id)->exists();
    }

    public function gravatarUrl(int $size = 160): string
    {
        $hash = md5(strtolower(trim($this->email)));

        return 'https://www.gravatar.com/avatar/'.$hash.'?s='.$size.'&d=mp';
    }

    public function avatarUrl(int $size = 160): string
    {
        if ($this->avatar_path !== null && $this->avatar_path !== '') {
            return Storage::disk('public')->url($this->avatar_path);
        }

        return $this->gravatarUrl($size);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
            'can_create_groups' => 'boolean',
        ];
    }
}
