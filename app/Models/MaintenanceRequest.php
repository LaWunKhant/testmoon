<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'description',
        'status',
        'scheduled_date',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
    ];

    // Define the relationship with the House model
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    // Define the relationship with the Tenant model
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
