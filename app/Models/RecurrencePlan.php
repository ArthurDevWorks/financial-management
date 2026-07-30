<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\RecurrenceFrequency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurrencePlan extends Model
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
        'frequency',
        'start_date',
        'end_date',
        'next_generation',
        'active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_method' => PaymentMethod::class,
        'frequency' => RecurrenceFrequency::class,
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'next_generation' => 'date:Y-m-d',
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function releases()
    {
        return $this->hasMany(Release::class, 'recurrence_id');
    }

    public function generateNextRelease(): ?Release
    {
        if (!$this->active) {
            return null;
        }

        if ($this->next_generation->gt(today())) {
            return null;
        }

        if ($this->next_generation->gt($this->end_date)) {
            $this->update(['active' => false]);
            return null;
        }

        $release = Release::create([
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'type' => $this->type,
            'payment_method' => $this->payment_method?->value,
            'status' => 'pending',
            'date' => $this->next_generation->format('Y-m-d'),
            'recurrence_id' => $this->id,
        ]);

        $nextDate = $this->frequency->addToDate(clone $this->next_generation);

        $updates = ['next_generation' => $nextDate->format('Y-m-d')];

        if ($nextDate->gt($this->end_date)) {
            $updates['active'] = false;
        }

        $this->update($updates);

        return $release;
    }
}
