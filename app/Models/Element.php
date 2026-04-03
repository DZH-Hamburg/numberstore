<?php

namespace App\Models;

use App\Enums\ElementType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Fillable(['type', 'name', 'config', 'encrypted_credentials', 'created_by'])]
class Element extends Model
{
    protected $hidden = [
        'encrypted_credentials',
        'last_screenshot_disk',
        'last_screenshot_path',
    ];

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

    public function hasStoredScreenshot(): bool
    {
        if ($this->type !== ElementType::Screenshot) {
            return false;
        }
        $path = $this->last_screenshot_path;
        if (! is_string($path) || $path === '') {
            return false;
        }
        $disk = $this->last_screenshot_disk ?: (string) config('filesystems.default');

        return Storage::disk($disk)->exists($path);
    }

    public function lastScreenshotStreamResponse(?string $downloadName = null, bool $asAttachment = false): StreamedResponse|BinaryFileResponse|null
    {
        if (! $this->hasStoredScreenshot()) {
            return null;
        }
        $disk = $this->last_screenshot_disk ?: (string) config('filesystems.default');
        $path = $this->last_screenshot_path;
        if (! is_string($path) || $path === '') {
            return null;
        }

        $name = $downloadName ?? 'screenshot.png';

        if ($asAttachment) {
            return Storage::disk($disk)->download($path, $name);
        }

        return Storage::disk($disk)->response($path, $name, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    protected function casts(): array
    {
        return [
            'type' => ElementType::class,
            'config' => 'array',
            'encrypted_credentials' => 'encrypted',
            'last_screenshot_at' => 'datetime',
        ];
    }
}
