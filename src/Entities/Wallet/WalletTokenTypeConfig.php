<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Wallet;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;

/**
 * Class WalletTokenTypeConfig.
 */
class WalletTokenTypeConfig extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallet_token_type_config';

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
        'wallet_token_type_id',
        'key',
        'value',
        'created_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
