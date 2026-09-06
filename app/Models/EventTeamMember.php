<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Seorang dalam pasukan bertugas program — dokumen dalaman.
 *
 * Sengaja teks bebas, bukan pautan ke akaun pengguna: AJK masjid dan
 * sukarelawan tempatan tidak mempunyai akaun dalam sistem ini.
 */
class EventTeamMember extends Model
{
    protected $fillable = ['event_id', 'sort_order', 'role', 'name', 'phone'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
