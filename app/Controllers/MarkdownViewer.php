<?php

namespace App\Controllers;

class MarkdownViewer extends BaseController
{
    /**
     * Display the Markdown Viewer page
     * All Markdown processing is done client-side using marked.js
     *
     * @return string
     */
    public function index(): string
    {
        // Load the markdown viewer content view
        $mainContent = view('markdown_viewer_content');

        // Load additional CSS and JS
        $additionalCss = view('markdown_viewer_styles');
        $additionalJs = '<script>' . view('markdown_viewer_scripts') . '</script>';

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Markdown Viewer - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }
}

