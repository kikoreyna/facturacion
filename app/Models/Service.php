<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'name', 'description', 'unit', 'is_active'])]
class Service extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tariffLists(): BelongsToMany
    {
        return $this->belongsToMany(TariffList::class, 'service_tariff_list')
            ->withPivot('price', 'currency')
            ->withTimestamps();
    }

    public function tariffFor(TariffList|int $tariffList): ?object
    {
        $id = $tariffList instanceof TariffList ? $tariffList->id : $tariffList;

        return $this->tariffLists()->whereKey($id)->first()?->pivot;
    }

    public function priceFor(TariffList|int $tariffList): ?string
    {
        return $this->tariffFor($tariffList)?->price;
    }
}
