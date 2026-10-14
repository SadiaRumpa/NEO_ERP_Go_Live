<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\StockItemResource;
use App\Models\StockItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockItemApiController extends ApiController
{
    /**
     * Display a listing of stock items.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockItem::with('variants');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->boolean('low_stock')) {
            $query->whereHas('variants', function ($q) {
                $q->where('quantity', '<=', 10);
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $items = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($items, StockItemResource::class);
    }

    /**
     * Display the specified stock item.
     */
    public function show(int $id): JsonResponse
    {
        $item = StockItem::with('variants')->find($id);

        if (!$item) {
            return $this->errorResponse('Stock item not found.', 404);
        }

        return $this->successResponse(new StockItemResource($item));
    }
}
