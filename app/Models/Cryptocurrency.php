<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cryptocurrency extends Model
{
    protected $fillable = ['cmc_id', 'name', 'symbol', 'slug'];

    public function priceHistories()
    {
        return $table->hasMany(PriceHistory::class);
    }
}
