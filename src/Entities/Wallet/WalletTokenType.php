<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Wallet;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;

/**
 * Class Wallet.
 */
class WalletTokenType extends Model implements \JDT\Pow\Interfaces\Entities\WalletTokenType
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallet_token_type';

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
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null $key
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|null
     */
    public function config($key = null)
    {
        $models = \Config::get('pow.models');
        if(isset($key)) {
            $config = $models['wallet_token_type_config']::where('wallet_token_type_id', $this->getId())
                ->where('key', $key)->first();
            return $config->value ?? null;
        }

        $models = \Config::get('pow.models');
        return $this->hasMany($models['wallet_token_type_config'], 'wallet_token_type_id', 'id');
    }
}
