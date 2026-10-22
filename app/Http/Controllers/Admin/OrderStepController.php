<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\OrderStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderStepController extends Controller
{
    public function index(): View
    {
        $orderSteps = OrderStep::query()->orderBy('sort_order')->orderBy('id')->get();
        $section = HomeSection::forKey(HomeSection::KEY_ORDER_WAY);

        return view('Admin.OrderSteps.index', compact('orderSteps', 'section'));
    }

    public function create(): View
    {
        return view('Admin.OrderSteps.form', [
            'orderStep' => new OrderStep(['status' => 1, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        OrderStep::query()->create($this->validated($request));

        return redirect()->route('admin.order-steps.index')->with('success', 'Order step added successfully.');
    }

    public function edit(OrderStep $orderStep): View
    {
        return view('Admin.OrderSteps.form', compact('orderStep'));
    }

    public function update(Request $request, OrderStep $orderStep): RedirectResponse
    {
        $orderStep->update($this->validated($request));

        return redirect()->route('admin.order-steps.index')->with('success', 'Order step updated successfully.');
    }

    public function destroy(OrderStep $orderStep): RedirectResponse
    {
        $orderStep->delete();

        return redirect()->route('admin.order-steps.index')->with('success', 'Order step deleted.');
    }

    public function toggleStatus(OrderStep $orderStep): RedirectResponse
    {
        $orderStep->update(['status' => $orderStep->isActive() ? 0 : 1]);

        return back()->with('success', 'Order step status updated.');
    }

    public function updateSection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
        ]);

        HomeSection::query()->updateOrCreate(
            ['section_key' => HomeSection::KEY_ORDER_WAY],
            $data
        );

        return back()->with('success', 'Section heading updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['status'] = (int) $data['status'];

        return $data;
    }
}
