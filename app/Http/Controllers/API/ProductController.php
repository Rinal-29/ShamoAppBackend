<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormater;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $description = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');


        $product = Product::with(['category', 'galleries']);

        if ($id) {
            $product->find($id);

            if ($product) {
                return ResponseFormater::success($product, 'produk berhasil didapatkan');
            } else {
                return ResponseFormater::error(null, 'produk tidak ditemukan', 404);
            }
        }

        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }

        if ($description) {
            $product->where('description', 'like', '%' . $description . '%');
        }

        if ($tags) {
            $product->where('tags', 'like', '%' . $tags . '%');
        }

        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }

        if ($price_to) {
            $product->where('price', '>=', $price_to);
        }

        if ($categories) {
            $product->where('categories', $categories);
        }

        return ResponseFormater::success($product->paginate($limit), 'produk berhasil didapatkan');
    }
}