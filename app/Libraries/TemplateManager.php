<?php

namespace App\Libraries;

class TemplateManager
{
    public function renderToHtml(array $sections, string $template = 'classic', array $options = []): string
    {
        return view('templates/' . $template, [
            'sections' => $sections,
            'options' => $options,
        ]);
    }
}
