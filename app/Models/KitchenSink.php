<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'status',
    'category',
    'visibility',
    'owner_email',
    'support_phone',
    'website',
    'price',
    'progress',
    'priority',
    'is_active',
    'requires_follow_up',
    'published_at',
    'event_date',
    'event_time',
    'reminder_at',
    'favorite_color',
    'summary',
    'notes',
    'hero_image',
    'attachments',
    'tags',
    'audiences',
    'review_groups',
    'delivery_channels',
    'metadata',
    'stats',
    'faq_items',
    'content',
    'markdown_content',
    'code_snippet',
])]
class KitchenSink extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'progress' => 'integer',
            'priority' => 'integer',
            'is_active' => 'boolean',
            'requires_follow_up' => 'boolean',
            'published_at' => 'datetime',
            'event_date' => 'date',
            'reminder_at' => 'datetime',
            'attachments' => 'array',
            'tags' => 'array',
            'audiences' => 'array',
            'review_groups' => 'array',
            'delivery_channels' => 'array',
            'metadata' => 'array',
            'stats' => 'array',
            'faq_items' => 'array',
        ];
    }
}
