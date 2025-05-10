<?php

namespace PinIndia\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostOffice extends Model
{
    public $timestamps = false;
    protected $fillable = ['pincode_id', 'name', 'office', 'type', 'delivery', 'latitude', 'longitude'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_post_offices' : 'post_offices';
    }

    public function pincode(): BelongsTo
    {
        return $this->belongsTo(Pincode::class);
    }
}
