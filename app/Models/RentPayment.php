<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentPayment extends Model
{
    // protected $fillable = [
    //     'tenant_id',
    //     'due_date',
    //     'amount',
    //     'paid',
    // ];

    protected $guarded = ['id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
