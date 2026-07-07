<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReleaseStoreRequest;
use App\Http\Requests\ReleaseUpdateRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Release;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class ReleaseController extends Controller
{
    public function index(Request $request)
    {
        $releases = Release::with(['account', 'category'])
            ->where('user_id', Auth::id())
            ->when($request->search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('account', fn ($q) => $q->where('account', 'like', "%{$search}%"));
            }))
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('releases/Index', [
            'releases' => $releases,
            'filters' => $request->only(['search']),
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

    public function export()
    {
        $releases = Release::with(['account', 'category'])
            ->where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $callback = function () use ($releases) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Título', 'Descrição', 'Categoria', 'Conta', 'Data', 'Valor', 'Tipo']);

            foreach ($releases as $release) {
                fputcsv($handle, [
                    $release->id,
                    $release->title,
                    $release->description ?? '',
                    $release->category?->name ?? 'Sem Categoria',
                    $release->account?->account ?? '-',
                    $release->date?->format('d/m/Y'),
                    number_format($release->amount, 2, ',', '.'),
                    $release->type === 'revenue' ? 'Receita' : 'Despesa',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="lancamentos.csv"',
        ]);
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
