<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// *** Ensure these are imported ***
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// *** Ensure the class implements HasMedia and uses InteractsWithMedia ***
class House extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia; // Use the trait

    protected $fillable = [
        'name',
        'address',
        'price',
        'description',
        'capacity',
        'owner_id',
        // You can remove 'photo_path' from $fillable now if you added it previously
        // 'photo_path',
    ];

    // You can remove 'photo_path' from $casts now if you added it previously
    protected $casts = [
        'price' => 'decimal:2',
        'capacity' => 'integer',
        // 'photo_path' => 'string',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    // You can define media collections here if needed
    // public function registerMediaCollections(): void
    // {
    //     $this->addMediaCollection('photos'); // Define a 'photos' collection
    // }
}
