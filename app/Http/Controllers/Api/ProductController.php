<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //GET /api/products
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json($products);
    }

    //POST /api/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen_url' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id', 
        ]);

        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    //GET /api/products/{id}
    public function show(Product $product)
    {
        $product->load('category');
        return response()->json($product);
    }

    //PUT /api/products/{id}
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'imagen_url' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id', 
        ]);

        $product->update($validated);
        return response()->json($product);
    }

    //DELETE /api/products/{id}
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
