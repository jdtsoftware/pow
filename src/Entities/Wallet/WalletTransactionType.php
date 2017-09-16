<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Wallet;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;

/**
 * Class Wallet.
 */
class WalletTransactionType extends Model
{
    use SoftDeletes;

    const DEBIT = 'debit';
    const CREDIT = 'credit';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallet_transaction_type';

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
}
