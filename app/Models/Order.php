<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $order_code
 * @property string $address
 * @property string $shipping_date
 * @property int $status
 *
 * @property object $products
 */
class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_id')->with('product');
    }

    public static function generateOrderCode(): string
    {
        $code = 'o_' . rand(100000000, 999999999);

        if (self::query()->where(['order_code' => $code])->first())
        {
            $code = self::generateOrderCode();
        }

        return $code;
    }
}
