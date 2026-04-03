<?php

namespace App\Http\Controllers;

use App\Enums\ElementType;
use App\Jobs\RunScreenshotJob;
use App\Models\Element;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ElementScreenshotController extends Controller
{
    public function show(Request $request, Group $group, Element $element): StreamedResponse|BinaryFileResponse
    {
        $this->authorize('view', $group);
        abort_unless($element->groups()->whereKey($group->id)->exists(), 404);
        abort_unless($element->type === ElementType::Screenshot, 404);
        $response = $element->lastScreenshotStreamResponse(null, $request->boolean('download'));
        abort_if($response === null, 404);

        return $response;
    }

    public function store(Group $group, Element $element): JsonResponse
    {
        $this->authorize('update', $element);
        abort_unless($element->groups()->whereKey($group->id)->exists(), 404);
        abort_unless($element->type === ElementType::Screenshot, 422);

        RunScreenshotJob::dispatch($element->id);

        return response()->json([
            'status' => 'queued',
            'element_id' => $element->id,
            'previous_last_screenshot_at' => $element->last_screenshot_at?->toIso8601String(),
        ], 202);
    }

    public function meta(Group $group, Element $element): JsonResponse
    {
        $this->authorize('view', $group);
        abort_unless($element->groups()->whereKey($group->id)->exists(), 404);

        return response()->json([
            'last_screenshot_at' => $element->last_screenshot_at?->toIso8601String(),
        ]);
    }
}
