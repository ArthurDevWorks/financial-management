<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('categories/Index', [
            'categories' => Category::query()
                ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('type')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('categories/Create', [
            'types' => \App\Enums\CategoryType::all(),
        ]);
    }

    public function store(CategoryStoreRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Categoria cadastrada com sucesso');
    }

    public function edit(Category $category)
    {
        return Inertia::render('categories/Edit', [
            'category' => $category,
            'types' => \App\Enums\CategoryType::all(),
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso');
    }

    public function export()
    {
        $categories = Category::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $callback = function () use ($categories) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Nome', 'Tipo']);

            foreach ($categories as $category) {
                fputcsv($handle, [
                    $category->id,
                    $category->name,
                    ucfirst($category->type),
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="categorias.csv"',
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Categoria removida com sucesso');
    }
}
