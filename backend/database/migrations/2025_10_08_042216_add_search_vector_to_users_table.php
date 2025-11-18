<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add a generated FULLTEXT column (or a normal column)
        // MySQL cannot auto-generate content into a FULLTEXT index directly using triggers
        // but you *can* use a generated column:
        DB::statement("
            ALTER TABLE users 
            ADD COLUMN search_vector TEXT 
            GENERATED ALWAYS AS (CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) STORED
        ");

        // 2. Add FULLTEXT index for fast searching
        DB::statement("
            ALTER TABLE users 
            ADD FULLTEXT INDEX users_search_vector_idx (search_vector)
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE users 
            DROP INDEX users_search_vector_idx
        ");

        DB::statement("
            ALTER TABLE users 
            DROP COLUMN search_vector
        ");
    }
};
