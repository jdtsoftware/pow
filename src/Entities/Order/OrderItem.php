<?php

declare(strict_types=1);

namespace JDT\Pow\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;

/**
 * Class OrderItem.
 */
class OrderItem extends Model
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
        'tokens_total',
        'tokens_spent',
        'unit_price',
        'total_price',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $with = [
    ];

}
