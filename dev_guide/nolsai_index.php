<?php
session_start();

header('X-Content-Type-Options: nosniff');

function get_config() {
    return $_SESSION['ai_chat_config'] ?? [
        'baseUrl' => '',
        'apiKey' => '',
        'modelId' => '',
        'systemPrompt' => '',
    ];
}

function save_config($cfg) {
    $_SESSION['ai_chat_config'] = $cfg;
}

// --- Handle API actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'save_config') {
        save_config([
            'baseUrl' => trim($input['baseUrl'] ?? ''),
            'apiKey' => $input['apiKey'] ?? '',
            'modelId' => trim($input['modelId'] ?? ''),
            'systemPrompt' => $input['systemPrompt'] ?? '',
        ]);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($action === 'load_config') {
        $cfg = get_config();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'config' => [
            'baseUrl' => $cfg['baseUrl'],
            'modelId' => $cfg['modelId'],
            'systemPrompt' => $cfg['systemPrompt'],
            'hasApiKey' => !empty($cfg['apiKey']),
        ]]);
        exit;
    }

    if ($action === 'test_connection') {
        $cfg = get_config();
        if (empty($cfg['baseUrl']) || empty($cfg['apiKey']) || empty($cfg['modelId'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Fill Base URL, API Key, and Model first.']);
            exit;
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

        header('Content-Type: application/json');
        if ($error) {
            echo json_encode(['status' => 'error', 'message' => "cURL error: $error"]);
        } elseif ($httpCode >= 200 && $httpCode < 300) {
            echo json_encode(['status' => 'ok', 'message' => 'Connection successful']);
        } else {
            echo json_encode(['status' => 'error', 'message' => "HTTP $httpCode: " . substr($response, 0, 200)]);
        }
        exit;
    }

    if ($action === 'chat') {
        $cfg = get_config();
        if (empty($cfg['baseUrl']) || empty($cfg['apiKey']) || empty($cfg['modelId'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'API not configured.']);
            exit;
        }

        $apiMessages = [];
        if (!empty($cfg['systemPrompt'])) {
            $apiMessages[] = ['role' => 'system', 'content' => $cfg['systemPrompt']];
        }

        $messages = $input['messages'] ?? [];
        $maxImages = 10;
        $maxSizeBytes = 10 * 1024 * 1024; // 10MB per image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($messages as $msg) {
            if (!is_array($msg['content'])) {
                $apiMessages[] = $msg;
                continue;
            }

            $validatedContent = [];
            $imageCount = 0;

            foreach ($msg['content'] as $part) {
                if ($part['type'] === 'image_url' && isset($part['image_url']['url'])) {
                    $imageCount++;
                    if ($imageCount > $maxImages) {
                        header('Content-Type: application/json');
                        echo json_encode(['status' => 'error', 'message' => "Maximum $maxImages images allowed per message."]);
                        exit;
                    }

                    $url = $part['image_url']['url'];
                    if (preg_match('/^data:(image\/[a-z]+);base64,/', $url, $m)) {
                        $mimeType = $m[1];
                        if (!in_array($mimeType, $allowedTypes)) {
                            header('Content-Type: application/json');
                            echo json_encode(['status' => 'error', 'message' => "Unsupported image type: $mimeType. Allowed: JPEG, PNG, GIF, WebP."]);
                            exit;
                        }

                        $base64Data = substr($url, strpos($url, ',') + 1);
                        $decodedSize = (int)(strlen($base64Data) * 3 / 4);
                        if ($decodedSize > $maxSizeBytes) {
                            $sizeMB = round($decodedSize / (1024 * 1024), 1);
                            header('Content-Type: application/json');
                            echo json_encode(['status' => 'error', 'message' => "Image too large ($sizeMB MB). Maximum 10MB per image."]);
                            exit;
                        }
                    }
                }
                $validatedContent[] = $part;
            }

            $apiMessages[] = ['role' => $msg['role'], 'content' => $validatedContent];
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

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

        $buffer = '';
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error || ($httpCode < 200 || $httpCode >= 300)) {
            $errMsg = $error ?: "HTTP $httpCode";
            echo "data: " . json_encode(['error' => $errMsg]) . "\n\n";
            flush();
        }

        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title>AI Chat</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/12.0.2/marked.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs" type="module"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0a0a0f;
  --bg-grad:linear-gradient(135deg,#0a0a0f 0%,#111118 50%,#0d0d14 100%);
  --surface:#13131d;
  --surface-hover:#1a1a27;
  --surface-active:#1f1f2e;
  --border:rgba(255,255,255,.06);
  --border-bright:rgba(255,255,255,.1);
  --text:#f0f0f5;
  --text-sec:#a0a0b8;
  --text-muted:#6b6b80;
  --red:#ef4444;
  --red-glow:rgba(239,68,68,.15);
  --red-soft:#dc2626;
  --green:#22c55e;
  --green-glow:rgba(34,197,94,.15);
  --green-soft:#16a34a;
  --radius:12px;
  --radius-sm:8px;
  --radius-xs:6px;
  --shadow:0 4px 24px rgba(0,0,0,.4);
  --shadow-lg:0 8px 40px rgba(0,0,0,.5);
  --transition:all .2s cubic-bezier(.4,0,.2,1);
}
html,body{height:100%;overflow:hidden;background:var(--bg);background-image:var(--bg-grad);color:var(--text);font-family:'Inter',system-ui,-apple-system,sans-serif;font-size:14px;-webkit-font-smoothing:antialiased}
/* Layout */
.app{display:flex;height:100vh;height:100dvh;position:relative}
/* Sidebar */
.sidebar{width:320px;background:var(--surface);border-right:1px solid rgba(34,197,94,.15);display:flex;flex-direction:column;z-index:100;transition:transform .3s cubic-bezier(.4,0,.2,1);position:relative;box-shadow:5px 0 30px rgba(34,197,94,.08);flex-shrink:0}
.sidebar-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.sidebar-header h2{font-size:15px;font-weight:600;letter-spacing:-.3px;display:flex;align-items:center;gap:10px}
.sidebar-header h2 svg{color:var(--red)}
.sidebar-body{flex:1;overflow-y:auto;padding:20px 24px}
.sidebar-section{margin-bottom:24px}
.sidebar-section-title{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:12px}
.field{margin-bottom:16px}
.field label{display:block;font-size:12px;font-weight:500;color:var(--text-sec);margin-bottom:6px}
.field input,.field textarea{width:100%;background:var(--bg);border:1px solid var(--border-bright);color:var(--text);padding:10px 14px;border-radius:var(--radius-sm);font-family:inherit;font-size:13px;outline:none;transition:var(--transition)}
.field input:focus,.field textarea:focus{border-color:var(--red);box-shadow:0 0 0 3px var(--red-glow)}
.field textarea{resize:vertical;min-height:80px;line-height:1.5}
.field input::placeholder,.field textarea::placeholder{color:var(--text-muted)}
/* Buttons */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 18px;border-radius:var(--radius-sm);font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;transition:var(--transition);border:none;outline:none}
.btn-primary{background:var(--red-soft);color:#fff}
.btn-primary:hover:not(:disabled){background:var(--red);box-shadow:0 0 20px var(--red-glow)}
.btn-ghost{background:transparent;color:var(--text-sec);border:1px solid var(--border-bright)}
.btn-ghost:hover:not(:disabled){background:var(--surface-hover);color:var(--text);border-color:var(--text-muted)}
.btn-danger{background:transparent;color:var(--red);border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover:not(:disabled){background:var(--red-glow);border-color:var(--red)}
.btn-icon{width:40px;height:40px;padding:0;background:var(--surface-hover);color:var(--text-sec);border:1px solid var(--border)}
.btn-icon:hover:not(:disabled){background:var(--surface-active);color:var(--text);border-color:var(--text-muted)}
.btn-sm{padding:6px 12px;font-size:12px}
.btn:disabled{opacity:.4;cursor:not-allowed}
/* Test result */
.test-row{display:flex;align-items:center;gap:12px;margin-top:8px}
.test-result{font-size:12px;font-weight:500}
.test-result.success{color:var(--green)}
.test-result.error{color:var(--red)}
/* Main Chat Area */
.main{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden}
/* Top Bar */
.topbar{height:60px;background:var(--surface);border-bottom:1px solid rgba(34,197,94,.12);display:flex;align-items:center;justify-content:space-between;padding:0 24px;flex-shrink:0;box-shadow:0 3px 15px rgba(34,197,94,.06);z-index:10}
.topbar-left{display:flex;align-items:center;gap:12px;min-width:0;overflow:hidden}
.topbar-title{font-size:15px;font-weight:600;letter-spacing:-.3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.topbar-model{font-size:11px;color:var(--text-muted);background:var(--surface-hover);padding:4px 10px;border-radius:20px;border:1px solid var(--border);white-space:nowrap}
.topbar-actions{display:flex;align-items:center;gap:8px;flex-shrink:0}
/* Messages */
.messages{flex:1;overflow-y:auto;padding:24px;display:flex;flex-direction:column;gap:20px;scroll-behavior:smooth}
.message{display:flex;gap:14px;max-width:880px;width:100%;animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.message.user{align-self:flex-end;flex-direction:row-reverse}
.message-avatar{width:36px;height:36px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;font-weight:600}
.message.user .message-avatar{background:var(--red-glow);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.message.assistant .message-avatar{background:var(--green-glow);color:var(--green);border:1px solid rgba(34,197,94,.2)}
.message-content{flex:1;min-width:0}
.message-role{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;color:var(--text-muted)}
.message.user .message-role{text-align:right}
.message-bubble{padding:14px 18px;border-radius:var(--radius);line-height:1.65;word-wrap:break-word;box-shadow:0 0 15px rgba(34,197,94,.15),0 0 5px rgba(34,197,94,.1)}
.message.user .message-bubble{background:linear-gradient(135deg,rgba(239,68,68,.1) 0%,rgba(239,68,68,.05) 100%);border:1px solid rgba(239,68,68,.15);border-radius:var(--radius) var(--radius) var(--radius-xs) var(--radius);box-shadow:0 0 15px rgba(239,68,68,.12),0 0 5px rgba(239,68,68,.08)}
.message.assistant .message-bubble{background:var(--surface);border:1px solid rgba(34,197,94,.2);border-radius:var(--radius) var(--radius) var(--radius) var(--radius-xs);box-shadow:0 0 20px rgba(34,197,94,.15),0 0 8px rgba(34,197,94,.1),inset 0 0 20px rgba(34,197,94,.03)}
.message.system-info{align-self:center;max-width:400px;text-align:center;background:transparent;border:none;padding:40px 20px}
.message.system-info .message-bubble{background:transparent;border:none;color:var(--text-muted);font-size:13px}
/* Markdown content styles */
.md h1,.md h2,.md h3,.md h4,.md h5,.md h6{margin:16px 0 8px;font-weight:600;line-height:1.3;color:var(--text)}
.md h1{font-size:1.4em;padding-bottom:8px;border-bottom:1px solid var(--border)}
.md h2{font-size:1.2em;padding-bottom:6px;border-bottom:1px solid var(--border)}
.md h3{font-size:1.1em}
.md p{margin:8px 0}
.md ul,.md ol{margin:8px 0;padding-left:24px}
.md li{margin:4px 0}
.md blockquote{margin:12px 0;padding:10px 16px;border-left:3px solid var(--red);background:var(--red-glow);border-radius:0 var(--radius-sm) var(--radius-sm) 0;color:var(--text-sec)}
.md blockquote p{margin:4px 0}
.md a{color:var(--red);text-decoration:none;border-bottom:1px solid transparent;transition:var(--transition)}
.md a:hover{border-bottom-color:var(--red)}
.md hr{border:none;border-top:1px solid var(--border);margin:16px 0}
.md table{border-collapse:collapse;margin:12px 0;width:100%;font-size:.92em;overflow-x:auto;display:block}
.md th,.md td{border:1px solid var(--border);padding:8px 14px;text-align:left}
.md th{background:var(--surface-hover);font-weight:600}
.md tr:nth-child(even){background:rgba(255,255,255,.02)}
.md code{background:rgba(255,255,255,.08);padding:2px 6px;border-radius:4px;font-family:'SF Mono',Menlo,Consolas,monospace;font-size:.88em}
.md pre{background:#08080d;border:1px solid var(--border);padding:16px;border-radius:var(--radius-sm);overflow-x:auto;margin:12px 0;font-family:'SF Mono',Menlo,Consolas,monospace;font-size:.86em;line-height:1.6;position:relative}
.md pre code{background:transparent;padding:0;font-size:inherit}
.code-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid var(--border)}
.code-lang{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);font-weight:600}
.code-copy{font-size:11px;color:var(--text-muted);background:var(--surface-hover);border:1px solid var(--border);padding:4px 10px;border-radius:var(--radius-xs);cursor:pointer;transition:var(--transition)}
.code-copy:hover{color:var(--text);border-color:var(--text-muted)}
.md img{max-width:100%;max-height:400px;border-radius:var(--radius-sm);margin:8px 0;display:block;cursor:pointer;transition:var(--transition)}
.md img:hover{opacity:.9}
.img-grid{display:flex;flex-wrap:wrap;gap:8px;margin:8px 0}
.img-grid img{max-width:180px;max-height:140px;border-radius:var(--radius-xs)}
/* Composer */
.composer{padding:16px 24px 20px;background:var(--surface);border-top:1px solid rgba(34,197,94,.12);flex-shrink:0;box-shadow:0 -3px 15px rgba(34,197,94,.06)}
.composer-inner{max-width:880px;margin:0 auto}
.preview-bar{display:flex;gap:8px;margin-bottom:12px;overflow-x:auto;padding:4px 0}
.preview-bar.hidden{display:none}
.preview-thumb{position:relative;flex-shrink:0;width:60px;height:60px;border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border)}
.preview-thumb img{width:100%;height:100%;object-fit:cover}
.preview-remove{position:absolute;top:2px;right:2px;width:20px;height:20px;background:rgba(0,0,0,.8);color:#fff;border:none;border-radius:50%;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:var(--transition)}
.preview-remove:hover{background:var(--red)}
.composer-row{display:flex;gap:10px;align-items:flex-end}
@media(max-width:480px){
  .composer-row{gap:8px}
}
.composer textarea{flex:1;background:var(--bg);border:1px solid var(--border-bright);color:var(--text);padding:12px 16px;border-radius:var(--radius);font-family:inherit;font-size:14px;resize:none;outline:none;min-height:48px;max-height:200px;line-height:1.5;transition:var(--transition)}
.composer textarea:focus{border-color:var(--red);box-shadow:0 0 0 3px var(--red-glow)}
.composer textarea::placeholder{color:var(--text-muted)}
.composer-send{width:48px;height:48px;background:var(--red-soft);color:#fff;border:none;border-radius:var(--radius);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:var(--transition);flex-shrink:0}
.composer-send:hover:not(:disabled){background:var(--red);box-shadow:0 0 20px var(--red-glow);transform:scale(1.02)}
.composer-send:disabled{opacity:.4;cursor:not-allowed}
.composer-send svg{width:20px;height:20px}
.composer-stop{width:48px;height:48px;background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2);border-radius:var(--radius);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:var(--transition);flex-shrink:0}
.composer-stop:hover{background:var(--red-glow);border-color:var(--red)}
/* Sidebar toggle / burger */
.sidebar-toggle{display:none;width:40px;height:40px;background:var(--surface-hover);color:var(--text-sec);border:1px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;align-items:center;justify-content:center;transition:var(--transition);flex-shrink:0}
.sidebar-toggle:hover{background:var(--surface-active);color:var(--text)}
.sidebar-close-btn{display:none;width:32px;height:32px;background:var(--surface-hover);color:var(--text-sec);border:1px solid var(--border);border-radius:var(--radius-xs);cursor:pointer;align-items:center;justify-content:center;transition:var(--transition);flex-shrink:0}
.sidebar-close-btn:hover{background:var(--surface-active);color:var(--text)}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:90;backdrop-filter:blur(4px);opacity:0;transition:opacity .3s}
.sidebar-overlay.active{display:block;opacity:1}
/* Status indicator */
.status-dot{width:8px;height:8px;border-radius:50%;background:var(--green);animation:pulse 2s infinite}
.status-dot.error{background:var(--red);animation:none}
.status-dot.busy{background:var(--red);animation:pulse 1s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
/* Typing cursor */
.cursor::after{content:"▊";color:var(--red);animation:blink 1s infinite;margin-left:2px;font-size:.9em}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
/* Loading bubbles */
.loading-bubbles{display:flex;gap:6px;padding:8px 0;align-items:center}
.loading-bubbles .bubble{width:10px;height:10px;border-radius:50%;background:var(--green);box-shadow:0 0 10px var(--green),0 0 20px rgba(34,197,94,.5);animation:bubble 1.4s infinite ease-in-out}
.loading-bubbles .bubble:nth-child(1){animation-delay:0s}
.loading-bubbles .bubble:nth-child(2){animation-delay:.2s}
.loading-bubbles .bubble:nth-child(3){animation-delay:.4s}
@keyframes bubble{0%,80%,100%{transform:scale(.6);opacity:.4}40%{transform:scale(1);opacity:1}}
/* Message metadata */
.message-meta{display:flex;align-items:center;gap:12px;margin-top:8px;font-size:11px;color:var(--text-muted)}
.message-meta svg{width:12px;height:12px;flex-shrink:0}
.message-meta span{display:flex;align-items:center;gap:4px}
.message.user .message-meta{justify-content:flex-end}
/* Scrollbar */
::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border-bright);border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:var(--text-muted)}
#fileInput{display:none}
/* Empty state */
.empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;padding:40px;animation:fadeIn .5s ease}
.empty-icon{width:80px;height:80px;background:var(--red-glow);border:1px solid rgba(239,68,68,.15);border-radius:24px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;box-shadow:0 0 30px rgba(34,197,94,.15),0 0 15px rgba(34,197,94,.1)}
.empty-icon svg{width:36px;height:36px;color:var(--red)}
.empty-title{font-size:18px;font-weight:600;margin-bottom:8px}
.empty-desc{font-size:13px;color:var(--text-muted);max-width:300px;line-height:1.6}
/* Responsive */
@media(max-width:768px){
  .sidebar{position:fixed;left:0;top:0;bottom:0;transform:translateX(-100%);box-shadow:var(--shadow-lg);width:85vw;max-width:320px}
  .sidebar.open{transform:translateX(0)}
  .sidebar-close-btn{display:flex}
  .sidebar-toggle{display:flex}
  .main{flex:1 1 100%;width:100%;min-width:0}
  .topbar{padding:0 12px;height:56px;gap:8px}
  .messages{padding:12px}
  .composer{padding:10px 12px 14px}
  .message{max-width:100%}
  .message-avatar{width:30px;height:30px;font-size:11px}
  .message-bubble{padding:10px 12px}
  .composer textarea{font-size:16px;padding:10px 14px}
  .topbar-model{display:none}
  .topbar-title{font-size:14px}
}
@media(max-width:480px){
  .sidebar{width:92vw}
  .composer-send,.composer-stop{width:42px;height:42px}
  .btn-icon{width:36px;height:36px}
  .preview-thumb{width:52px;height:52px}
  .message-bubble{padding:8px 10px;font-size:13px}
  .sidebar-header{padding:14px 16px}
  .sidebar-body{padding:14px 16px}
  .field input,.field textarea{padding:8px 12px}
}
/* SweetAlert2 Custom Theme */
.swal2-popup{
  background:#13131d!important;
  border:1px solid rgba(34,197,94,.2)!important;
  border-radius:16px!important;
  box-shadow:0 8px 40px rgba(0,0,0,.6),0 0 30px rgba(34,197,94,.15),0 0 10px rgba(34,197,94,.1)!important;
  font-family:'Inter',system-ui,sans-serif!important;
}
.swal2-title{
  color:#f0f0f5!important;
  font-size:1.25rem!important;
  font-weight:600!important;
}
.swal2-html-container{
  color:#a0a0b8!important;
  font-size:.95rem!important;
}
.swal2-icon{
  border-color:rgba(239,68,68,.3)!important;
  color:#ef4444!important;
}
.swal2-icon.swal2-warning{
  border-color:rgba(239,68,68,.4)!important;
  color:#ef4444!important;
}
.swal2-confirm{
  background:linear-gradient(135deg,#ef4444,#dc2626)!important;
  color:#fff!important;
  border:none!important;
  border-radius:10px!important;
  font-weight:600!important;
  padding:12px 28px!important;
  box-shadow:0 4px 16px rgba(239,68,68,.3)!important;
  transition:all .2s ease!important;
}
.swal2-confirm:hover{
  background:linear-gradient(135deg,#dc2626,#b91c1c)!important;
  transform:translateY(-1px)!important;
  box-shadow:0 6px 20px rgba(239,68,68,.4)!important;
}
.swal2-cancel{
  background:#1f1f2e!important;
  color:#a0a0b8!important;
  border:1px solid rgba(255,255,255,.06)!important;
  border-radius:10px!important;
  font-weight:500!important;
  padding:12px 28px!important;
  transition:all .2s ease!important;
}
.swal2-cancel:hover{
  background:#262636!important;
  color:#f0f0f5!important;
}
.swal2-actions{
  gap:12px!important;
  margin-top:24px!important;
}
.swal2-timer-progress-bar{
  background:rgba(239,68,68,.5)!important;
}
</style>
</head>
<body>
<div class="app">
  <!-- Sidebar Overlay (mobile) -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h2>
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
          <path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
          <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
        </svg>
        Settings
      </h2>
      <button class="sidebar-close-btn" id="closeSidebar">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="sidebar-body">
      <div class="sidebar-section">
        <div class="sidebar-section-title">API Configuration</div>
        <div class="field">
          <label>Base URL</label>
          <input id="baseUrl" placeholder="https://api.openai.com/v1/chat/completions" />
        </div>
        <div class="field">
          <label>API Key</label>
          <input id="apiKey" type="password" placeholder="your-api-key" />
        </div>
        <div class="field">
          <label>Model ID</label>
          <input id="modelId" placeholder="gpt-4o-mini" />
        </div>
        <div class="test-row">
          <button class="btn btn-ghost btn-sm" id="testBtn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Test Connection
          </button>
          <span class="test-result" id="testResult"></span>
        </div>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-title">System Prompt</div>
        <div class="field">
          <textarea id="systemPrompt" placeholder="You are a helpful assistant."></textarea>
        </div>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-title">Chat Actions</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <button class="btn btn-ghost" id="newChatBtn" style="width:100%">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Chat
          </button>
          <button class="btn btn-ghost" id="exportChatBtn" style="width:100%">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Chat
          </button>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="main">
    <!-- Top Bar -->
    <div class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle" id="openSidebar">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">AI Chat</div>
        <span class="topbar-model" id="modelName">Not configured</span>
      </div>
      <div class="topbar-actions">
        <div class="status-dot" id="statusDot"></div>
        <span id="statusText" style="font-size:12px;color:var(--text-muted)">Ready</span>
        <span id="timer" style="font-size:12px;color:var(--red);font-weight:600;min-width:45px;display:none">0s</span>
      </div>
    </div>

    <!-- Messages -->
    <div class="messages" id="messages">
      <div class="empty-state" id="emptyState">
        <div class="empty-icon">
          <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
          </svg>
        </div>
        <div class="empty-title">Start a Conversation</div>
        <div class="empty-desc">Configure your API settings in the sidebar, then send a message to begin chatting.</div>
      </div>
    </div>

    <!-- Composer -->
    <div class="composer">
      <div class="composer-inner">
        <div class="preview-bar hidden" id="previewBar"></div>
        <div class="composer-row">
          <button class="btn-icon" id="attachBtn" title="Attach image or PDF">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
          </button>
          <input type="file" id="fileInput" accept="image/*,.pdf" multiple />
          <textarea id="input" placeholder="Type your message..." rows="1"></textarea>
          <button class="composer-send" id="sendBtn" title="Send message">
            <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
          <button class="composer-stop" id="stopBtn" style="display:none" title="Stop generation">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="module">
import * as pdfjsLib from "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs";
pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.worker.min.mjs";

const $ = (id) => document.getElementById(id);
const CHAT_KEY = "ai_chat_history_v1";
const SELF = window.location.href.split('?')[0];

let messages = [];
let abortController = null;
let pendingImages = [];

// --- Sidebar toggle ---
const sidebar = $("sidebar");
const overlay = $("sidebarOverlay");

function openSidebar() {
  sidebar.classList.add("open");
  overlay.classList.add("active");
  document.body.style.overflow = "hidden";
}
function closeSidebar() {
  sidebar.classList.remove("open");
  overlay.classList.remove("active");
  document.body.style.overflow = "";
}

$("openSidebar").addEventListener("click", openSidebar);
$("closeSidebar").addEventListener("click", closeSidebar);
overlay.addEventListener("click", closeSidebar);
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeSidebar();
});

// --- Init ---
async function loadConfig() {
  try {
    const res = await fetch(SELF, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'load_config' }),
    });
    const data = await res.json();
    if (data.status === 'ok') {
      $("baseUrl").value = data.config.baseUrl || "";
      $("modelId").value = data.config.modelId || "";
      $("systemPrompt").value = data.config.systemPrompt || "";
      $("apiKey").value = data.config.hasApiKey ? "••••••••" : "";
      $("apiKey").dataset.masked = data.config.hasApiKey ? "1" : "0";
      updateModelName();
    }
  } catch {}
  try {
    const hist = JSON.parse(localStorage.getItem(CHAT_KEY) || "[]");
    messages = hist;
    renderAll();
  } catch {}
}

function updateModelName() {
  const model = $("modelId").value.trim();
  $("modelName").textContent = model || "Not configured";
}

async function saveConfig() {
  const apiKey = $("apiKey").value;
  const payload = {
    action: 'save_config',
    baseUrl: $("baseUrl").value.trim(),
    apiKey: (apiKey === "••••••••" && $("apiKey").dataset.masked === "1") ? "" : apiKey,
    modelId: $("modelId").value.trim(),
    systemPrompt: $("systemPrompt").value,
  };
  if (payload.apiKey === "" && $("apiKey").dataset.masked === "1") {
    delete payload.apiKey;
  }
  try {
    await fetch(SELF, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
  } catch {}
  updateModelName();
}

function sanitizeMessage(msg) {
  if (!msg || typeof msg !== 'object') return msg;
  if (!Array.isArray(msg.content)) return msg;

  const sanitizedContent = msg.content.map(part => {
    if (part.type === 'image_url' && part.image_url?.url?.startsWith('data:')) {
      return { type: 'image_url', image_url: { url: '[image]' } };
    }
    return part;
  });

  return { ...msg, content: sanitizedContent };
}

function saveHistory() {
  const MAX_MESSAGES = 50;
  const toSave = messages.slice(-MAX_MESSAGES).map(m => sanitizeMessage(m));

  try {
    localStorage.setItem(CHAT_KEY, JSON.stringify(toSave));
  } catch (e) {
    if (e.name === 'QuotaExceededError') {
      const pruned = toSave.slice(Math.floor(toSave.length * 0.2));
      try {
        localStorage.setItem(CHAT_KEY, JSON.stringify(pruned));
      } catch {
        localStorage.removeItem(CHAT_KEY);
      }
    }
  }
}

["baseUrl","apiKey","modelId","systemPrompt"].forEach(id => {
  $(id).addEventListener("change", saveConfig);
  $(id).addEventListener("blur", saveConfig);
});

// --- File handling ---
$("attachBtn").addEventListener("click", () => $("fileInput").click());

$("fileInput").addEventListener("change", async (e) => {
  const files = Array.from(e.target.files);
  e.target.value = "";

  const maxImages = 10;
  const maxFileSize = 10 * 1024 * 1024; // 10MB
  const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp", "application/pdf"];

  if (pendingImages.length + files.length > maxImages) {
    Swal.fire({ title: "Too many images", text: `Maximum ${maxImages} images allowed.`, icon: "warning" });
    return;
  }

  for (const file of files) {
    if (!allowedTypes.includes(file.type)) {
      Swal.fire({ title: "Unsupported file type", text: `${file.name} is not supported. Use JPEG, PNG, GIF, WebP, or PDF.`, icon: "warning" });
      continue;
    }

    if (file.size > maxFileSize) {
      const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
      Swal.fire({ title: "File too large", text: `${file.name} is ${sizeMB} MB. Maximum 10MB per file.`, icon: "warning" });
      continue;
    }

    if (file.type === "application/pdf") {
      const imgs = await pdfToImages(file);
      pendingImages.push(...imgs);
    } else if (file.type.startsWith("image/")) {
      const dataUrl = await fileToDataUrl(file);
      pendingImages.push(dataUrl);
    }
  }
  renderPreviews();
});

function fileToDataUrl(file) {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.readAsDataURL(file);
  });
}

async function pdfToImages(file) {
  const arrayBuffer = await file.arrayBuffer();
  const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
  const images = [];
  for (let i = 1; i <= pdf.numPages; i++) {
    const page = await pdf.getPage(i);
    const scale = 1.5;
    const viewport = page.getViewport({ scale });
    const canvas = document.createElement("canvas");
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    const ctx = canvas.getContext("2d");
    await page.render({ canvasContext: ctx, viewport }).promise;
    images.push(canvas.toDataURL("image/png"));
  }
  return images;
}

function renderPreviews() {
  const bar = $("previewBar");
  bar.innerHTML = "";
  if (pendingImages.length === 0) {
    bar.classList.add("hidden");
    return;
  }
  bar.classList.remove("hidden");
  pendingImages.forEach((src, i) => {
    const thumb = document.createElement("div");
    thumb.className = "preview-thumb";
    thumb.innerHTML = `<img src="${src}" /><button class="preview-remove" data-idx="${i}">×</button>`;
    bar.appendChild(thumb);
  });
  bar.querySelectorAll(".preview-remove").forEach(btn => {
    btn.addEventListener("click", () => {
      const idx = parseInt(btn.dataset.idx);
      pendingImages.splice(idx, 1);
      renderPreviews();
    });
  });
}

// --- Rendering ---
marked.setOptions({
  breaks: true,
  gfm: true,
  highlight: function(code, lang) {
    if (lang && hljs.getLanguage(lang)) {
      try { return hljs.highlight(code, { language: lang }).value; } catch {}
    }
    try { return hljs.highlightAuto(code).value; } catch {}
    return code;
  }
});

const renderer = new marked.Renderer();
renderer.code = function({ text, lang }) {
  const langLabel = lang || 'code';
  const highlighted = lang && hljs.getLanguage(lang)
    ? hljs.highlight(text, { language: lang }).value
    : (() => { try { return hljs.highlightAuto(text).value; } catch { return escapeHtml(text); } })();
  return `<pre><div class="code-header"><span class="code-lang">${langLabel}</span><button class="code-copy" onclick="copyCode(this)">Copy</button></div><code class="hljs${lang ? ' language-'+lang : ''}">${highlighted}</code></pre>`;
};
marked.use({ renderer });

window.copyCode = function(btn) {
  const pre = btn.closest('pre');
  const code = pre.querySelector('code');
  const text = code.textContent;
  navigator.clipboard.writeText(text).then(() => {
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = 'Copy', 1500);
  });
};

function renderMarkdown(text) {
  try { return marked.parse(text || ''); }
  catch { return escapeHtml(text || ''); }
}
function escapeHtml(s) {
  return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function renderAll() {
  const el = $("messages");
  el.innerHTML = "";
  const empty = $("emptyState");
  if (messages.length === 0) {
    el.appendChild(createEmptyState());
    return;
  }
  messages.forEach(m => appendMessageEl(m.role, m.content));
  el.scrollTop = el.scrollHeight;
}

function createEmptyState() {
  const div = document.createElement("div");
  div.className = "empty-state";
  div.id = "emptyState";
  div.innerHTML = `
    <div class="empty-icon">
      <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
      </svg>
    </div>
    <div class="empty-title">Start a Conversation</div>
    <div class="empty-desc">Configure your API settings in the sidebar, then send a message to begin chatting.</div>
  `;
  return div;
}

function appendMessageEl(role, content, isLoading = false) {
  const el = $("messages");
  // Remove empty state if present
  const empty = el.querySelector(".empty-state");
  if (empty) empty.remove();

  const div = document.createElement("div");
  div.className = `message ${role}`;

  const avatar = document.createElement("div");
  avatar.className = "message-avatar";
  avatar.innerHTML = role === "user"
    ? `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`
    : `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a7 7 0 017 7c0 5-7 11-7 11S5 14 5 9a7 7 0 017-7z"/><circle cx="12" cy="9" r="2.5"/></svg>`;

  const contentDiv = document.createElement("div");
  contentDiv.className = "message-content";

  const roleLabel = document.createElement("div");
  roleLabel.className = "message-role";
  roleLabel.textContent = role === "user" ? "You" : "Assistant";

  const bubble = document.createElement("div");
  bubble.className = "message-bubble";
  const body = document.createElement("div");
  body.className = "md";
  
  if (isLoading && role === "assistant") {
    body.innerHTML = `<div class="loading-bubbles"><div class="bubble"></div><div class="bubble"></div><div class="bubble"></div></div>`;
  } else {
    renderContent(body, content);
  }
  bubble.appendChild(body);

  // Metadata container
  const meta = document.createElement("div");
  meta.className = "message-meta";
  meta.id = `meta-${Date.now()}`;

  contentDiv.appendChild(roleLabel);
  contentDiv.appendChild(bubble);
  contentDiv.appendChild(meta);
  div.appendChild(avatar);
  div.appendChild(contentDiv);
  el.appendChild(div);
  el.scrollTop = el.scrollHeight;
  return { body, meta, div };
}

function updateMessageMeta(metaEl, responseTime, tokens) {
  if (!metaEl) return;
  
  let metaHtml = '';
  
  if (responseTime) {
    const seconds = (responseTime / 1000).toFixed(2);
    metaHtml += `<span><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>${seconds}s</span>`;
  }
  
  if (tokens) {
    metaHtml += `<span><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>${tokens} tokens</span>`;
  }
  
  metaEl.innerHTML = metaHtml;
}

function renderContent(container, content) {
  if (Array.isArray(content)) {
    const imgGrid = document.createElement("div");
    imgGrid.className = "img-grid";
    let textParts = [];
    for (const part of content) {
      if (part.type === "image_url" && part.image_url) {
        const img = document.createElement("img");
        img.src = part.image_url.url;
        imgGrid.appendChild(img);
      } else if (part.type === "text") {
        textParts.push(part.text);
      }
    }
    if (imgGrid.children.length > 0) container.appendChild(imgGrid);
    if (textParts.length) {
      const textDiv = document.createElement("div");
      textDiv.innerHTML = renderMarkdown(textParts.join("\n"));
      container.appendChild(textDiv);
    }
  } else {
    container.innerHTML = renderMarkdown(content || "");
  }
}

function updateMessageEl(bodyEl, content, streaming) {
  bodyEl.innerHTML = renderMarkdown(content || "") + (streaming ? '<span class="cursor"></span>' : '');
  $("messages").scrollTop = $("messages").scrollHeight;
}

// --- Status ---
function setStatus(text, type = "ready") {
  $("statusText").textContent = text;
  const dot = $("statusDot");
  dot.className = "status-dot";
  if (type === "error") dot.classList.add("error");
  else if (type === "busy") dot.classList.add("busy");
}

// --- Timer ---
let timerInterval = null;

function startTimer() {
  const timerEl = $("timer");
  timerEl.style.display = "inline";
  timerEl.textContent = "0s";
  let seconds = 0;
  timerInterval = setInterval(() => {
    seconds++;
    if (seconds < 60) {
      timerEl.textContent = seconds + "s";
    } else {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      timerEl.textContent = mins + "m " + secs + "s";
    }
  }, 1000);
}

function stopTimer() {
  if (timerInterval) {
    clearInterval(timerInterval);
    timerInterval = null;
  }
}

// --- Chat ---
async function streamChat(msgs, bodyEl, metaEl, silent = false, overallStartTime = null) {
  let assistantText = "";
  let totalTokens = 0;
  const startTime = overallStartTime || Date.now();

  if (!silent) abortController = new AbortController();

  const res = await fetch(SELF, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action: "chat", messages: msgs }),
    signal: abortController.signal,
  });

  if (!res.ok) {
    const errText = await res.text();
    throw new Error(`HTTP ${res.status}: ${errText}`);
  }

  const reader = res.body.getReader();
  const decoder = new TextDecoder();
  let buffer = "";

  while (true) {
    const { done, value } = await reader.read();
    if (done) break;
    buffer += decoder.decode(value, { stream: true });

    const lines = buffer.split("\n");
    buffer = lines.pop();

    for (const line of lines) {
      const trimmed = line.trim();
      if (!trimmed || !trimmed.startsWith("data:")) continue;
      const data = trimmed.slice(5).trim();
      if (data === "[DONE]") continue;
      try {
        const json = JSON.parse(data);
        if (json.error) throw new Error(json.error);
        const delta = json.choices?.[0]?.delta?.content
                   ?? json.choices?.[0]?.message?.content ?? "";
        if (delta) {
          assistantText += delta;
          if (!silent && bodyEl) updateMessageEl(bodyEl, assistantText, true);
        }
        if (json.usage?.total_tokens) {
          totalTokens = json.usage.total_tokens;
        }
      } catch (e) {
        if (e.message && !e.message.includes("JSON")) throw e;
      }
    }
  }

  const responseTime = Date.now() - startTime;
  if (!totalTokens) {
    totalTokens = Math.ceil(assistantText.length / 4);
  }

  if (!silent && bodyEl) {
    updateMessageEl(bodyEl, assistantText, false);
    if (metaEl) updateMessageMeta(metaEl, responseTime, totalTokens);
  }

  return assistantText;
}

async function send() {
  const input = $("input");
  const text = input.value.trim();
  if (!text && pendingImages.length === 0) return;

  const overallStartTime = Date.now();

  const baseUrl = $("baseUrl").value.trim();
  const apiKey = $("apiKey").value.trim();
  const model = $("modelId").value.trim();

  if (!baseUrl || !apiKey || !model) {
    setStatus("Configure API settings first", "error");
    openSidebar();
    return;
  }

  const imagesToSend = [...pendingImages];
  const BATCH_SIZE = 10;
  const needsBatching = imagesToSend.length > BATCH_SIZE;

  // Clear input and previews
  input.value = "";
  input.style.height = "auto";
  input.placeholder = "Type your message...";
  pendingImages = [];
  $("fileInput").value = "";
  renderPreviews();
  input.focus();

  setBusy(true);
  startTimer();
  abortController = new AbortController();

  try {
    if (!needsBatching) {
      // Single batch: send all at once
      const userContent = [];
      if (text) userContent.push({ type: "text", text });
      for (const img of imagesToSend) {
        userContent.push({ type: "image_url", image_url: { url: img } });
      }

      const displayContent = userContent.length === 1 && userContent[0].type === "text"
        ? text : userContent;

      messages.push({ role: "user", content: displayContent });
      appendMessageEl("user", displayContent);
      saveHistory();

      setStatus("Thinking...", "busy");
      const assistantMsg = appendMessageEl("assistant", "", true);
      const result = await streamChat(messages, assistantMsg.body, assistantMsg.meta, false, overallStartTime);
      messages.push({ role: "assistant", content: result });
      saveHistory();
      setStatus("Ready");
    } else {
      // Multiple batches: read all silently, then respond
      const totalImages = imagesToSend.length;
      const batches = [];
      for (let i = 0; i < totalImages; i += BATCH_SIZE) {
        batches.push(imagesToSend.slice(i, i + BATCH_SIZE));
      }
      const totalBatches = batches.length;

      // Show user message
      appendMessageEl("user", [
        { type: "text", text: `${text || "Analyze these images"} (${totalImages} images)` }
      ]);

      // Show single loading message for entire extraction
      const loadingMsg = appendMessageEl("assistant", "", true);

      let accumulatedData = "";

      for (let b = 0; b < totalBatches; b++) {
        if (abortController.signal.aborted) break;

        const batch = batches[b];
        const startIdx = b * BATCH_SIZE + 1;
        const endIdx = Math.min(startIdx + batch.length - 1, totalImages);

        setStatus(`Reading images ${startIdx}-${endIdx} of ${totalImages}...`, "busy");
        updateMessageEl(loadingMsg.body, `*[Reading images ${startIdx}-${endIdx} of ${totalImages}...]*`, true);

        const batchContent = [];
        const prompt = `Extract all text and data from these images (images ${startIdx}-${endIdx} of ${totalImages}).\n\nINSTRUCTIONS:\n1. Read every image in this batch carefully.\n2. Extract ALL text, numbers, tables, and visible data.\n3. Return the COMPLETE raw extraction.\n4. Do NOT summarize or analyze.\n5. Include everything you see - headers, footers, body text, numbers, dates, names.\n6. Preserve the structure (tables as tables, lists as lists).\n7. If previous extractions exist, do not repeat them - only add NEW data from these images.`;

        batchContent.push({ type: "text", text: prompt });
        for (const img of batch) {
          batchContent.push({ type: "image_url", image_url: { url: img } });
        }

        // Build context with all previous extractions so AI knows what was already extracted
        const contextMessages = [];
        if (accumulatedData) {
          contextMessages.push({
            role: "assistant",
            content: `PREVIOUSLY EXTRACTED DATA (do not repeat this):\n${accumulatedData}`
          });
        }

        const stepMessages = [
          ...messages,
          ...contextMessages,
          { role: "user", content: batchContent }
        ];

        const batchResult = await streamChat(stepMessages, null, null, true);
        
        // APPEND this batch's extraction to accumulated data
        accumulatedData += `\n\n--- BATCH ${b + 1} (Images ${startIdx}-${endIdx}) ---\n${batchResult}`;
      }

      if (!abortController.signal.aborted) {
        // Final response: run extracted data against user's prompt
        setStatus("Generating response...", "busy");
        updateMessageEl(loadingMsg.body, `*[Applying your request to extracted data...]*`, true);

        const finalPrompt = `I have extracted ALL data from ${totalImages} images across ${totalBatches} batches. Here is the COMPLETE extracted data:\n\n---EXTRACTED DATA START---\n${accumulatedData}\n---EXTRACTED DATA END---\n\nIMPORTANT: The above contains ALL extracted text and data from EVERY image. Now apply the following request to ALL of this data:\n\n${text || "Analyze and summarize all the extracted data."}`;

        const finalMessages = [
          ...messages,
          { role: "user", content: finalPrompt }
        ];

        const finalResult = await streamChat(finalMessages, loadingMsg.body, loadingMsg.meta, false, overallStartTime);
        messages.push({ role: "user", content: [
          { type: "text", text: text || "Analyze these images" },
          ...imagesToSend.map(img => ({ type: "image_url", image_url: { url: img } }))
        ]});
        messages.push({ role: "assistant", content: finalResult });
        saveHistory();
        setStatus("Ready");
      }
    }
  } catch (err) {
    if (err.name === "AbortError") {
      setStatus("Stopped");
    } else {
      appendMessageEl("assistant", `**Error:** ${err.message}`);
      setStatus("Error", "error");
    }
  } finally {
    stopTimer();
    setBusy(false);
    abortController = null;
  }
}

async function testConnection() {
  const result = $("testResult");
  const btn = $("testBtn");

  const baseUrl = $("baseUrl").value.trim();
  const apiKey = $("apiKey").value.trim();
  const model = $("modelId").value.trim();

  if (!baseUrl || !apiKey || !model) {
    result.className = "test-result error";
    result.textContent = "Fill all fields first";
    return;
  }

  await saveConfig();
  btn.disabled = true;
  btn.innerHTML = `<svg width="14" height="14" style="animation:spin 1s linear infinite" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v4m0 12v4m10-10h-4M6 12H2m15.07-5.07l-2.83 2.83m-5.66 5.66l-2.83 2.83m12.14 0l-2.83-2.83M9.76 9.76L6.93 6.93"/></svg> Testing...`;
  result.className = "test-result";
  result.textContent = "";

  try {
    const res = await fetch(SELF, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "test_connection" }),
    });
    const data = await res.json();

    if (data.status === "ok") {
      result.className = "test-result success";
      result.textContent = "✓ Connected";
    } else {
      result.className = "test-result error";
      result.textContent = "✗ Failed";
    }
  } catch (err) {
    result.className = "test-result error";
    result.textContent = "✗ Error";
  } finally {
    btn.disabled = false;
    btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Test Connection`;
  }
}

function stop() {
  if (abortController) abortController.abort();
}

async function newChat() {
  if (messages.length) {
    const result = await Swal.fire({
      title: 'Clear current chat?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Clear',
      cancelButtonText: 'Cancel',
      reverseButtons: true,
      customClass: {
        popup: 'swal-dark-theme'
      }
    });
    if (!result.isConfirmed) return;
  }
  messages = [];
  saveHistory();
  renderAll();
  setStatus("Ready");
}

function exportChat() {
  const blob = new Blob([JSON.stringify(messages, null, 2)], { type: "application/json" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = `chat-${new Date().toISOString().slice(0,10)}.json`;
  a.click();
  URL.revokeObjectURL(a.href);
}

function setBusy(busy) {
  $("sendBtn").style.display = busy ? "none" : "flex";
  $("stopBtn").style.display = busy ? "flex" : "none";
  $("input").disabled = busy;
}

// --- Input handling ---
const input = $("input");
input.addEventListener("keydown", (e) => {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    if (!$("sendBtn").disabled) send();
  }
});

function autoResize(el) {
  el.style.height = "auto";
  el.style.height = Math.min(el.scrollHeight, 200) + "px";
}
input.addEventListener("input", () => autoResize(input));

// --- Event listeners ---
$("sendBtn").addEventListener("click", send);
$("stopBtn").addEventListener("click", stop);
$("testBtn").addEventListener("click", testConnection);
$("newChatBtn").addEventListener("click", newChat);
$("exportChatBtn").addEventListener("click", exportChat);

// Add spin animation
const style = document.createElement('style');
style.textContent = '@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}';
document.head.appendChild(style);

// Init
loadConfig();
input.focus();
</script>
</body>
</html>