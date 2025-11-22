<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Client;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    /**
     * Display a listing of sales with filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Sale::with(['client', 'user', 'saleDetails.product']);

        // Filter by client_id
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Order by sale_date descending
        $query->orderBy('sale_date', 'desc');

        $sales = $query->paginate($request->get('per_page', 15));

        return response()->json($sales);
    }

    /**
     * Store a newly created sale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|array',
            'client.id' => 'nullable|exists:clients,id',
            'client.name' => 'required_without:client.id|string|max:255',
            'client.phone' => 'required_without:client.id|string|max:20',
            'client.email' => 'nullable|email|max:255',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:CASH,CARD,YAPE,PLIN,CREDIT',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get or create client
            if (isset($request->client['id']) && $request->client['id']) {
                $client = Client::findOrFail($request->client['id']);
            } else {
                $client = Client::create([
                    'name' => $request->client['name'],
                    'phone' => $request->client['phone'],
                    'email' => $request->client['email'] ?? null,
                    'total_spent' => 0,
                ]);
            }

            // Get user (from auth or request)
            $userId = $request->user_id ?? auth()->id() ?? 1;

            // Create sale
            $sale = Sale::create([
                'client_id' => $client->id,
                'user_id' => $userId,
                'total_amount' => 0, // Will be calculated
                'status' => $request->payment_method === 'CREDIT' ? 'PENDING' : 'PAID',
                'payment_method' => $request->payment_method,
                'sale_date' => now(),
            ]);

            $totalAmount = 0;

            // Process products
            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['id']);

                // Verify stock
                if ($product->stock < $productData['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$productData['quantity']}"
                    ], 400);
                }

                $quantity = $productData['quantity'];
                $unitPrice = $product->sale_price;
                $subtotal = $quantity * $unitPrice;

                // Create sale detail
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Update product stock
                $product->decrement('stock', $quantity);

                // Create inventory movement
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'type' => 'OUT',
                    'quantity' => $quantity,
                    'reason' => 'SALE',
                    'related_sale_id' => $sale->id,
                ]);

                $totalAmount += $subtotal;
            }

            // Update sale total
            $sale->update(['total_amount' => round($totalAmount, 2)]);

            // Update client
            $client->increment('total_spent', $totalAmount);
            $client->update(['last_purchase_date' => now()]);

            DB::commit();

            // Generate WhatsApp link
            $phone = preg_replace('/[^0-9]/', '', $client->phone);
            if (!str_starts_with($phone, '51')) {
                $phone = '51' . $phone;
            }
            $whatsappLink = "whatsapp://send/?phone={$phone}&text=" . urlencode(
                "Hola {$client->name}, tu compra de S/ " . number_format($totalAmount, 2) . " se procesó exitosamente."
            );

            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully',
                'data' => [
                    'sale' => $sale->load(['client', 'user', 'saleDetails.product']),
                    'whatsapp_link' => $whatsappLink,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sale.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $sale = Sale::with(['client', 'user', 'saleDetails.product'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $sale
        ]);
    }

    /**
     * Update sale status (for payments module).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:PAID,PENDING,CANCELED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $sale = Sale::with('client')->findOrFail($id);
        $sale->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Sale status updated successfully',
            'data' => $sale->load(['client', 'user', 'saleDetails.product'])
        ]);
    }

    /**
     * Generate WhatsApp link for payment reminder.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentReminder($id)
    {
        $sale = Sale::with('client')->findOrFail($id);

        if ($sale->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Sale is not pending payment'
            ], 400);
        }

        $phone = preg_replace('/[^0-9]/', '', $sale->client->phone);
        if (!str_starts_with($phone, '51')) {
            $phone = '51' . $phone;
        }

        $saleDate = $sale->sale_date->format('d/m/Y');
        $whatsappLink = "whatsapp://send/?phone={$phone}&text=" . urlencode(
            "Hola {$sale->client->name}, tienes una deuda de S/ " . number_format($sale->total_amount, 2) . " del día {$saleDate}."
        );

        return response()->json([
            'success' => true,
            'data' => [
                'whatsapp_link' => $whatsappLink
            ]
        ]);
    }
}

