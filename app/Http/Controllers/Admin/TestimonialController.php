<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::query()->orderByDesc('id')->get();

        return view('Admin.Testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        return view('Admin.Testimonials.form', [
            'testimonial' => new Testimonial(['status' => 1, 'star_rating' => 5]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Testimonial::query()->create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial added successfully.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('Admin.Testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    public function toggleStatus(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['status' => $testimonial->isActive() ? 0 : 1]);

        return back()->with('success', 'Testimonial status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'star_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'short_description' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'in:0,1'],
        ]);

        $data['star_rating'] = (int) $data['star_rating'];
        $data['status'] = (int) $data['status'];

        return $data;
    }
}
