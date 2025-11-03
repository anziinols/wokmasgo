<?php

namespace App\Controllers;

class ImageCreator extends BaseController
{
    /**
     * Display the Image Creator page
     * Image generation is done client-side using Gemini Nano Banana model
     *
     * @return string
     */
    public function index(): string
    {
        // Load the image creator content view
        $mainContent = view('image_creator_content');

        // Load additional CSS and JS
        $additionalCss = view('image_creator_styles');
        $scriptContent = view('image_creator_scripts');

        // Add cache busting comment to force reload
        $additionalJs = '<script>// Version: ' . time() . "\n" . $scriptContent . '</script>';

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Image Creator - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }
}
