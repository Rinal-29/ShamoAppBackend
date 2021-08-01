<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormater;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        if ($id) {

            $category = ProductCategory::with(['products'])->find($id);

            if ($category) {
                return ResponseFormater::success(
                    $category,
                    'Data Kategori Berhasil Didapatkan'
                );
            } else {
                return ResponseFormater::error(
                    'null',
                    'Data Kategori tidak berhasil didapatkan',
                    404
                );
            }
        }

        $category = ProductCategory::query();

        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }

        if ($show_product) {
            $category->with('products');
        }

        return ResponseFormater::success($category->paginate($limit), 'Data List Kategori Berhasil didapatkan');
    }
}
