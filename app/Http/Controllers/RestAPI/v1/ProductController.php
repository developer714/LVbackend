<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MostDemanded;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\Wishlist;
use App\Traits\FileManagerTrait;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use App\Utils\ProductManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use FileManagerTrait;
    public function __construct(
        private Product      $product,
        private Order        $order,
        private MostDemanded $most_demanded,
    )
    {
    }

    
    /**
    * Display a listing of the resource.
    */

    public function index() {
        $products = Product::select('id', 'category_id', 'sub_category_id', 'sub_sub_category_id', 'name', 'nation', 'amount', 'pv', 'images', 'updated_at')->get();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ], 200);
    }

    /**
    * Store a newly created resource in storage.
    */

    public function store( Request $request ) {
        $validator = Validator::make($request->all(), rules: [ 
            'name' => 'required|string',
            'category_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'sub_sub_category_id' => 'nullable|integer',
            'nation' => 'nullable|string',
            'amount' => 'nullable|integer',
            'pv' => 'nullable|string',
            'images' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $product = Product::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 201);
    }

    /**
    * Display the specified resource.
    */

    public function show( string $id ) {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 200);
    }

    /**
    * Show the form for editing the specified resource.
    */

    public function edit( string $id ) {
        $product = Product::findOrFail( $id );
        // Using Branch model
        // Return branch data for editing
        return response()->json( $product );
    }

    /**
    * Update the specified resource in storage.
    */

    public function update(Request $request, string $id) {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [ 
            'category_id' => 'required|integer',
            'sub_category_id' => 'nullable|integer',
            'sub_sub_category_id' => 'nullable|integer',
            'name' => 'required|string',
            'nation' => 'nullable|string',
            'amount' => 'nullable|string',
            'pv' => 'nullable|string',
            'images' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $product->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 200);
    }

    /**
    * Remove the specified resource from storage.
    */

    public function destroy( string $id ) {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ], 200);
    }

    public function get_latest_products(Request $request): JsonResponse
    {
        $products = ProductManager::get_latest_products($request, $request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function getNewArrivalProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getNewArrivalProducts($request, $request['limit'], $request['offset']);
        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }

    public function getFeaturedProductsList(Request $request): JsonResponse
    {
        $products = ProductManager::getFeaturedProductsList($request, $request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function getTopRatedProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getTopRatedProducts($request, $request['limit'], $request['offset']);
        $productsList = count($products->items()) > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }

    public function get_searched_products(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $products = ProductManager::search_products($request, $request['name'], 'all', $request['limit'], $request['offset']);

        if ($products['products'] == null) {
            $products = ProductManager::translated_product_search($request['name'], 'all', $request['limit'], $request['offset']);
        }
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function product_filter(Request $request)
    {
        $search = [base64_decode($request->search)];
        $categories = json_decode($request->category);
        $brand = json_decode($request->brand);

        // products search
        $products = Product::active()->with(['rating', 'tags'])
            ->where(function ($query) use ($search) {
                foreach ($search as $value) {
                    $query->orWhere('name', 'like', "%{$value}%")
                        ->orWhereHas('tags', function ($query) use ($search) {
                            $query->where(function ($q) use ($search) {
                                foreach ($search as $value) {
                                    $q->where('tag', 'like', "%{$value}%");
                                }
                            });
                        });
                }
            })
            ->withCount(['reviews' => function ($query) {
                $query->active()->whereNull('delivery_man_id');
            }])
            ->when($request->has('brand') && count($brand) > 0, function ($query) use ($request, $brand) {
                return $query->whereIn('brand_id', $brand);
            })
            ->when($request->has('category') && count($categories) > 0, function ($query) use ($categories) {
                return $query->whereIn('category_id', $categories)
                    ->orWhereIn('sub_category_id', $categories)
                    ->orWhereIn('sub_sub_category_id', $categories);
            })
            ->when($request->has('sort_by') && !empty($request->sort_by), function ($query) use ($request) {
                $query->when($request['sort_by'] == 'low-high', function ($query) {
                    return $query->orderBy('unit_price', 'ASC');
                })
                    ->when($request['sort_by'] == 'high-low', function ($query) {
                        return $query->orderBy('unit_price', 'DESC');
                    })
                    ->when($request['sort_by'] == 'a-z', function ($query) {
                        return $query->orderBy('name', 'ASC');
                    })
                    ->when($request['sort_by'] == 'z-a', function ($query) {
                        return $query->orderBy('name', 'DESC');
                    })
                    ->when($request['sort_by'] == 'latest', function ($query) {
                        return $query->latest();
                    });
            })
            ->when(!empty($request['price_min']) || !empty($request['price_max']), function ($query) use ($request) {
                return $query->whereBetween('unit_price', [$request['price_min'], $request['price_max']]);
            });

        $products = ProductManager::getPriorityWiseSearchedProductQuery(query: $products, keyword: implode(' ', $search), dataLimit: $request['limit'], offset: $request['offset'], type: 'searched');

        return [
            'total_size' => $products->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'min_price' => $products->min('unit_price'),
            'max_price' => $products->max('unit_price'),
            'products' => count($products) > 0 ? Helpers::product_data_formatting($products->items(), true) : [],
        ];
    }

    public function get_suggestion_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $products = ProductManager::search_products($request, $request['name'], 'all', $request['limit'], $request['offset']);
        if ($products['products'] == null) {
            $products = ProductManager::translated_product_search($request['name'], 'all', $request['limit'], $request['offset']);
        }

        $products_array = [];
        if ($products['products']) {
            foreach ($products['products'] as $product) {
                $products_array[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }
        }

        return response()->json(['products' => $products_array], 200);
    }

    public function getProductDetails(Request $request, $slug)
    {
        $user = Helpers::get_customer($request);

        $product = Product::with(['reviews.customer', 'seller.shop', 'tags', 'digitalVariation'])
            ->withCount(['wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }])
            ->where(['slug' => $slug])->first();

        if (isset($product)) {
            $product = Helpers::product_data_formatting($product, false);

            if (isset($product->reviews) && !empty($product->reviews)) {
                $overallRating = getOverallRating($product->reviews);
                $product['average_review'] = $overallRating[0];
            } else {
                $product['average_review'] = 0;
            }

            $temporary_close = Helpers::get_business_settings('temporary_close');
            $inhouse_vacation = Helpers::get_business_settings('vacation_add');
            $inhouse_vacation_start_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_start_date'] : null;
            $inhouse_vacation_end_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_end_date'] : null;
            $inhouse_temporary_close = $product['added_by'] == 'admin' ? $temporary_close['status'] : false;
            $product['inhouse_vacation_start_date'] = $inhouse_vacation_start_date;
            $product['inhouse_vacation_end_date'] = $inhouse_vacation_end_date;
            $product['inhouse_temporary_close'] = $inhouse_temporary_close;
            $product['reviews_count'] = $product->reviews->count();
        }
        return response()->json($product, 200);
    }

    public function getBestSellingProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getBestSellingProductsList($request, $request['limit'], $request['offset']);
        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }

    public function get_home_categories(Request $request)
    {
        $categories = Category::whereHas('product', function ($query){
            return $query->active();
        })->where('home_status', true)->get();
        $categories->map(function ($data) use ($request) {
            $data['products'] = Helpers::product_data_formatting(CategoryManager::products($data['id'], $request), true);
            return $data;
        });
        return response()->json($categories, 200);
    }

    public function get_related_products(Request $request, $id)
    {
        if (Product::find($id)) {
            $products = ProductManager::get_related_products($id, $request);
            $products = Helpers::product_data_formatting($products, true);
            return response()->json($products, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('product_not_found')]
        ], 404);
    }

    public function get_product_reviews($id)
    {
        $reviews = Review::with(['customer', 'reply'])->where(['product_id' => $id])->get();
        foreach ($reviews as $item) {
            $item['attachment_full_url'] = $item->attachment_full_url;
        }
        return response()->json($reviews, 200);
    }

    public function getProductReviewByOrder(Request $request, $productId, $orderId): JsonResponse
    {
        $user = $request->user();
        $reviews = Review::with('reply')->where(['product_id' => $productId, 'customer_id' => $user->id])->whereNull('delivery_man_id')->get();
        $reviewData = null;
        foreach ($reviews as $review) {
            if ($review->order_id == $orderId) {
                $reviewData = $review;
            }
        }
        if (isset($reviews[0]) && !$reviewData) {
            $reviewData = ($reviews[0]['order_id'] == null) ? $reviews[0] : null;
        }
        if ($reviewData) {
            $reviewData['attachment_full_url'] = $reviewData->attachment_full_url;
        }

        return response()->json($reviewData ?? [], 200);
    }

    public function deleteReviewImage(Request $request): JsonResponse
    {
        $review = Review::find($request['id']);

        $array = [];
        foreach ($review->attachment as $image) {
            $imageName = $image['file_name'] ?? $image;
            if ($imageName != $request['name']) {
                $array[] = $image;
            } else {
                $this->delete(filePath: 'review/' . $request['name']);
            }
        }

        $review->attachment = $array;
        $review->save();
        return response()->json(translate('review_image_removed_successfully'), 200);
    }

    public function get_product_rating($id)
    {
        try {
            $product = Product::find($id);
            $overallRating = getOverallRating($product->reviews);
            return response()->json(floatval($overallRating[0]), 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function counter($product_id)
    {
        try {
            $countOrder = OrderDetail::where('product_id', $product_id)->count();
            $countWishlist = Wishlist::where('product_id', $product_id)->count();
            return response()->json(['order_count' => $countOrder, 'wishlist_count' => $countWishlist], 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function social_share_link($product_slug)
    {
        $product = Product::where('slug', $product_slug)->first();
        $link = route('product', $product->slug);
        try {

            return response()->json($link, 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function submit_product_review(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $image_array = [];
        if (!empty($request->file('fileUpload'))) {
            foreach ($request->file('fileUpload') as $image) {
                if ($image != null) {
                    $image_array[] = [
                        'file_name' => $this->upload('review/', 'webp', $image),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                }
            }
        }


        $reviewData = Review::where([
                'delivery_man_id' => null,
                'customer_id' => $request->user()->id,
                'product_id' => $request['product_id'],
                'order_id' => $request['order_id'],
            ])->first();
        if ($reviewData) {
            $reviewData->update([
                'customer_id' => $request->user()->id,
                'product_id' => $request['product_id'],
                'comment' => $request['comment'],
                'rating' => $request['rating'],
                'attachment' => $image_array,
            ]);
        } else {
            $reviewArray = [
                'customer_id' => $request->user()->id,
                'order_id' => $request['order_id'],
                'product_id' => $request['product_id'],
                'comment' => $request['comment'],
                'rating' => $request['rating'],
                'attachment' => $image_array,
            ];


            $oldReview = Review::where(['order_id' => $request['order_id']])->get();
            if (count($oldReview) > 0) {
                $review_id = $oldReview[0]['order_id'] . (count($oldReview) + 1);
            } else {
                $review_id = $request['order_id'] . '1';
            }
            $reviewArray['id'] = $review_id;
            Review::create($reviewArray);
        }

        return response()->json(['message' => translate('successfully_review_submitted')], 200);
    }

    public function updateProductReview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $review = Review::find($request['id']);
        $image_array = [];
        if ($review && $review->attachment && $request->has('fileUpload')) {
            foreach ($review->attachment as $image) {
                $image_array[] = $image;
            }
        }
        if (!empty($request->file('fileUpload'))) {
            foreach ($request->file('fileUpload') as $image) {
                if ($image != null) {
                    $image_array[] = [
                        'file_name' => $this->upload('review/', 'webp', $image),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                }
            }
        }

        $review->order_id = $request->order_id;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->attachment = $image_array;
        $review->save();

        return response()->json(['message' => translate('successfully_review_updated')], 200);
    }

    public function submit_deliveryman_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = Order::where([
            'id' => $request->order_id,
            'customer_id' => $request->user()->id,
            'payment_status' => 'paid'])->first();

        if (!isset($order->delivery_man_id)) {
            return response()->json(['message' => translate('invalid_review')], 403);
        }

        Review::updateOrCreate(
            [
                'delivery_man_id' => $order->delivery_man_id,
                'customer_id' => $request->user()->id,
                'order_id' => $order->id
            ],
            [
                'customer_id' => $request->user()->id,
                'order_id' => $order->id,
                'delivery_man_id' => $order->delivery_man_id,
                'comment' => $request->comment,
                'rating' => $request->rating,
            ]
        );

    }

    public function get_shipping_methods(Request $request)
    {
        $methods = ShippingMethod::where(['status' => 1])->get();
        return response()->json($methods, 200);
    }

    public function get_discounted_product(Request $request)
    {
        $products = ProductManager::get_discounted_product($request, $request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function get_most_demanded_product(Request $request)
    {
        $user = Helpers::get_customer($request);
        // Most demanded product
        $products = MostDemanded::where('status', 1)->with(['product' => function ($query) use ($user) {
            $query->withCount(['orderDetails', 'orderDelivered', 'reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }]);
        }])->whereHas('product', function ($query) {
            return $query->active();
        })->first();

        if ($products) {
            $products['banner'] = $products->banner ?? '';
            $products['product_id'] = $products->product['id'] ?? 0;
            $products['slug'] = $products->product['slug'] ?? '';
            $products['review_count'] = $products->product['reviews_count'] ?? 0;
            $products['order_count'] = $products->product['order_details_count'] ?? 0;
            $products['delivery_count'] = $products->product['order_delivered_count'] ?? 0;
            $products['wishlist_count'] = $products->product['wish_list_count'] ?? 0;

            unset($products->product['category_ids']);
            unset($products->product['images']);
            unset($products->product['details']);
            unset($products->product);
        } else {
            $products = [];
        }

        return response()->json($products, 200);
    }

    public function get_shop_again_product(Request $request)
    {
        $user = Helpers::get_customer($request);
        if ($user != 'offline') {
            $products = Product::active()->with('seller.shop', 'reviews')
                ->withCount(['wishList' => function ($query) use ($user) {
                    $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                }])
                ->whereHas('seller.orders', function ($query) use ($request) {
                    $query->where(['customer_id' => $request->user()->id, 'seller_is' => 'seller']);
                })
                ->select('id', 'name', 'slug', 'thumbnail', 'unit_price', 'purchase_price', 'added_by', 'user_id')
                ->inRandomOrder()->take(12)->get();

            $products?->map(function ($product) {
                $product['reviews_count'] = $product->reviews->count();
                unset($product->reviews);
                return $product;
            });
        } else {
            $products = [];
        }


        return response()->json($products, 200);
    }

    public function just_for_you(Request $request)
    {
        $user = Helpers::get_customer($request);
        if ($user != 'offline') {
            $orders = $this->order->where(['customer_id' => $user->id])->with(['details'])->get();

            if ($orders) {
                $orders = $orders?->map(function ($order) {
                    $order_details = $order->details->map(function ($detail) {
                        $product = json_decode($detail->product_details);
                        $category = json_decode($product->category_ids)[0]->id;
                        $detail['category_id'] = $category;
                        return $detail;
                    });
                    $order['id'] = $order_details[0]->id;
                    $order['category_id'] = $order_details[0]->category_id;

                    return $order;
                });

                $categories = [];
                foreach ($orders as $order) {
                    $categories[] = ($order['category_id']);;
                }
                $ids = array_unique($categories);


                $just_for_you = $this->product->with([
                    'compareList' => function ($query) use ($user) {
                        return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                    }
                ])
                    ->withCount(['wishList' => function ($query) use ($user) {
                        $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                    }])
                    ->active()
                    ->where(function ($query) use ($ids) {
                        foreach ($ids as $id) {
                            $query->orWhere('category_ids', 'like', "%{$id}%");
                        }
                    })
                    ->inRandomOrder()
                    ->take(8)
                    ->get();
            } else {
                $just_for_you = $this->product->with([
                    'compareList' => function ($query) use ($user) {
                        return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                    }
                ])
                    ->withCount(['wishList' => function ($query) use ($user) {
                        $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                    }])
                    ->active()
                    ->inRandomOrder()
                    ->take(8)
                    ->get();
            }
        } else {
            $just_for_you = $this->product->with([
                'compareList' => function ($query) use ($user) {
                    return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                }
            ])
                ->withCount(['wishList' => function ($query) use ($user) {
                    $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                }])
                ->active()
                ->inRandomOrder()
                ->take(8)
                ->get();
        }

        $products = Helpers::product_data_formatting($just_for_you, true);

        return response()->json($products, 200);
    }

    public function getMostSearchingProductsList(Request $request): JsonResponse
    {
        $products = ProductManager::getBestSellingProductsList($request, $request['limit'], $request['offset']);
        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }
}
