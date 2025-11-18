<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Entities;

use App\Models\ActiveUser;
use App\Models\CreatedBy;
use App\Models\DeletedBy;
use App\Models\ReportTo;
use App\Models\UpdatedBy;
use App\Modules\Common\Domain\Entities\FileUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class User extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'profile_photo_path',
        'job_title',
        'email',
        'email_verified_at',
        'contact_number',
        'whatsapp_number',
        'password',
        'locale',
        'rights',
        'notifications',
        'enabled',
        'status',
        'remarks',
        'last_login_at',
        'user_form_path',
        'report_to_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'token',
        'created_at',
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locale' => 'string',
            'rights' => 'string',
            'notifications' => 'boolean',
            'enabled' => 'boolean',
            'status' => 'string',
            'last_login_at' => 'datetime',
            'report_to_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'deleted_by' => 'integer',
            'created_at' => 'datetime',
            'user_id' => 'integer',
            'last_activity' => 'integer',
        ];
    }

    public function prunable()
    {
        return self::onlyTrashed()
            ->where('deleted_at', '<', now()->subMonths(6));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName(class_basename($this))
            ->setDescriptionForEvent(fn (string $eventName): string => "User {$eventName}");
    }

    public function reportTo(): BelongsTo
    {
        return $this->belongsTo(ReportTo::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CreatedBy::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(UpdatedBy::class);
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(DeletedBy::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function activeUsers(): HasMany
    {
        return $this->hasMany(ActiveUser::class);
    }

    public function fileUploads(): HasMany
    {
        return $this->hasMany(FileUpload::class);
    }

    public function hasRelatedRecords(): bool
    {
        return $this->users()->exists() || $this->activeUsers()->exists() || $this->fileUploads()->exists();
    }
}
