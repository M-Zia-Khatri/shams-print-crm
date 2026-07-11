<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemPaymentReceivedRequest;
use App\Models\ItemPaymentReceived;
use Illuminate\Http\JsonResponse;

class ItemPaymentReceivedController extends Controller
{
    public function store(ItemPaymentReceivedRequest $request): JsonResponse
    {
        $itemPaymentReceived = ItemPaymentReceived::create($request->validated());

        return response()->json([
            'message' => 'Payment received added successfully.',
            'data' => $itemPaymentReceived,
        ]);
    }

    public function edit(ItemPaymentReceived $itemPaymentReceived): JsonResponse
    {
        return response()->json([
            'data' => $itemPaymentReceived,
        ]);
    }

    public function update(ItemPaymentReceivedRequest $request, ItemPaymentReceived $itemPaymentReceived): JsonResponse
    {
        $itemPaymentReceived->update($request->validated());

        return response()->json([
            'message' => 'Payment received updated successfully.',
            'data' => $itemPaymentReceived,
        ]);
    }

    public function destroy(ItemPaymentReceived $itemPaymentReceived): JsonResponse
    {
        $itemPaymentReceived->delete();

        return response()->json([
            'message' => 'Payment received deleted successfully.',
        ]);
    }
}
