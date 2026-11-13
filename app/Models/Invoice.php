<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property InvoiceStatus $status
 * @property Carbon $issue_date
 * @property Carbon $due_date
 * @property Carbon|null $reminded_at
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'sequence', 'number', 'status',
        'issue_date', 'due_date', 'currency', 'notes',
        'subtotal', 'discount', 'tax_rate', 'tax', 'total',
        'amount_paid', 'sent_at', 'paid_at', 'reminded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'issue_date' => 'date',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'reminded_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_on');
    }

    public function balance(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    public function isOverdue(): bool
    {
        return $this->status === InvoiceStatus::Sent
            && $this->balance() > 0
            && $this->due_date->lt(today());
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::Sent)
            ->whereDate('due_date', '<', today())
            ->whereColumn('amount_paid', '<', 'total');
    }

    /**
     * Overdue invoices with an email-addressed client that haven't been reminded this week.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeDueForReminder(Builder $query): Builder
    {
        return $this->scopeOverdue($query)
            ->whereHas('client', fn (Builder $c) => $c->whereNotNull('email')->where('email', '!=', ''))
            ->where(fn (Builder $q) => $q->whereNull('reminded_at')
                ->orWhere('reminded_at', '<', now()->subDays(7)));
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            // Default: everything except cancelled, which is not money owed.
            null, '' => $query->where('status', '!=', InvoiceStatus::Cancelled->value),
            // Explicit choice: include cancelled invoices as well.
            'all' => $query,
            'overdue' => $this->scopeOverdue($query),
            default => $query->where('status', $status),
        };
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('number', 'like', "%{$term}%")
                ->orWhereHas('client', fn (Builder $c) => $c->where('name', 'like', "%{$term}%"));
        });
    }
}
