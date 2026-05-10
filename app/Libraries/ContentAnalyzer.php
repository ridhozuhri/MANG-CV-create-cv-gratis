<?php

namespace App\Libraries;

class ContentAnalyzer
{
    private const CAPACITIES = [
        'classic' => [
            'education' => 4,
            'experience' => 6,
            'skills' => 10,
            'languages' => 8,
        ],
        'modern' => [
            'education' => 5,
            'experience' => 8,
            'skills' => 15,
            'languages' => 10,
        ],
        'sidebar' => [
            'education' => 3,
            'experience' => 4,
            'skills' => 6,
            'languages' => 5,
        ],
        'minimalist' => [
            'education' => 999,
            'experience' => 999,
            'skills' => 999,
            'languages' => 999,
        ],
        'professional' => [
            'education' => 6,
            'experience' => 12,
            'skills' => 12,
            'languages' => 10,
        ],
    ];

    public function analyze(array $cvData, string $template): array
    {
        $capacity = $this->getCapacity($template);
        $overflowSections = [];

        foreach (['education', 'experience', 'skills', 'languages'] as $section) {
            $items = $cvData[$section]['items'] ?? [];
            $count = is_array($items) ? count($items) : 0;
            $max = $capacity[$section] ?? 999;

            if ($count > $max) {
                $overflowSections[] = [
                    'section' => $section,
                    'current' => $count,
                    'max' => $max,
                    'overflow' => $count - $max,
                ];
            }
        }

        return [
            'has_overflow' => count($overflowSections) > 0,
            'overflow_sections' => $overflowSections,
            'capacity' => $capacity,
            'estimated_pages' => $this->estimatedPages($template, $cvData),
            'template' => $template,
        ];
    }

    public function estimatedPages(string $template, array $cvData): int
    {
        $totalChars = 0;

        $personal = $cvData['personal'] ?? [];
        if (! empty($personal['summary'])) {
            $totalChars += mb_strlen($personal['summary']);
        }

        $education = $cvData['education']['items'] ?? [];
        foreach ($education as $item) {
            $totalChars += mb_strlen(implode(' ', array_filter($item)));
        }

        $experience = $cvData['experience']['items'] ?? [];
        foreach ($experience as $item) {
            $totalChars += mb_strlen(implode(' ', array_filter($item)));
        }

        $skills = $cvData['skills']['items'] ?? [];
        $totalChars += count($skills) * 15;

        $languages = $cvData['languages']['items'] ?? [];
        $totalChars += count($languages) * 15;

        $charsPerPage = match ($template) {
            'classic' => 2500,
            'modern' => 2800,
            'sidebar' => 2200,
            'minimalist' => 3000,
            'professional' => 2600,
            default => 2500,
        };

        return max(1, (int) ceil($totalChars / $charsPerPage));
    }

    public function getCapacity(string $template): array
    {
        return self::CAPACITIES[$template] ?? [
            'education' => 999,
            'experience' => 999,
            'skills' => 999,
            'languages' => 999,
        ];
    }

    public function getRecommendedTemplate(array $cvData): string
    {
        $expCount = count($cvData['experience']['items'] ?? []);
        $eduCount = count($cvData['education']['items'] ?? []);
        $skillCount = count($cvData['skills']['items'] ?? []);

        if ($expCount > 8 || $skillCount > 12) {
            return 'professional';
        }

        if ($skillCount > 10 || $eduCount > 4) {
            return 'modern';
        }

        if ($expCount > 4 || $skillCount > 6) {
            return 'minimalist';
        }

        if ($expCount <= 2 && $skillCount <= 4) {
            return 'sidebar';
        }

        return 'classic';
    }

    public function getTemplateInfo(string $template): array
    {
        $names = [
            'classic' => 'Classic',
            'modern' => 'Modern',
            'sidebar' => 'Sidebar',
            'minimalist' => 'Minimalist',
            'professional' => 'Professional',
        ];

        $descriptions = [
            'classic' => 'Template klasik profesional dengan dua kolom',
            'modern' => 'Design modern dengan aksen warna biru',
            'sidebar' => 'Layout dengan sidebar di kiri',
            'minimalist' => 'Clean dan sederhana dengan banyak whitespace',
            'professional' => 'Format formal tradisional untuk senior',
        ];

        return [
            'name' => $names[$template] ?? ucfirst($template),
            'description' => $descriptions[$template] ?? '',
            'capacity' => $this->getCapacity($template),
        ];
    }
}
