<?php

namespace App\Http\Controllers;

use App\Models\StockAttribute;
use App\Models\StockItem;
use App\Models\StockCategory;
use App\Models\StockItemAttribute;
use App\Models\StockItemMovement;
use App\Models\StockWarehouse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\StockItemVariant;
use App\Models\StockItemVariantAttribute;
use Illuminate\Support\Facades\Auth;

class StockItemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StockItem::with('category')->select('*');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('category', function ($row) {
                    return $row->category_id ? $row->category->name : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('stockitems.show', $row->id);
                    return '
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm">Show</a>
                        <button class="delete btn btn-danger btn-sm" data-id="' . $row->id . '">Delete</button>
                    ';
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $categories = StockCategory::where('is_active', true)->get();

        return view('stockitems.index', compact('categories'));
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:stock_categories,id',
            'description' => 'nullable|string',
        ]);
        $stockItem = StockItem::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'is_active' => true, // Assuming you want to set it active by default
        ]);

        return redirect()->route('stockitems.show', $stockItem->id)
            ->with('success', 'Stock item created successfully.');
    }

    public function showItem($id)
    {
        $stockitem = StockItem::with(['category', 'attributes', 'attributes.attribute', 'variants'])->findOrFail($id);
        $categories = StockCategory::where('is_active', true)->get();
        $attributes = StockAttribute::get(); // Assuming you have a relationship for attributes
        $warehouses = StockWarehouse::where('is_active', true)->get();
        return view('stockitems.showitem', compact('stockitem', 'categories', 'attributes', 'warehouses'));
    }

    public function addAttributeToItem(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'stock_item_id' => 'required|exists:stock_items,id',
            'stock_attribute_id' => 'required|exists:stock_attributes,id',
        ]);

        // Check if attribute already added
        $exists = StockItemAttribute::where([
            'stock_item_id' => $request->stock_item_id,
            'stock_attribute_id' => $request->stock_attribute_id,
        ])->exists();

        if ($exists) {
            return response()->json(['status' => false, 'message' => 'Attribute already added.']);
        }

        StockItemAttribute::create([
            'stock_item_id' => $request->stock_item_id,
            'stock_attribute_id' => $request->stock_attribute_id,
        ]);

        return response()->json(['status' => true, 'message' => 'Attribute added successfully.']);
    }

    public function addItemVariants(Request $request, $stockItemId)
    {
        // ✅ Step 1: Validate input
        $request->validate([
            'variant_attributes' => 'required|array',
            'variant_attributes.*' => 'required|string|max:255',
            'warehouse_id' => 'required|exists:stock_warehouses,id',
        ]);

        DB::beginTransaction();

        try {
            // ✅ Step 2: Create the variant first
            $variant = StockItemVariant::create([
                'stock_item_id' => $stockItemId,
                'avg_price' => 0,
                'quantity' => 0,
                'sku' => '', // placeholder
                'warehouse_id' => $request->warehouse_id,
                'is_active' => true,
            ]);

            // ✅ Step 3: Add variant attributes
            $skuParts = [];

            foreach ($request->variant_attributes as $stockAttributeId => $value) {
                StockItemVariantAttribute::create([
                    'stock_item_variant_id' => $variant->id,
                    'stock_attribute_id' => $stockAttributeId,
                    'value' => $value,
                ]);

                $skuParts[] = Str::slug($value);
            }

            // Add warehouse name if available
            $warehouse = StockWarehouse::find($request->warehouse_id);
            $warehouseSlug = $warehouse ? Str::slug($warehouse->name) : 'unknown-warehouse';

            $stockItem = StockItem::findOrFail($stockItemId);

            // Add sanitized stock item name
            $stockItemSlug = Str::slug($stockItem->name);

            // Final SKU generation
            $variant->sku = implode('-', array_filter([
                $stockItemSlug,
                implode('-', $skuParts),
                $warehouseSlug,
            ]));

            $variant->name = implode('-', array_filter([
                $stockItemSlug,
                implode('-', $skuParts)
            ]));

            $variant->save();

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Variant saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function indexInventory(Request $request)
    {
        if ($request->ajax()) {
            $data = StockItemVariant::with(['stockItem', 'warehouse'])->select('*')->orderBy('name', 'asc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('warehouse', function ($row) {
                    return $row->warehouse ? $row->warehouse->name : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('stockitemsinventory.movement-history', $row->id);

                    return '
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm">History</a>

                        <button class="btn btn-success btn-sm" 
                            data-id="' . $row->id . '" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addStockModal" 
                            onclick="openAddStockModal(' . $row->id . ')">
                            <i class="ti tabler-plus"></i> Stock
                        </button>

                        <button class="btn btn-warning btn-sm" 
                            data-id="' . $row->id . '">
                            <i class="ti tabler-minus"></i> Assign Job</button>

                        <button class="btn btn-dark btn-sm" 
                            data-id="' . $row->id . '" 
                            data-bs-toggle="modal" 
                            data-bs-target="#transferStockModal" 
                            onclick="openTransferStockModal(' . $row->id . ')">
                            <i class="ti tabler-plus"></i> Transfer
                        </button>
                    ';
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $warehouses = StockWarehouse::where('is_active', true)->get();

        return view('stockitems.manage-inventory', compact('warehouses'));
    }

    public function addStockToInventory(Request $request)
    {
        $request->validate([
            'item_variant_id' => 'required|exists:stock_item_variants,id',
            'quantity' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'movement_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $itemVariant = StockItemVariant::lockForUpdate()->findOrFail($request->item_variant_id);

            // Create movement
            $movement = StockItemMovement::create([
                'item_variant_id' => $itemVariant->id,
                'quantity' => $request->quantity,
                'cost' => $request->cost,
                'movement_type' => 'in',
                'movement_date' => $request->movement_date,
                'user_id' => Auth::id(),
                'reference' => $request->reference,
                'status' => $request->status ?? 'completed',
                'reason' => $request->reason
            ]);

            // Update quantity
            $itemVariant->quantity += $request->quantity;

            // Update average price (improved logic)
            if ($request->cost !== null && $itemVariant->avg_price !== null) {
                $itemVariant->avg_price = ($itemVariant->avg_price + $request->cost) / 2;
            } elseif ($request->cost !== null) {
                $itemVariant->avg_price = $request->cost;
            }

            $itemVariant->save();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Stock movement recorded successfully.',
                'data' => $movement
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Stock movement failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showStockVarient($id)
    {
        $variant = StockItemVariant::with('warehouse', 'stockItem')->find($id);

        if (!$variant) {
            return response()->json(['status' => false, 'message' => 'Item not found']);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'stock_item_id' => $variant->stock_item_id,
                'source_warehouse_id' => $variant->warehouse_id,
                'source_warehouse_name' => $variant->warehouse?->name ?? '',
                'product_name' => $variant->stockItem?->name ?? '',
            ]
        ]);
    }

    public function transferStock(Request $request)
    {
        $request->validate([
            'item_variant_id' => 'required|exists:stock_item_variants,id',
            'quantity' => 'required|numeric|min:0.01',
            'source_warehouse_id' => 'required|exists:stock_warehouses,id',
            'destination_warehouse_id' => 'required|exists:stock_warehouses,id|different:source_warehouse_id',
            'movement_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            // Lock the source item for update
            $sourceItem = StockItemVariant::with(['warehouse'])
                ->lockForUpdate()
                ->where('id', $request->item_variant_id)
                ->where('warehouse_id', $request->source_warehouse_id)
                ->firstOrFail();

            if ($sourceItem->quantity < $request->quantity) {
                throw new \Exception('Insufficient stock in source warehouse.');
            }

            // Try to find the destination variant
            $destinationItem = StockItemVariant::where('stock_item_id', $sourceItem->stock_item_id)
                ->where('warehouse_id', $request->destination_warehouse_id)
                ->first();

            if (!$destinationItem) {
                // Create new variant in destination warehouse
                $destinationItem = StockItemVariant::create([
                    'stock_item_id' => $sourceItem->stock_item_id,
                    'warehouse_id' => $request->destination_warehouse_id,
                    'quantity' => 0,
                    'avg_price' => $sourceItem->avg_price,
                    'name' => $sourceItem->name,
                    'sku' => $sourceItem->name . '-' . Str::slug(optional($sourceItem->warehouse)->name, '-'),
                    'is_active' => true,
                ]);
            }

            // Log the movement
            $movement = StockItemMovement::create([
                'item_variant_id' => $sourceItem->id,
                'quantity' => $request->quantity,
                'movement_type' => 'transfer',
                'movement_date' => $request->movement_date,
                'user_id' => Auth::id(),
                'reference' => $request->reference,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'source' => 'warehouse',
                'destination' => 'warehouse',
                'status' => 'completed',
                'reason' => $request->reason,
            ]);

            // Adjust stock quantities
            $sourceItem->decrement('quantity', $request->quantity);
            $destinationItem->increment('quantity', $request->quantity);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Stock transfer recorded successfully.',
                'data' => $movement
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Stock transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stockInventoryMovementHistory(Request $request, $id)
    {
        $variant = StockItemVariant::with('stockItem')->find($id);

        if (!$variant) {
            return response()->json(['status' => false, 'message' => 'Item variant not found']);
        }

        $query = StockItemMovement::where('item_variant_id', $id)
            ->select('*')
            ->orderBy('movement_date', 'desc');

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('movement_date', function ($row) {
                    return $row->movement_date
                        ? \Carbon\Carbon::parse($row->movement_date)->format('Y-m-d')
                        : '';
                })
                ->addColumn('movement_type', function ($row) {
                    return ucfirst($row->movement_type);
                })
                ->addColumn('quantity', function ($row) {
                    return number_format($row->quantity, 2);
                })
                ->addColumn('source', function ($row) {
                    return $row->source ?? '-';
                })
                ->addColumn('destination', function ($row) {
                    return $row->destination ?? '-';
                })
                ->addColumn('status', function ($row) {
                    return ucfirst($row->status);
                })
                ->rawColumns(['movement_date', 'movement_type', 'quantity', 'source', 'destination', 'status'])
                ->make(true);
        }

        return view('stockitems.manage-inventory-history', compact('variant'));
    }



    public function deleteItem($id)
    {
        $stockItem = StockItem::findOrFail($id);
        $stockItem->delete();

        return response()->json(['status' => true, 'message' => 'Stock item deleted successfully.']);
    }
}
