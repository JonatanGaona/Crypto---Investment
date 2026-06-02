<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $fillable = ['cryptocurrency_id', 'price', 'percent_change_24h', 'volume_24h', 'recorded_at'];

    public function cryptocurrency()
    {
        return $this->belongsTo(Cryptocurrency::class);
    }
}
