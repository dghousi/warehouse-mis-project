<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {

        // 1. Add the tsvector column using raw SQL
        DB::statement('ALTER TABLE users ADD COLUMN search_vector tsvector');

        // 2. Create a GIN index for fast searching
        DB::statement('CREATE INDEX users_search_vector_idx ON users USING GIN (search_vector)');

        // 3. Create the trigger function to auto-update the search vector
        DB::statement("
            CREATE OR REPLACE FUNCTION users_search_vector_update() RETURNS trigger AS $$
            BEGIN
                NEW.search_vector :=
                    to_tsvector('english', coalesce(NEW.first_name,'') || ' ' || coalesce(NEW.last_name,''));
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;
        ");

        // 4. Create the trigger itself
        DB::statement('
            CREATE TRIGGER users_search_vector_trigger
            BEFORE INSERT OR UPDATE ON users
            FOR EACH ROW
            EXECUTE FUNCTION users_search_vector_update();
        ');

        // 5. Backfill existing rows
        DB::statement("
            UPDATE users
            SET search_vector = to_tsvector('english', coalesce(first_name,'') || ' ' || coalesce(last_name,''))
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS users_search_vector_trigger ON users');
        DB::statement('DROP FUNCTION IF EXISTS users_search_vector_update');
        DB::statement('DROP INDEX IF EXISTS users_search_vector_idx');

        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS search_vector');
    }
};
