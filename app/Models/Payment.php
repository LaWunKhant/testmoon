<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // By default, Eloquent will assume your table name is the plural of the model name
    // (e.g., 'payments' for the 'Payment' model).
    // If your table name was different, you would specify it like this:
    // protected $table = 'my_payments_table';

    // Specify the columns that are mass assignable (you can fill these using an array)
    protected $fillable = [
        'tenant_id',
        'house_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
    ];

    // If you have columns that should not be mass assignable, use $guarded
    // protected $guarded = ['id'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }
}
