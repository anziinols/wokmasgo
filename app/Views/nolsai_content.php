<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="nolsai-app" id="nolsaiApp">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h2>
        <img src="<?= esc($logo_url) ?>" alt="Nolsai" class="nolsai-logo-sm">
        Settings
      </h2>
      <button class="sidebar-close-btn" id="closeSidebar" type="button">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="sidebar-body">
      <div class="sidebar-section">
        <div class="sidebar-section-title">API Configuration</div>
        <div class="field">
          <label for="baseUrl">Base URL</label>
          <input id="baseUrl" placeholder="https://api.openai.com/v1/chat/completions" />
        </div>
        <div class="field">
          <label for="apiKey">API Key</label>
          <input id="apiKey" type="password" placeholder="your-api-key" />
        </div>
        <div class="field">
          <label for="modelId">Model ID</label>
          <input id="modelId" placeholder="gpt-4o-mini" />
        </div>
        <div class="test-row">
          <button class="btn btn-ghost btn-sm" id="testBtn" type="button">
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
          <button class="btn btn-ghost" id="newChatBtn" type="button" style="width:100%">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Chat
          </button>
          <button class="btn btn-ghost" id="exportChatBtn" type="button" style="width:100%">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Chat
          </button>
          <button class="btn btn-ghost" id="visitorsBtn" type="button" style="width:100%">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            Visitor Analytics
          </button>
        </div>
      </div>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle" id="openSidebar" type="button">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <img src="<?= esc($logo_url) ?>" alt="Nolsai" class="nolsai-logo-top">
        <div class="topbar-title">Nolsai</div>
        <span class="topbar-model" id="modelName">Not configured</span>
      </div>
      <div class="topbar-actions">
        <div class="status-dot" id="statusDot"></div>
        <span id="statusText" style="font-size:12px;color:var(--nolsai-text-muted)">Ready</span>
        <span id="timer" style="font-size:12px;color:var(--nolsai-red);font-weight:600;min-width:45px;display:none">0s</span>
      </div>
    </div>

    <div class="messages" id="messages">
      <div class="empty-state" id="emptyState">
        <div class="empty-icon">
          <img src="<?= esc($logo_url) ?>" alt="Nolsai" class="nolsai-logo-empty">
        </div>
        <div class="empty-title">Start a Conversation</div>
        <div class="empty-desc">Configure your API settings in the sidebar, then send a message to begin chatting with Nolsai.</div>
      </div>
    </div>

    <div class="composer">
      <div class="composer-inner">
        <div class="preview-bar hidden" id="previewBar"></div>
        <div class="composer-row">
          <button class="btn-icon" id="attachBtn" type="button" title="Attach image or PDF">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
          </button>
          <input type="file" id="fileInput" accept="image/*,.pdf" multiple />
          <textarea id="input" placeholder="Type your message..." rows="1"></textarea>
          <button class="composer-send" id="sendBtn" type="button" title="Send message">
            <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
          <button class="composer-stop" id="stopBtn" type="button" style="display:none" title="Stop generation">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
window.NOLSAI_CONFIG = {
  saveConfigUrl: <?= json_encode($save_config_url) ?>,
  loadConfigUrl: <?= json_encode($load_config_url) ?>,
  testConnectionUrl: <?= json_encode($test_connection_url) ?>,
  chatUrl: <?= json_encode($chat_url) ?>,
  visitorsUrl: <?= json_encode($visitors_url) ?>,
  logoUrl: <?= json_encode($logo_url) ?>
};
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/12.0.2/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.js"></script>
