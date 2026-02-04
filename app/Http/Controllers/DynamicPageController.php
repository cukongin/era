<?php

namespace App\Http\Controllers;

use App\Models\DynamicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DynamicPageController extends Controller
{
    // Admin: List Pages
    public function index()
    {
        $pages = DynamicPage::orderBy('updated_at', 'desc')->get();
        return view('settings.pages.index', compact('pages'));
    }

    // Admin: Create Form
    public function create()
    {
        return view('settings.pages.create');
    }

    // Admin: Store Page
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'status' => 'required|in:draft,published'
        ]);

        $slug = Str::slug($request->title);
        // Ensure unique slug
        if (DynamicPage::where('slug', $slug)->exists()) {
            $slug .= '-' . time();
        }

        DynamicPage::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'status' => $request->status,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('settings.pages.index')->with('success', 'Halaman berhasil dibuat');
    }

    // Admin: Edit Form
    public function edit(DynamicPage $page)
    {
        return view('settings.pages.edit', compact('page'));
    }

    // Admin: Update Page
    public function update(Request $request, DynamicPage $page)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'status' => 'required|in:draft,published'
        ]);

        // Optional: Update slug if title changes? Usually better to keep slug stable for SEO/Links.
        // Let's keep slug stable unless explicitly requested or emptiness.

        $page->update([
            'title' => $request->title,
            'content' => $request->content,
            'status' => $request->status,
        ]);

        return redirect()->route('settings.pages.index')->with('success', 'Halaman berhasil diperbarui');
    }

    // Admin: Delete Page
    public function destroy(DynamicPage $page)
    {
        $page->delete();
        return back()->with('success', 'Halaman berhasil dihapus');
    }

    // Public: Show Page
    public function show($slug)
    {
        $page = DynamicPage::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('pages.show', compact('page'));
    }
}
