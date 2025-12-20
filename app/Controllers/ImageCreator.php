<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class ImageCreator extends BaseController
{
    /**
     * Display the Image Creator main page (selection page)
     *
     * @return string
     */
    public function index(): string
    {
        // Prepare data for views
        $viewData = [
            'csrf_token_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
            'base_url' => base_url()
        ];

        // Load the image creator content view
        $mainContent = view('image_creator_content', $viewData);

        // Load additional CSS
        $additionalCss = view('image_creator_styles', $viewData);

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Image Creator - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => ''
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }

    /**
     * Display Logo options page
     *
     * @return string
     */
    public function logo(): string
    {
        // Prepare data for views
        $viewData = [
            'base_url' => base_url()
        ];

        // Load the logo options view
        $mainContent = view('image_creator_logo', $viewData);

        // Load additional CSS
        $additionalCss = view('image_creator_styles', $viewData);

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Logo Creator - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => ''
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }

    /**
     * Display Logo creation page
     *
     * @return string
     */
    public function logoCreate(): string
    {
        // Prepare data for views
        $viewData = [
            'csrf_token_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
            'base_url' => base_url()
        ];

        // Load the logo create view
        $mainContent = view('image_creator_logo_create', $viewData);

        // Load additional CSS and JS
        $additionalCss = view('image_creator_styles', $viewData);
        $scriptContent = view('image_creator_scripts', $viewData);

        // Add cache busting comment to force reload
        $additionalJs = '<script>// Version: ' . time() . "\n" . $scriptContent . '</script>';

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Create Logo - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }

    /**
     * Display Logo edit page
     *
     * @return string
     */
    public function logoEdit(): string
    {
        // Prepare data for views
        $viewData = [
            'csrf_token_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
            'base_url' => base_url()
        ];

        // Load the logo edit view
        $mainContent = view('image_creator_logo_edit', $viewData);

        // Load additional CSS and JS
        $additionalCss = view('image_creator_styles', $viewData);
        $scriptContent = view('image_creator_scripts', $viewData);

        // Add cache busting comment to force reload
        $additionalJs = '<script>// Version: ' . time() . "\n" . $scriptContent . '</script>';

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Edit Logo - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }

    /**
     * Display Flyer options page
     *
     * @return string
     */
    public function flyer(): string
    {
        // Prepare data for views
        $viewData = [
            'base_url' => base_url()
        ];

        // Load the flyer options view
        $mainContent = view('image_creator_flyer', $viewData);

        // Load additional CSS
        $additionalCss = view('image_creator_styles', $viewData);

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Advertisement Flyer Creator - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => ''
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }

    /**
     * Display Flyer creation page
     *
     * @return string
     */
    public function flyerCreate(): string
    {
        // Prepare data for views
        $viewData = [
            'csrf_token_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
            'base_url' => base_url()
        ];

        // Load the flyer create view
        $mainContent = view('image_creator_flyer_create', $viewData);

        // Load additional CSS and JS
        $additionalCss = view('image_creator_styles', $viewData);
        $scriptContent = view('image_creator_scripts', $viewData);

        // Add cache busting comment to force reload
        $additionalJs = '<script>// Version: ' . time() . "\n" . $scriptContent . '</script>';

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Create Flyer - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }

    /**
     * Display Flyer edit page
     *
     * @return string
     */
    public function flyerEdit(): string
    {
        // Prepare data for views
        $viewData = [
            'csrf_token_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
            'base_url' => base_url()
        ];

        // Load the flyer edit view
        $mainContent = view('image_creator_flyer_edit', $viewData);

        // Load additional CSS and JS
        $additionalCss = view('image_creator_styles', $viewData);
        $scriptContent = view('image_creator_scripts', $viewData);

        // Add cache busting comment to force reload
        $additionalJs = '<script>// Version: ' . time() . "\n" . $scriptContent . '</script>';

        // Prepare data for the template
        $templateData = [
            'page_title' => 'Edit Flyer - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];

        // Return the complete page using the public template
        return view('public_template', $templateData);
    }

    /**
     * Generate image via OpenRouter API (backend endpoint)
     * This keeps the API key secure on the server side
     *
     * @return ResponseInterface
     */
    public function generate(): ResponseInterface
    {
        // Only accept POST requests
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid request method'
            ])->setStatusCode(405);
        }

        try {
            // Get the API key from environment
            $apiKey = getenv('openrouter.apiKey');

            if (empty($apiKey)) {
                throw new \Exception('OpenRouter API key not configured');
            }

            // Get request data
            $requestData = $this->request->getJSON(true);

            if (empty($requestData)) {
                throw new \Exception('No request data provided');
            }

            // Initialize cURL
            $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');

            // Set cURL options
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($requestData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                    'HTTP-Referer: ' . base_url(),
                    'X-Title: WOKMASGO Image Creator'
                ],
                CURLOPT_TIMEOUT => 120, // 2 minutes timeout for image generation
            ]);

            // Execute request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Check for cURL errors
            if ($curlError) {
                throw new \Exception('cURL error: ' . $curlError);
            }

            // Decode response
            $responseData = json_decode($response, true);

            // Check HTTP status code
            if ($httpCode !== 200) {
                $errorMessage = $responseData['error']['message'] ?? 'Unknown error';
                throw new \Exception('API request failed: ' . $errorMessage);
            }

            // Return successful response
            return $this->response->setJSON([
                'success' => true,
                'data' => $responseData,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Image Creator API Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'csrf_token' => csrf_hash()
            ])->setStatusCode(500);
        }
    }
}
