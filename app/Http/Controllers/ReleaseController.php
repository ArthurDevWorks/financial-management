<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReleaseStoreRequest;
use App\Http\Requests\ReleaseUpdateRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Release;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReleaseController extends Controller
{
    public function index(Request $request)
    {
        $releases = Release::with(['account', 'category'])
            ->where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate();

        return Inertia::render('releases/Index', [
            'releases' => $releases,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('releases/Create', [
            'accounts' => Account::with('bank')->where('user_id', Auth::id())->get(),
            'categories' => Category::where('type', 'receita')->orWhere('type', 'despesa')->get(),
        ]);
    }

    public function store(ReleaseStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        Release::create($validated);

        return redirect()->route('releases.index')->with('success', 'Lançamento criado com sucesso.');
    }

    public function edit(Release $release)
    {
        if ($release->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('releases/Edit', [
            'release' => $release,
            'accounts' => Account::with('bank')->where('user_id', Auth::id())->get(),
            'categories' => Category::all(),
        ]);
    }

    public function update(ReleaseUpdateRequest $request, Release $release)
    {
        if ($release->user_id !== Auth::id()) {
            abort(403);
        }

        $release->update($request->validated());

        return redirect()->route('releases.index')->with('success', 'Lançamento atualizado com sucesso.');
    }

    public function destroy(Release $release)
    {
        if ($release->user_id !== Auth::id()) {
            abort(403);
        }

        $release->delete();

        return redirect()->route('releases.index')->with('success', 'Lançamento excluído com sucesso.');
    }
}
