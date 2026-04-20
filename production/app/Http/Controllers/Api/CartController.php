<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;
use App\Models\Cart;
use App\Models\EventBooking;


class CartController extends Controller
{


    private function getUserCart($user)
        {
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart || $cart->isExpired()) {
                $cart?->delete();

                $cart = Cart::create([
                    'user_id' => $user->id,
                    'expires_at' => now()->addDay()
                ]);
            }

            return $cart;
        }


    public function index()
        {
            $user = Auth::guard('sanctum')->user();

            $cart = $this->getUserCart($user);

            return ApiResponse::success($cart->items);

            // return response()->json([
            //     'items' => $cart->items,
            //     'expires_at' => $cart->expires_at
            // ]);
        }


    public function add(Request $request)
        {
            $request->validate([
                'item_type' => 'required|string',
                'item_id' => 'required|integer',
                'title' => 'required|string',
                'price' => 'required|numeric',
                'metadata' => 'nullable|array'
            ]);

            $user = Auth::guard('sanctum')->user();

            $cart = $this->getUserCart($user);

            if ($request->item_type === 'event') {
                $alreadyBooked = EventBooking::where('event_id', $request->item_id)
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['confirmed', 'paid'])
                    ->exists();

                if ($alreadyBooked) {
                    return response()->json(['message' => 'You already paid/booked this event.'], 409);
                }
            }

            $exists = $cart->items()
                ->where('item_type', $request->item_type)
                ->where('item_id', $request->item_id)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'Item already in cart'], 409);
            }

            $cart->items()->create([
                'item_type' => $request->item_type,
                'item_id' => $request->item_id,
                'title' => $request->title,
                'price' => $request->price,
                'metadata' => $request->metadata
            ]);

            return ApiResponse::success('Added to cart');

            // return response()->json(['message' => 'Added to cart']);
        }


    public function remove($id)
        {

            $user = Auth::guard('sanctum')->user();
            $cart = $this->getUserCart($user);

            $cart->items()->where('id', $id)->delete();

            return ApiResponse::success('Item removed');
        }


    
}
