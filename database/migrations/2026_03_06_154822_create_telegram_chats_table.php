<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telegram_chats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            $table->foreignId('telegraph_chat_id')
                ->constrained('telegraph_chats')
                ->cascadeOnDelete();

            $table->string('type')->default('private');

            $table->string('chat_title')->nullable();
            $table->string('chat_username')->nullable();
            $table->string('chat_external_id')->nullable();

            $table->boolean('is_primary')->default(false);

            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('unread_count')->default(0);

            $table->timestamps();

            $table->index(['contact_id', 'is_primary']);
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_chats');
    }
};
