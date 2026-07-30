<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\ReleaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Release extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'title',
        'description',
        'amount',
        'type',
        'payment_method',
        'status',
        'installment_number',
        'total_installments',
        'parent_id',
        'recurrence_id',
        'date',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status' => ReleaseStatus::class,
        'date' => 'date:Y-m-d',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Release::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Release::class, 'parent_id');
    }

    public function recurrencePlan()
    {
        return $this->belongsTo(RecurrencePlan::class, 'recurrence_id');
    }

    public function scopeRevenue($query)
    {
        return $query->where('type', 'revenue');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeInstallment($query)
    {
        return $query->whereNotNull('installment_number');
    }

    public function scopeRecurring($query)
    {
        return $query->whereNotNull('recurrence_id');
    }

    public function isInstallment(): bool
    {
        return !is_null($this->installment_number);
    }

    public function isRecurring(): bool
    {
        return !is_null($this->recurrence_id);
    }
}
