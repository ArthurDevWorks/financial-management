<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('categories/Index', [
            'categories' => Category::query()
                ->orderBy('type')
                ->orderBy('name')
                ->paginate(10)
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

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Categoria removida com sucesso');
    }
}
