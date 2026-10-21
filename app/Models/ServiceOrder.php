<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'client_id', 'user_id', 'title', 'description', 'status', 'scheduled_at', 'completed_at', 'invoice_id'])]
class ServiceOrder extends Model
{
    public const SCHEDULED = 'scheduled';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const INVOICED = 'invoiced';
    public const CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ServiceOrderLog::class)->oldest();
    }

    // Órdenes acumuladas: completadas y aún sin factura
    public function scopePendingInvoice(Builder $query): Builder
    {
        return $query->where('status', self::COMPLETED)->whereNull('invoice_id');
    }

    public function getCurrencyAttribute(): ?string
    {
        return $this->items->first()?->currency;
    }

    public function getTotalAttribute(): string
    {
        return (string) $this->items->sum('subtotal');
    }

    public static function nextNumber(): string
    {
        return 'OS-'.now()->format('Y').'-'.str_pad((string) ((static::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }
}
