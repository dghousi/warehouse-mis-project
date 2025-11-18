<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SORTABLE_COLUMNS = [
        'id',
        'first_name',
        'email',
        'created_at',
    ];

    private const FILTERS = [
        'main_organization_id' => [], // dynamic
        'rights' => ['create', 'review', 'approval'],
        'status' => ['pending', 'approved', 'rejected', 'uploadForm'],
        'enabled' => [0, 1],
        'notifications' => [0, 1],
    ];

    private const SEARCHABLE_COLUMNS = [
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'email' => 'email',
        'jobTitle' => 'job_title',
        'contactNumber' => 'contact_number',
        'whatsappNumber' => 'whatsapp_number',
        'remarks' => 'remarks',
    ];

    private const FIELDABLE_COLUMNS = [
        'id' => 'id',
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'profilePhotoPath' => 'profile_photo_path',
        'jobTitle' => 'job_title',
        'reportToId' => 'report_to_id',
        'email' => 'email',
        'emailVerifiedAt' => 'email_verified_at',
        'contactNumber' => 'contact_number',
        'whatsappNumber' => 'whatsapp_number',
        'locale' => 'locale',
        'mainOrganizationId' => 'main_organization_id',
        'rights' => 'rights',
        'notifications' => 'notifications',
        'enabled' => 'enabled',
        'status' => 'status',
        'remarks' => 'remarks',
        'lastLoginAt' => 'last_login_at',
        'userFormPath' => 'user_form_path',
        'createdBy' => 'created_by',
        'updatedBy' => 'updated_by',
        'deletedBy' => 'deleted_by',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at',
    ];

    private const BOOLEAN_FIELDS = [
        'notifications',
        'enabled',
    ];

    private const CAST_IDS = [
        'reportToId',
        'mainOrganizationId',
        'createdBy',
        'updatedBy',
        'deletedBy',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('job_title');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('password');
            $table->enum('locale', ['en', 'dr', 'ps'])->default('en');
            $table->enum('rights', ['create', 'review', 'approval'])->default('review');
            $table->boolean('notifications')->default(true);
            $table->boolean('enabled')->default(true);
            $table->enum('status', ['pending', 'approved', 'rejected', 'uploadForm'])->default('pending');
            $table->text('remarks')->nullable();
            $table->datetime('last_login_at')->nullable();
            $table->string('user_form_path')->nullable();
            $table->foreignId('report_to_id')->default(0)->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
