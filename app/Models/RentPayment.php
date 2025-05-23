<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo class

// Ensure Tenant model is imported

class RentPayment extends Model
{
    use HasFactory;

    // ... other properties like $casts, $fillable, $guarded, $table ...

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'paid' => 'boolean',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
    ];

    // *** Add the tenant relationship method here ***
    /**
     * Get the tenant that owns the rent payment.
     */
    /**
     * Get the tenant that owns the rent payment.
     */
    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    // You might also need a house relationship on the RentPayment model if a payment
    // is directly linked to a house as well, or if the tenant can rent multiple houses.
    // However, the command is trying to load 'tenant.house', which assumes the house
    // relationship is on the Tenant model. Let's focus on adding the tenant relationship first.
    // public function house(): BelongsTo
    // {
    //     return $this->belongsTo(House::class);
    // }

}
