<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kitchen_sinks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->string('category')->nullable();
            $table->string('visibility')->default('internal');
            $table->string('owner_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('website')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedTinyInteger('progress')->default(25);
            $table->unsignedTinyInteger('priority')->default(3);
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_follow_up')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->dateTime('reminder_at')->nullable();
            $table->string('favorite_color')->nullable();
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('attachments')->nullable();
            $table->json('tags')->nullable();
            $table->json('audiences')->nullable();
            $table->json('review_groups')->nullable();
            $table->json('delivery_channels')->nullable();
            $table->json('metadata')->nullable();
            $table->json('stats')->nullable();
            $table->json('faq_items')->nullable();
            $table->longText('content')->nullable();
            $table->longText('markdown_content')->nullable();
            $table->longText('code_snippet')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_sinks');
    }
};
