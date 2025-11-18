<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SORTABLE_COLUMNS = [
        'id',
        'original_name',
        'created_at',
    ];

    private const FILTERS = [];

    private const SEARCHABLE_COLUMNS = [
        'originalName' => 'original_name',
        'path' => 'path',
        'mimeType' => 'mime_type',
    ];

    private const FIELDABLE_COLUMNS = [
        'id' => 'id',
        'userId' => 'user_id',
        'path' => 'path',
        'originalName' => 'original_name',
        'mimeType' => 'mime_type',
        'module' => 'module',
        'size' => 'size',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    private const BOOLEAN_FIELDS = [];

    private const CAST_IDS = [
        'userId',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->string('module')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};
