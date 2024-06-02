<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\product\StoreRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    //
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();
        try {
            $product = Product::create($validated);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json(['error' => 'Error creating product'], 500);
        }
        return response()->json($product, 201);
    }
}
