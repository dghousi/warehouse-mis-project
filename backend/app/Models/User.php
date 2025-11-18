<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'profile_photo_path',
        'job_title',
        'report_to_id',
        'email',
        'contact_number',
        'whatsapp_number',
        'password',
        'locale',
        'main_organization_id',
        'rights',
        'notifications',
        'enabled',
        'status',
        'remarks',
        'last_login_at',
        'user_form_path',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'report_to_id' => 'int',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locale' => 'string',
            'main_organization_id' => 'int',
            'rights' => 'string',
            'notifications' => 'boolean',
            'enabled' => 'boolean',
            'status' => 'string',
            'last_login_at' => 'datetime',
            'created_by' => 'int',
            'updated_by' => 'int',
            'deleted_by' => 'int',
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
            ->logOnly([
                'first_name',
                'last_name',
                'profile_photo_path',
                'job_title',
                'report_to_id',
                'email',
                'contact_number',
                'whatsapp_number',
                'locale',
                'main_organization_id',
                'rights',
                'notifications',
                'enabled',
                'status',
                'remarks',
                'last_login_at',
                'user_form_path',
                'created_by',
                'updated_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user')
            ->setDescriptionForEvent(fn (string $eventName): string => "User {$eventName}");
    }

    public function reportTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'report_to_id')->select(['id', 'first_name', 'last_name']);
    }
}
