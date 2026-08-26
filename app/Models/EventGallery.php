<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGallery extends Model
{
    protected $table = 'event_galleries';

    protected $fillable = ['event_id', 'image_path', 'caption', 'sort_order', 'is_approved', 'uploaded_by'];

    protected function casts(): array
    {
        return ['is_approved' => 'boolean'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('is_approved', true);
    }

    public function url(): string
    {
        return asset('storage/'.$this->image_path);
    }

    /** Susunan paparan yang ditetapkan admin. */
    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }
}
