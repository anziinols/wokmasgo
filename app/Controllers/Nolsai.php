<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Nolsai extends BaseController
{
    /**
     * Display the Nolsai AI Chat page
     *
     * @return string
     */
    public function index(): string
    {
        $viewData = [
            'csrf_token_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
            'base_url' => base_url(),
            'save_config_url' => base_url('nolsai/save-config'),
            'load_config_url' => base_url('nolsai/load-config'),
            'test_connection_url' => base_url('nolsai/test-connection'),
            'chat_url' => base_url('nolsai/chat'),
            'visitors_url' => base_url('nolsai/visitors'),
            'logo_url' => base_url('assets/images/nolsai-logo.png'),
        ];

        $mainContent = view('nolsai_content', $viewData);
        $additionalCss = view('nolsai_styles', $viewData);
        $additionalJs = '<script>' . view('nolsai_scripts', $viewData) . '</script>';

        $templateData = [
            'page_title' => 'Nolsai - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs,
        ];

        return view('public_template', $templateData);
    }

    /**
     * Save API configuration to session
     *
     * @return ResponseInterface
     */
    public function saveConfig(): ResponseInterface
    {
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method',
            ])->setStatusCode(405);
        }

        $input = $this->request->getJSON(true) ?? [];
        $session = session();
        $existing = $session->get('ai_chat_config') ?? [
            'baseUrl' => '',
            'apiKey' => '',
            'modelId' => '',
            'systemPrompt' => '',
        ];

        $cfg = [
            'baseUrl' => trim($input['baseUrl'] ?? $existing['baseUrl'] ?? ''),
            'apiKey' => array_key_exists('apiKey', $input) && $input['apiKey'] !== ''
                ? $input['apiKey']
                : ($existing['apiKey'] ?? ''),
            'modelId' => trim($input['modelId'] ?? $existing['modelId'] ?? ''),
            'systemPrompt' => $input['systemPrompt'] ?? $existing['systemPrompt'] ?? '',
        ];

        $session->set('ai_chat_config', $cfg);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Configuration saved',
        ]);
    }

    /**
     * Load API configuration from session
     *
     * @return ResponseInterface
     */
    public function loadConfig(): ResponseInterface
    {
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method',
            ])->setStatusCode(405);
        }

        $cfg = session()->get('ai_chat_config') ?? [
            'baseUrl' => '',
            'apiKey' => '',
            'modelId' => '',
            'systemPrompt' => '',
        ];

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Configuration loaded',
            'data' => [
                'config' => [
                    'baseUrl' => $cfg['baseUrl'] ?? '',
                    'modelId' => $cfg['modelId'] ?? '',
                    'systemPrompt' => $cfg['systemPrompt'] ?? '',
                    'hasApiKey' => !empty($cfg['apiKey']),
                ],
            ],
        ]);
    }

    /**
     * Test API connection
     *
     * @return ResponseInterface
     */
    public function testConnection(): ResponseInterface
    {
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request method',
            ])->setStatusCode(405);
        }

        $cfg = session()->get('ai_chat_config') ?? [];

        if (empty($cfg['baseUrl']) || empty($cfg['apiKey']) || empty($cfg['modelId'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Fill Base URL, API Key, and Model first.',
            ]);
        }

        $ch = curl_init($cfg['baseUrl']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $cfg['apiKey'],
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $cfg['modelId'],
                'messages' => [['role' => 'user', 'content' => 'Hi']],
                'max_tokens' => 1,
                'stream' => false,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => "cURL error: $error",
            ]);
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Connection successful',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => "HTTP $httpCode: " . substr((string) $response, 0, 200),
        ]);
    }

    /**
     * Stream chat response via SSE
     *
     * @return void
     */
    public function chat(): void
    {
        if (!$this->request->is('post')) {
            $this->response->setStatusCode(405);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        $cfg = session()->get('ai_chat_config') ?? [];

        if (empty($cfg['baseUrl']) || empty($cfg['apiKey']) || empty($cfg['modelId'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'API not configured.']);
            return;
        }

        $input = $this->request->getJSON(true) ?? [];
        $apiMessages = [];

        if (!empty($cfg['systemPrompt'])) {
            $apiMessages[] = ['role' => 'system', 'content' => $cfg['systemPrompt']];
        }

        $messages = $input['messages'] ?? [];
        $maxImages = 10;
        $maxSizeBytes = 10 * 1024 * 1024;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($messages as $msg) {
            if (!isset($msg['content']) || !is_array($msg['content'])) {
                $apiMessages[] = $msg;
                continue;
            }

            $validatedContent = [];
            $imageCount = 0;

            foreach ($msg['content'] as $part) {
                if (($part['type'] ?? '') === 'image_url' && isset($part['image_url']['url'])) {
                    $imageCount++;
                    if ($imageCount > $maxImages) {
                        header('Content-Type: application/json');
                        echo json_encode(['status' => 'error', 'message' => "Maximum $maxImages images allowed per message."]);
                        return;
                    }

                    $url = $part['image_url']['url'];
                    if (preg_match('/^data:(image\/[a-z]+);base64,/', $url, $m)) {
                        $mimeType = $m[1];
                        if (!in_array($mimeType, $allowedTypes, true)) {
                            header('Content-Type: application/json');
                            echo json_encode(['status' => 'error', 'message' => "Unsupported image type: $mimeType. Allowed: JPEG, PNG, GIF, WebP."]);
                            return;
                        }

                        $base64Data = substr($url, strpos($url, ',') + 1);
                        $decodedSize = (int) (strlen($base64Data) * 3 / 4);
                        if ($decodedSize > $maxSizeBytes) {
                            $sizeMB = round($decodedSize / (1024 * 1024), 1);
                            header('Content-Type: application/json');
                            echo json_encode(['status' => 'error', 'message' => "Image too large ($sizeMB MB). Maximum 10MB per image."]);
                            return;
                        }
                    }
                }
                $validatedContent[] = $part;
            }

            $apiMessages[] = ['role' => $msg['role'] ?? 'user', 'content' => $validatedContent];
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $buffer = '';
        $ch = curl_init($cfg['baseUrl']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $cfg['apiKey'],
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $cfg['modelId'],
                'messages' => $apiMessages,
                'stream' => true,
            ]),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_BUFFERSIZE => 256,
            CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$buffer) {
                $buffer .= $chunk;
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    $trimmed = trim($line);
                    if (str_starts_with($trimmed, 'data:')) {
                        $data = trim(substr($trimmed, 5));
                        if ($data === '[DONE]') {
                            echo "data: [DONE]\n\n";
                            flush();
                            continue;
                        }
                        echo "data: $data\n\n";
                        flush();
                    }
                }
                return strlen($chunk);
            },
        ]);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error || ($httpCode < 200 || $httpCode >= 300)) {
            $errMsg = $error ?: "HTTP $httpCode";
            echo 'data: ' . json_encode(['error' => $errMsg]) . "\n\n";
            flush();
        }

        exit;
    }

    /**
     * Return visitor analytics from CSV
     *
     * @return ResponseInterface
     */
    public function visitors(): ResponseInterface
    {
        $csvPath = ROOTPATH . 'public/uploads/nolsai_visitors.csv';

        if (!is_file($csvPath)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Visitors data file not found',
            ])->setStatusCode(404);
        }

        $rows = [];
        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unable to read visitors data',
            ])->setStatusCode(500);
        }

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = [
                'user_agent' => $data[0] ?? '',
                'ip' => $data[1] ?? '',
                'question' => $data[2] ?? '',
                'source' => $data[3] ?? '',
                'timestamp' => $data[4] ?? '',
            ];
        }

        fclose($handle);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Visitors loaded',
            'data' => [
                'total' => count($rows),
                'visitors' => $rows,
            ],
        ]);
    }
}
