<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Order;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;

/**
 * Class OrderItem.
 */
class OrderItem extends Model implements iOrderItemEntity
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_item';

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
        'order_id',
        'product_id',
        'quantity',
        'tokens_total',
        'tokens_spent',
        'original_unit_price',
        'adjusted_unit_price',
        'original_total_price',
        'adjusted_total_price',
        'vat_percentage',
        'original_vat_price',
        'adjusted_vat_price',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->adjusted_total_price;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product'], 'id', 'product_id');
    }
}
