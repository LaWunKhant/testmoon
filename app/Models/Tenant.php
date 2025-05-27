<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// *** Import the Authenticatable contract and trait ***
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as AuthenticatableTrait; // Alias the trait to avoid naming conflict with Model

// *** Implement Authenticatable interface and use the trait ***
// We are changing 'extends Model' to 'extends AuthenticatableTrait'
class Tenant extends AuthenticatableTrait // Use the trait providing auth methods
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'house_id',
        'rent',
        'password', // Include password as fillable (needed for create during registration)
        // 'remember_token', // Add remember_token if you include it in the migration
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'rent' => 'float',
        'house_id' => 'integer',
        'password' => 'hashed', // *** Cast password to 'hashed' for automatic hashing when setting ***
        // 'email_verified_at' => 'datetime', // If you add email verification later
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    // The AuthenticatableTrait provides the necessary methods (getAuthIdentifierName, getAuthIdentifier, getAuthPassword, getRememberToken, setRememberToken)
}
