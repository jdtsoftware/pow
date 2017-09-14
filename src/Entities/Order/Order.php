<?php

declare(strict_types=1);

namespace JDT\Pow\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;
use JDT\Pow\Interfaces\Entities\Product;

/**
 * Class Order.
 */
class Order extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_id',
        'order_status_id',
        'payment_gateway_id',
        'total_price',
        'po_number',
        'payment_gateway_reference',
        'payment_gateway_blob',
        'created_user_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $with = [
    ];

    /**
     * @param $product
     * @param int $qty
     */
    public function addLineItem(Product $product, int $qty = 1)
    {
        $qty = (int) $qty;
        if($qty <= 0) { //lets be safe.
            $qty = 1;
        }

        $models = \Config::get('pow.models');
        $models['order_item']::create([
            'order_id' => $this->id,
            'product_id' => $product->getId(),
            'tokens_total' => 0,
            'tokens_spent' => 0,
            'qty' => $qty,
            'unit_price' => $product->getTotalPrice(),
            'total_price' => $product->getTotalPrice($qty),
        ]);
    }
}
