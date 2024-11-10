<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\ProductGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mavinoo\Batch\BatchFacade;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Product::orderBy('created_at', 'desc')->paginate($request['limit'] ?? 10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        // Lấy thêm các trường khác từ $request
        $extraFields = $request->only(['avatarUrl', 'galleryUrls']);

        // Kết hợp dữ liệu đã xác thực và các trường bổ sung
        $input = array_merge($validated, $extraFields);

        $data =  $validated;
        $data['avatar'] = isset($input['avatarUrl']) && $input['avatarUrl'] ? str_replace(url("/"), "", $input['avatarUrl']) : '/storage/uploads/zg732RP3l2GtYbbCfnttEPL5B1x7H2ywTZHvT5SG.png';

        DB::beginTransaction();

        try {

            $product = Product::create($data);

            $gallerys = [];
            if ($product && !empty($input['galleryUrls'])) {
                foreach ($input['galleryUrls'] as $value) {
                    $gallerys[] = [
                        'product_id' => $product['id'],
                        'url' => str_replace(url("/"), "", $value['url']),
                        'created_at' => now()
                    ];
                }
                ProductGallery::insert($gallerys);
            }
            DB::commit();

            return $product;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Product::with('galleries')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        // Lấy thêm các trường khác từ $request
        $extraFields = $request->only(['avatarUrl', 'galleryUrls']);

        // Kết hợp dữ liệu đã xác thực và các trường bổ sung
        $input = array_merge($validated, $extraFields);

        $data =  $validated;
        $data['avatar'] = isset($input['avatarUrl']) && $input['avatarUrl'] ? str_replace(url("/"), "", $input['avatarUrl']) : '/storage/uploads/zg732RP3l2GtYbbCfnttEPL5B1x7H2ywTZHvT5SG.png';

        DB::beginTransaction();

        try {
            $product->update($data);

            if ($product && !empty($input['galleryUrls'])) {
                $gallerysUpdate = [];
                $gallerysCreate = [];
                $galleryIdsToKeep = [];

                foreach ($input['galleryUrls'] as $value) {
                    if (isset($value['id']) && $value['id']) {
                        // Gallery cần update
                        $gallerysUpdate[] = [
                            'id' => $value['id'],
                            'product_id' => $product['id'],
                            'url' => str_replace(url("/"), "", $value['url']),
                            'updated_at' => now()
                        ];
                        $galleryIdsToKeep[] = $value['id'];
                    } else {
                        // Gallery cần create mới
                        $gallerysCreate[] = [
                            'product_id' => $product['id'],
                            'url' => str_replace(url("/"), "", $value['url']),
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }

                // 1. Thực hiện batch update cho các gallery đã tồn tại
                if (!empty($gallerysUpdate)) {
                    BatchFacade::update(new ProductGallery, $gallerysUpdate, 'id');
                }

                // 2. Xóa các gallery không thuộc gallerysUpdate hoặc gallerysCreate
                ProductGallery::where('product_id', $product['id'])
                    ->whereNotIn('id', $galleryIdsToKeep)
                    ->delete();

                // 3. Batch insert các gallery mới
                if (!empty($gallerysCreate)) {
                    ProductGallery::insert($gallerysCreate);
                }
            }
            DB::commit();

            return $product;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        DB::beginTransaction();

        try {
            $product->delete();

            DB::commit();

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Product deletion failed'], 500);
        }
    }
}
