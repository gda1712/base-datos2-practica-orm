<?php

namespace App\Http\Controllers;

use App\Http\Requests\movement\StoreMovementRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Movement;
use App\Models\Product;

class MovementController extends Controller
{
    // Methods with ORM
    public function storeMovementWithORM(StoreMovementRequest $request)
    {
        $validated = $request->validated();
        $date = now();
        $hour = $date->format('H:i:s');
        $validated['date'] = $date;
        $validated['hour'] = $hour;

        try {
            DB::beginTransaction();

            // Select for update in products table
            $product = Product::lockForUpdate()->where('id', $validated['product_id'])->first();

            if(empty($product)) {
                DB::rollBack();
                return response()->json(['error' => 'Product not found'], 404);
            }

            if($validated['type'] === 'subtraction' && $product->quantity < $validated['quantity']) {
                DB::rollBack();
                return response()->json(['error' => 'Insufficient quantity'], 400);
            }

            $movement = Movement::create($validated);
            $product->last_movement_id = $movement->id;

            // update quantity
            if ($validated['type'] === 'addition') {
                $product->quantity += $validated['quantity'];
                $product->save();
            } else {
                $product->quantity -= $validated['quantity'];
                $product->save();
            }

            DB::commit();

        } catch (\Exception $e) {
            // Log in case of error
            DB::rollBack();
            Log::error('Error creating movement: ' . $e->getMessage());
            return response()->json(['error' => 'Error creating movement'], 500);
        }
        return response()->json([
            'message' => 'Movement created successfully'
        ], 201);
    }

    //Methods without ORM

    public function storeMovementWithoutORM(StoreMovementRequest $request)
    {
        $validated = $request->validated();
        $productId = $validated['product_id'];
        $quantity = $validated['quantity'];
        $type = $validated['type'];

        $date = now();
        $hour = $date->format('H:i:s');

        try {
            DB::beginTransaction();

            $product = DB::select("SELECT * FROM product WHERE id = ? FOR UPDATE", [$productId]);
            $product = $product[0] ?? [];

            if (empty($product)) {
                DB::rollBack();
                return response()->json(['error' => 'Product not found'], 404);
            }

            if ($type === 'subtraction' && $product->quantity < $quantity) {
                DB::rollBack();
                return response()->json(['error' => 'Insufficient quantity'], 400);
            }

            // Insert the movement
            DB::insert("INSERT INTO movement (product_id, date, hour, quantity, type) VALUES (?, ?, ?, ?, ?)",
                [$productId, $date, $hour, $quantity, $type]);

            if ($type === 'addition') {
                DB::update("UPDATE product SET quantity = quantity + ? WHERE id = ?", [$quantity, $productId]);
            } else {
                DB::update("UPDATE product SET quantity = quantity - ? WHERE id = ?", [$quantity, $productId]);
            }

            // Add last movement to product register
            DB::update(
                "UPDATE product SET last_movement_id = (SELECT MAX(id) FROM movement where product_id = ?) WHERE id = ?",
                [$productId, $productId]
            );

            DB::commit();

        } catch (\Exception $e) {
           // Log in case of error
            DB::rollBack();
            Log::error('Error creating movement: ' . $e->getMessage());
            return response()->json(['error' => 'Error creating movement'], 500);
        }

        return response()->json([
            'message' => 'Movement created successfully'
        ], 201);
    }
}
