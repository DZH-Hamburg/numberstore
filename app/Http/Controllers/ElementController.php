<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreElementRequest;
use App\Http\Requests\UpdateElementRequest;
use App\Models\Element;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ElementController extends Controller
{
    public function create(Group $group): View
    {
        $this->authorize('create', [Element::class, $group]);

        return view('elements.create', ['group' => $group]);
    }

    public function store(StoreElementRequest $request, Group $group): RedirectResponse
    {
        $data = $request->validated();
        $element = Element::query()->create([
            'type' => $data['type'],
            'name' => $data['name'],
            'config' => $data['config'] ?? [],
            'created_by' => $request->user()->id,
        ]);
        $element->groups()->attach($group->id, [
            'consumer_can_read_via_api' => (bool) ($data['consumer_can_read_via_api'] ?? false),
        ]);

        return redirect()->route('groups.show', $group)->with('status', __('Element angelegt.'));
    }

    public function edit(Group $group, Element $element): View
    {
        $this->authorize('update', $element);
        $elementInGroup = $group->elements()->whereKey($element->getKey())->firstOrFail();

        return view('elements.edit', ['group' => $group, 'element' => $elementInGroup]);
    }

    public function update(UpdateElementRequest $request, Group $group, Element $element): RedirectResponse
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
        $element->save();

        if ($request->has('consumer_can_read_via_api') && $request->canSetConsumerFlag()) {
            $element->groups()->updateExistingPivot($group->id, [
                'consumer_can_read_via_api' => $request->boolean('consumer_can_read_via_api'),
            ]);
        }

        return redirect()->route('groups.show', $group)->with('status', __('Element gespeichert.'));
    }

    public function destroy(Group $group, Element $element): RedirectResponse
    {
        $this->authorize('delete', $element);
        abort_unless($element->groups()->whereKey($group->id)->exists(), 404);
        $element->groups()->detach($group->id);
        if ($element->groups()->count() === 0) {
            $element->delete();
        }

        return redirect()->route('groups.show', $group)->with('status', __('Element entfernt.'));
    }
}
