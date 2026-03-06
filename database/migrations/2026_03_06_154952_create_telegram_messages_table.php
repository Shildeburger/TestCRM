<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('telegram_chat_id')
                ->constrained('telegram_chats')
                ->cascadeOnDelete();

            $table->string('direction'); // incoming / outgoing
            $table->string('from_role')->nullable(); // client / manager / bot

            $table->text('text')->nullable();

            $table->string('telegram_message_id')->nullable()->index();

            $table->string('attachment_type')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable()->index();
            $table->timestamp('read_at')->nullable()->index();

            $table->timestamps();

            $table->index(['telegram_chat_id', 'direction']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
