<?php

namespace App\Controllers;

class Landing extends BaseController
{
    /**
     * Display the landing page with all available apps/modules
     *
     * @return string
     */
    public function index(): string
    {
        // Prepare data for the landing page content
        $contentData = [
            'apps' => $this->getAvailableApps()
        ];

        // Load the landing content view
        $mainContent = view('landing_content', $contentData);

        // Load additional CSS and JS
        $additionalCss = view('landing_styles');
        $additionalJs = '<script>' . view('landing_scripts') . '</script>';

        // Prepare data for the template
        $templateData = [
            'page_title' => 'WOKMASGO - Home',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }
    
    /**
     * Get list of available applications/modules
     * 
     * @return array
     */
    private function getAvailableApps(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Markdown Viewer',
                'description' => 'Convert and preview Markdown files',
                'icon' => 'fab fa-markdown',
                'url' => base_url('markdown-viewer'),
                'color' => 'maroon',
                'gradient' => 'gradient-maroon-gold'
            ],
        ];
    }
}

