<?php

namespace App\Http\Controllers;

use App\Models\Module;

class ModuleController extends Controller
{
    public function index(Module $module)
    {
        abort_if(! $module->is_public, 404);

        $entries = $module->entries()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('modules.index', compact('module', 'entries'));
    }

    public function show(Module $module, string $entry)
    {
        abort_if(! $module->is_public, 404);

        $entry = $module->entries()
            ->where('slug', $entry)
            ->where('is_published', true)
            ->firstOrFail();

        return view('modules.show', compact('module', 'entry'));
    }
}
