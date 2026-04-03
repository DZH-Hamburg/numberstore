<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreElementRequest;
use App\Http\Requests\UpdateElementRequest;
use App\Models\Element;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElementController extends Controller
{
    public function index(Request $request, Group $group): JsonResponse
    {
        $this->authorize('view', $group);

        $user = $request->user();
        $query = $group->elements()->withPivot('consumer_can_read_via_api');

        if (! $user->isPlatformAdmin() && ! $user->isGroupCreatorIn($group)) {
            $query->wherePivot('consumer_can_read_via_api', true);
        }

        return response()->json($query->get());
    }

    public function show(Request $request, Group $group, Element $element): JsonResponse
    {
        $this->authorize('view', $group);
        abort_unless($element->groups()->whereKey($group->id)->exists(), 404);
        $this->authorize('viewViaApi', [$element, $group]);

        return response()->json($element->load(['groups' => fn ($q) => $q->whereKey($group->id)]));
    }

    public function store(StoreElementRequest $request, Group $group): JsonResponse
    {
        $data = $request->validated();
        $secrets = $this->extractSecrets($data);

        $element = Element::query()->create([
            'type' => $data['type'],
            'name' => $data['name'],
            'config' => $data['config'] ?? [],
            'encrypted_credentials' => empty($secrets) ? null : json_encode($secrets, JSON_THROW_ON_ERROR),
            'created_by' => $request->user()->id,
        ]);
        $element->groups()->attach($group->id, [
            'consumer_can_read_via_api' => (bool) ($data['consumer_can_read_via_api'] ?? false),
        ]);

        return response()->json($element->fresh(), 201);
    }

    public function update(UpdateElementRequest $request, Group $group, Element $element): JsonResponse
    {
        abort_unless($element->groups()->whereKey($group->id)->exists(), 404);

        $data = $request->validated();
        if (array_key_exists('name', $data)) {
            $element->name = $data['name'];
        }
        if (array_key_exists('type', $data)) {
            $element->type = $data['type'];
        }
        if (array_key_exists('config', $data)) {
            $element->config = $data['config'] ?? [];
        }

        if (array_key_exists('secrets', $data)) {
            $incoming = $this->extractSecrets($data);
            $element->encrypted_credentials = $this->mergeSecrets($element->encrypted_credentials, $incoming);
        }
        $element->save();

        if ($request->has('consumer_can_read_via_api') && $request->canSetConsumerFlag()) {
            $element->groups()->updateExistingPivot($group->id, [
                'consumer_can_read_via_api' => $request->boolean('consumer_can_read_via_api'),
            ]);
        }

        return response()->json($element->fresh());
    }

    public function destroy(Request $request, Group $group, Element $element): JsonResponse
    {
        $this->authorize('delete', $element);
        abort_unless($element->groups()->whereKey($group->id)->exists(), 404);
        $element->groups()->detach($group->id);
        if ($element->groups()->count() === 0) {
            $element->delete();
        }

        return response()->json(null, 204);
    }

    /**
     * @param  array<string,mixed>  $validated
     * @return array{username?:string,password?:string,totp_secret?:string}
     */
    private function extractSecrets(array $validated): array
    {
        $raw = $validated['secrets'] ?? null;
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach (['username', 'password', 'totp_secret'] as $key) {
            $v = $raw[$key] ?? null;
            if (is_string($v) && trim($v) !== '') {
                $out[$key] = $v;
            }
        }

        return $out;
    }

    private function mergeSecrets(mixed $currentEncrypted, array $incoming): ?string
    {
        $current = [];
        if (is_string($currentEncrypted) && $currentEncrypted !== '') {
            try {
                $decoded = json_decode($currentEncrypted, true, flags: JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $current = $decoded;
                }
            } catch (\Throwable) {
                $current = [];
            }
        }

        $merged = array_merge($current, $incoming);

        return empty($merged) ? null : json_encode($merged, JSON_THROW_ON_ERROR);
    }
}
