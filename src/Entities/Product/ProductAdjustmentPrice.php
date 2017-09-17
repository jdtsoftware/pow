<?php

namespace JDT\Pow\Entities\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Finder\Expression\Expression;

/**
 * Class ProductAdjustmentPrice.
 */
class ProductAdjustmentPrice extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_adjustment_price';

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
        'product_id',
        'criteria',
        'adjustment'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product'], 'id', 'product_id');
    }

    /**
     * @param $price
     * @param $qty
     * @return string
     */
    public function getAdjustedPrice($price, $qty)
    {
        if(empty($this->criteria)) {
            return $price;
        }

        $expression = new ExpressionLanguage();
        $result = $expression->evaluate($this->criteria,['qty' => $qty]);

        if($result) {
            return $expression->evaluate($this->adjustment, ['price' => $price]);
        }

        return $price;
    }
}
