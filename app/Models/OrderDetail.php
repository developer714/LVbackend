<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class OrderDetail
 *
 * @property int $id
 * @property int|null $order_id
 * @property int|null $product_id
 * @property int|null $seller_id
 * @property string|null $digital_file_after_sell
 * @property string|null $product_details
 * @property int $qty
 * @property float $price
 * @property float $tax
 * @property float $discount
 * @property string $tax_model
 * @property string $delivery_status
 * @property string $payment_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int|null $shipping_method_id
 * @property string|null $variant
 * @property string|null $variation
 * @property string|null $discount_type
 * @property bool $is_stock_decreased
 * @property int|null $refund_request
 *
 * @package App\Models
 */
class OrderDetail extends Model
{
    use StorageTrait;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'pv',
        'quantity',
    ];

    protected $casts = [
        'price' => 'float',
        'pv' => 'float',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    //active_product
    public function activeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class)->where('status', 1);
    }

    //product_all_status
    public function productAllStatus(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_address');
    }

    //verification_images
    public function verificationImages(): HasMany
    {
        return $this->hasMany(OrderDeliveryVerification::class, 'order_id', 'order_id');
    }

    public function orderStatusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id', 'order_id');
    }

    public function getDigitalFileAfterSellFullUrlAttribute(): string|null|array
    {
        $value = $this->digital_file_after_sell;
        if (count($this->storage) > 0 ) {
            $storage = $this->storage->where('key','digital_file_after_sell')->first();
        }
        return $this->storageLink('product/digital-product', $value, $storage['value'] ?? 'public');
    }

    protected $with = ['storage'];
    protected $appends = ['digital_file_after_sell_full_url'];

    protected static function boot(): void
    {
        parent::boot();
        static::saved(function ($model) {
            if ($model->isDirty('digital_file_after_sell')) {
                $storage = config('filesystems.disks.default') ?? 'public';
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'digital_file_after_sell',
                ], [
                    'value' => $storage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
