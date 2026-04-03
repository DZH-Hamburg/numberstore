<?php

namespace App\Models;

use App\Enums\GroupMembershipRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'created_by'])]
class Group extends Model
{
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(GroupUser::class)
            ->withPivot(['id', 'role'])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function elements(): BelongsToMany
    {
        return $this->belongsToMany(Element::class)
            ->using(ElementGroup::class)
            ->withPivot(['id', 'consumer_can_read_via_api'])
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        static::creating(function (Group $group): void {
            if ($group->slug !== null && $group->slug !== '') {
                return;
            }
            $group->slug = static::uniqueSlugForName($group->name);
        });

        static::updating(function (Group $group): void {
            if (! $group->isDirty('name')) {
                return;
            }
            $group->slug = static::uniqueSlugForName($group->name, $group->getKey());
        });
    }

    public static function uniqueSlugForName(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 0;
        while (static::query()
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    /**
     * Speichert die Gruppe in einer kurzen Transaktion. Bei parallelen Anfragen kann die
     * eindeutige Slug-Prüfung trotzdem kollidieren; dann wird der Slug erneut vergeben (Retry).
     *
     * @param  array<string, mixed>  $options
     */
    public function save(array $options = []): bool
    {
        return DB::transaction(function () use ($options): bool {
            for ($attempt = 0; $attempt < 25; $attempt++) {
                try {
                    return parent::save($options);
                } catch (\Throwable $e) {
                    if (! static::isUniqueSlugConstraintViolation($e)) {
                        throw $e;
                    }
                    $this->slug = static::uniqueSlugForName($this->name, $this->exists ? $this->getKey() : null);
                }
            }

            throw new \RuntimeException('Unique slug could not be assigned after retries.');
        });
    }

    private static function isUniqueSlugConstraintViolation(\Throwable $e): bool
    {
        return is_a($e, 'Illuminate\\Database\\'.'UniqueConstraintViolationException', true) // pragma: allowlist secret
            && str_contains(strtolower($e->getMessage()), 'slug');
    }

    public function userHasRole(User $user, GroupMembershipRole $role): bool
    {
        return $this->users()
            ->whereKey($user->getKey())
            ->wherePivot('role', $role->value)
            ->exists();
    }

    public function userIsGroupCreator(User $user): bool
    {
        return $this->userHasRole($user, GroupMembershipRole::GroupCreator);
    }

    public function userIsMember(User $user): bool
    {
        return $this->users()->whereKey($user->getKey())->exists();
    }
}
