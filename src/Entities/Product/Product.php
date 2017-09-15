<?php

namespace JDT\Pow\Entities\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;

/**
 * Class Product.
 */
class Product extends Model implements \JDT\Pow\Interfaces\Entities\Product
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product';

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
        'handle',
        'name',
        'description',
        'total_price',
        'created_user_id',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function token()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product_token'], 'product_id', 'id');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTotalPrice($qty = 0, $display = false)
    {
        $totalPrice = $this->total_price;
        if($qty > 0) {
            $totalPrice = $this->total_price * $qty;
        }

        return $totalPrice;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getVATCharge($totalPrice)
    {
        $vat = \Config::get('pow.vat');
        if(empty($vat) || empty($totalPrice)) {
            return 0;
        }

        return ($vat / 100) * $totalPrice;
    }
}
