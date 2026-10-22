<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DeliverySlotController extends Controller
{
    public function index(): View
    {
        $slots = DeliverySlot::query()->orderBy('sort_order')->orderBy('start_time')->get();

        return view('Admin.DeliverySlots.index', compact('slots'));
    }

    public function create(): View
    {
        $nextOrder = (int) DeliverySlot::query()->max('sort_order') + 1;

        return view('Admin.DeliverySlots.form', [
            'slot' => new DeliverySlot([
                'status' => 1,
                'sort_order' => $nextOrder,
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DeliverySlot::query()->create($this->validated($request));

        return redirect()->route('admin.slots.index')->with('success', 'Delivery slot added.');
    }

    public function edit(DeliverySlot $deliverySlot): View
    {
        return view('Admin.DeliverySlots.form', [
            'slot' => $deliverySlot,
        ]);
    }

    public function update(Request $request, DeliverySlot $deliverySlot): RedirectResponse
    {
        $deliverySlot->update($this->validated($request, $deliverySlot));

        return redirect()->route('admin.slots.index')->with('success', 'Delivery slot updated.');
    }

    public function destroy(DeliverySlot $deliverySlot): RedirectResponse
    {
        $deliverySlot->delete();

        return redirect()->route('admin.slots.index')->with('success', 'Delivery slot deleted.');
    }

    public function toggleStatus(DeliverySlot $deliverySlot): RedirectResponse
    {
        $deliverySlot->update(['status' => $deliverySlot->isActive() ? 0 : 1]);

        return back()->with('success', 'Delivery slot status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?DeliverySlot $slot = null): array
    {
        $data = $request->validate([
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'label' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
        ]);

        $start = DeliverySlot::normalizeTime($data['start_time']);
        $end = DeliverySlot::normalizeTime($data['end_time']);

        if ($start < DeliverySlot::WINDOW_START || $end > DeliverySlot::WINDOW_END) {
            throw ValidationException::withMessages([
                'start_time' => 'Slot subah 9:00 se shaam 9:00 ke beech hona chahiye.',
            ]);
        }

        $key = DeliverySlot::makeKey($start, $end);
        $duplicate = DeliverySlot::query()
            ->where('slot_key', $key)
            ->when($slot, fn ($query) => $query->whereKeyNot($slot->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'start_time' => 'Ye time slot pehle se maujood hai.',
            ]);
        }

        if (! $slot && DeliverySlot::query()->count() >= DeliverySlot::MAX_SLOTS) {
            throw ValidationException::withMessages([
                'start_time' => 'Maximum 12 delivery slots. Kisi slot ko edit ya delete karein.',
            ]);
        }

        $label = trim((string) ($data['label'] ?? ''));

        return [
            'slot_key' => $key,
            'label' => $label !== '' ? $label : DeliverySlot::makeLabel($start, $end),
            'start_time' => $start.':00',
            'end_time' => $end.':00',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => (int) $data['status'],
        ];
    }
}
