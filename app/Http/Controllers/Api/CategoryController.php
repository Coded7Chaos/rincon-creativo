<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // 📍 GET /api/categories
    public function index()
    {
        return response()->json(Category::all());
    }

    // 📍 POST /api/categories
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    // 📍 GET /api/categories/{id}
    public function show(Category $category)
    {
        return response()->json($category);
    }

    // 📍 PUT /api/categories/{id}
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'nombre' => 'required|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $category->update($request->all());
        return response()->json($category);
    }

    // 📍 DELETE /api/categories/{id}
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}
