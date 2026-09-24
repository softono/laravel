<?php

namespace App\Constants;

/** Blog categories; `blogs.category` stores the key. Same list as the Next app's BLOG_CATEGORIES. */
final class BlogCategory
{
    /** @var array<string, string> key => label */
    public const LABELS = [
        'machine-learning' => 'Machine Learning',
        'deep-learning' => 'Deep Learning',
        'natural-language-processing' => 'Natural Language Processing',
        'computer-vision' => 'Computer Vision',
        'generative-ai' => 'Generative AI',
        'ai-ethics' => 'AI Ethics',
        'robotics' => 'Robotics',
        'ai-tools' => 'AI Tools',
    ];

    public static function label(string $key): string
    {
        return self::LABELS[$key] ?? $key;
    }
}
