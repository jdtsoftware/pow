<?php

namespace JDT\Pow\Entities\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;
use JDT\Pow\Http\Requests\SaveProductRequest;
use JDT\Pow\Traits\VatCharge;

/**
 * Class Product.
 */
class Product extends Model implements \JDT\Pow\Interfaces\Entities\Product
{
    use SoftDeletes;
    use VatCharge;

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

    protected static function boot()
    {
        $updateHandle = function ($model) {
            $model->handle = $model->toHandle($model->name);
        };

        static::creating($updateHandle);
        static::updating($updateHandle);
    }

    /**
     * @param $string
     * @return string
     * @todo move to model
     */
    public function toHandle($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($string)); // Removes special chars.
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function token()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product_token'], 'product_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function adjustment()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product_adjustment_price'], 'product_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shop()
    {
        $models = \Config::get('pow.models');
        return $this->hasMany($models['product_shop'], 'product_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderForm()
    {
        $models = \Config::get('pow.models');
        return $this->hasMany($models['product_order_form'], 'product_id', 'id');
    }

    /**
     * @param null $key
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|null
     */
    public function config($key = null)
    {
        $models = \Config::get('pow.models');
        if(isset($key)) {
            $config = $models['product_config']::where('product_id', $this->getId())
                ->where('key', $key)->first();
            return $config->value ?? null;
        }

        $models = \Config::get('pow.models');
        return $this->hasMany($models['product_config'], 'product_id', 'id');
    }

    /**
     * @return integer
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param int $qty
     * @return float
     */
    public function getOriginalPrice($qty = 0) : float
    {
        $totalPrice = $this->total_price;
        if($qty > 0) {
            $totalPrice = $this->total_price * $qty;
        }

        return $totalPrice;
    }

    /**
     * @param int $qty
     * @return float
     */
    public function getAdjustedPrice($qty = 0) : float
    {
        return $this->adjustment ?
            $this->adjustment->getAdjustedPrice($this->getOriginalPrice($qty), $qty)
            : $this->getOriginalPrice($qty);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @param SaveProductRequest $data
     */
    public function updateToken(SaveProductRequest $data)
    {
        if($this->token) {
            $this->token->delete();
        }

        if($data->wallet_token_type_id && $data->tokens) {
            $models = \Config::get('pow.models');

            $models['product_token']::create([
                'product_id' => $this->id,
                'wallet_token_type_id' => $data->wallet_token_type_id,
                'tokens' => $data->tokens
            ]);
        }
    }

    /**
     * @param SaveProductRequest $data
     *
     * @return bool
     */
    public function updateAdjustment(SaveProductRequest $data)
    {
        if(empty($data->criteria) && empty($data->adjustment)) {
            return false;
        }

        if($this->adjustment) {
            $this->adjustment->delete();
        }

        $models = \Config::get('pow.models');

        $models['product_adjustment_price']::create([
            'product_id' => $this->id,
            'criteria' => $data->criteria,
            'adjustment' => $data->adjustment
        ]);

        return true;
    }

    /**
     * @param SaveProductRequest $data
     */
    public function updateShop(SaveProductRequest $data)
    {
        $models = \Config::get('pow.models');
        $shopItems = $this->shop ?? [];
        $newShopItems = $data->shop ?? [];

        foreach($shopItems as $shopItem) {
            if(!isset($newShopItems[$shopItem->id])) {
                $shopItem->delete();
            }
        }

        foreach($newShopItems as $newShopItem) {
            $data = [
                'product_id' => $this->id,
                'quantity' => (int) $newShopItem['quantity'],
                'quantity_lock' => (bool) $newShopItem['quantity_lock'],
                'order_approval_required' => (bool) $newShopItem['order_approval_required'],
                'name' => $newShopItem['name'],
                'description' => $newShopItem['description'],
            ];

            if(isset($newShopItem['id'])) {
                $this->shop()
                    ->where('id', $newShopItem['id'])
                    ->update($data);
            } else {
                $models['product_shop']::create($data);
            }

        }
    }
}
