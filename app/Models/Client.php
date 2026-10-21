<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'tax_id', 'email', 'phone', 'address', 'tariff_list_id', 'is_active'])]
class Client extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tariffList(): BelongsTo
    {
        return $this->belongsTo(TariffList::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
