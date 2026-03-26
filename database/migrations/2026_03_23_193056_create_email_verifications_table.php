// database/migrations/xxxx_create_email_verifications_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('email_verifications', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('email', 191);
        $table->string('system_ip', 45)->nullable();
        $table->tinyInteger('attempt_count')->default(0);
        $table->string('otp', 6);
        $table->timestamp('expires_at');
        $table->tinyInteger('is_used')->default(0);
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();

        $table->index(['user_id', 'email']);
        $table->index('email');

        $table->foreign('user_id')
              ->references('id')
              ->on('users')
              ->nullOnDelete();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};