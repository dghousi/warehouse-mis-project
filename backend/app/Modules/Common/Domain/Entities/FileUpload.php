<?php

declare(strict_types=1);

namespace App\Modules\Common\Domain\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FileUpload extends Model
{
    protected $fillable = [
        'user_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'module',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
