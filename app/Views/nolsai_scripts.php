(function() {
  function $(id) { return document.getElementById(id); }

  var cfg = window.NOLSAI_CONFIG || {};
  var CHAT_KEY = 'nolsai_chat_history_v1';
  var messages = [];
  var abortController = null;
  var pendingImages = [];
  var timerInterval = null;
  var pdfjsReady = false;

  if (window.pdfjsLib) {
    window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.worker.min.js';
    pdfjsReady = true;
  }

  var sidebar = $('sidebar');
  var overlay = $('sidebarOverlay');

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('open');
    if (overlay) overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if ($('openSidebar')) $('openSidebar').addEventListener('click', openSidebar);
  if ($('closeSidebar')) $('closeSidebar').addEventListener('click', closeSidebar);
  if (overlay) overlay.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar();
  });

  async function loadConfig() {
    try {
      var res = await fetch(cfg.loadConfigUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({})
      });
      var data = await res.json();
      if (data.status === 'success' && data.data && data.data.config) {
        var c = data.data.config;
        if ($('baseUrl')) $('baseUrl').value = c.baseUrl || '';
        if ($('modelId')) $('modelId').value = c.modelId || '';
        if ($('systemPrompt')) $('systemPrompt').value = c.systemPrompt || '';
        if ($('apiKey')) {
          $('apiKey').value = c.hasApiKey ? '••••••••' : '';
          $('apiKey').dataset.masked = c.hasApiKey ? '1' : '0';
        }
        updateModelName();
      }
    } catch (e) {}

    try {
      var hist = JSON.parse(localStorage.getItem(CHAT_KEY) || '[]');
      messages = hist;
      renderAll();
    } catch (e) {}
  }

  function updateModelName() {
    var model = $('modelId') ? $('modelId').value.trim() : '';
    if ($('modelName')) $('modelName').textContent = model || 'Not configured';
  }

  async function saveConfig() {
    var apiKey = $('apiKey') ? $('apiKey').value : '';
    var payload = {
      baseUrl: $('baseUrl') ? $('baseUrl').value.trim() : '',
      apiKey: (apiKey === '••••••••' && $('apiKey') && $('apiKey').dataset.masked === '1') ? '' : apiKey,
      modelId: $('modelId') ? $('modelId').value.trim() : '',
      systemPrompt: $('systemPrompt') ? $('systemPrompt').value : ''
    };
    if (payload.apiKey === '' && $('apiKey') && $('apiKey').dataset.masked === '1') {
      delete payload.apiKey;
    }
    try {
      await fetch(cfg.saveConfigUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(payload)
      });
    } catch (e) {}
    updateModelName();
  }

  function sanitizeMessage(msg) {
    if (!msg || typeof msg !== 'object') return msg;
    if (!Array.isArray(msg.content)) return msg;
    var sanitizedContent = msg.content.map(function(part) {
      if (part.type === 'image_url' && part.image_url && part.image_url.url && String(part.image_url.url).indexOf('data:') === 0) {
        return { type: 'image_url', image_url: { url: '[image]' } };
      }
      return part;
    });
    return { role: msg.role, content: sanitizedContent };
  }

  function saveHistory() {
    var MAX_MESSAGES = 50;
    var toSave = messages.slice(-MAX_MESSAGES).map(function(m) { return sanitizeMessage(m); });
    try {
      localStorage.setItem(CHAT_KEY, JSON.stringify(toSave));
    } catch (e) {
      if (e.name === 'QuotaExceededError') {
        var pruned = toSave.slice(Math.floor(toSave.length * 0.2));
        try {
          localStorage.setItem(CHAT_KEY, JSON.stringify(pruned));
        } catch (e2) {
          localStorage.removeItem(CHAT_KEY);
        }
      }
    }
  }

  ['baseUrl', 'apiKey', 'modelId', 'systemPrompt'].forEach(function(id) {
    if ($(id)) {
      $(id).addEventListener('change', saveConfig);
      $(id).addEventListener('blur', saveConfig);
    }
  });

  if ($('attachBtn')) {
    $('attachBtn').addEventListener('click', function() {
      if ($('fileInput')) $('fileInput').click();
    });
  }

  if ($('fileInput')) {
    $('fileInput').addEventListener('change', async function(e) {
      var files = Array.prototype.slice.call(e.target.files || []);
      e.target.value = '';

      var maxImages = 10;
      var maxFileSize = 10 * 1024 * 1024;
      var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

      if (pendingImages.length + files.length > maxImages) {
        if (window.Swal) Swal.fire({ title: 'Too many images', text: 'Maximum ' + maxImages + ' images allowed.', icon: 'warning' });
        return;
      }

      for (var i = 0; i < files.length; i++) {
        var file = files[i];
        if (allowedTypes.indexOf(file.type) === -1) {
          if (window.Swal) Swal.fire({ title: 'Unsupported file type', text: file.name + ' is not supported. Use JPEG, PNG, GIF, WebP, or PDF.', icon: 'warning' });
          continue;
        }
        if (file.size > maxFileSize) {
          var sizeMB = (file.size / (1024 * 1024)).toFixed(1);
          if (window.Swal) Swal.fire({ title: 'File too large', text: file.name + ' is ' + sizeMB + ' MB. Maximum 10MB per file.', icon: 'warning' });
          continue;
        }
        if (file.type === 'application/pdf') {
          var imgs = await pdfToImages(file);
          pendingImages = pendingImages.concat(imgs);
        } else if (file.type.indexOf('image/') === 0) {
          var dataUrl = await fileToDataUrl(file);
          pendingImages.push(dataUrl);
        }
      }
      renderPreviews();
    });
  }

  function fileToDataUrl(file) {
    return new Promise(function(resolve) {
      var reader = new FileReader();
      reader.onload = function() { resolve(reader.result); };
      reader.readAsDataURL(file);
    });
  }

  async function pdfToImages(file) {
    if (!pdfjsReady || !window.pdfjsLib) {
      if (window.Swal) Swal.fire({ title: 'PDF unavailable', text: 'PDF library failed to load.', icon: 'warning' });
      return [];
    }
    var arrayBuffer = await file.arrayBuffer();
    var pdf = await window.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    var images = [];
    for (var i = 1; i <= pdf.numPages; i++) {
      var page = await pdf.getPage(i);
      var scale = 1.5;
      var viewport = page.getViewport({ scale: scale });
      var canvas = document.createElement('canvas');
      canvas.width = viewport.width;
      canvas.height = viewport.height;
      var ctx = canvas.getContext('2d');
      await page.render({ canvasContext: ctx, viewport: viewport }).promise;
      images.push(canvas.toDataURL('image/png'));
    }
    return images;
  }

  function renderPreviews() {
    var bar = $('previewBar');
    if (!bar) return;
    bar.innerHTML = '';
    if (pendingImages.length === 0) {
      bar.classList.add('hidden');
      return;
    }
    bar.classList.remove('hidden');
    pendingImages.forEach(function(src, i) {
      var thumb = document.createElement('div');
      thumb.className = 'preview-thumb';
      thumb.innerHTML = '<img src="' + src + '" /><button class="preview-remove" data-idx="' + i + '" type="button">×</button>';
      bar.appendChild(thumb);
    });
    var btns = bar.querySelectorAll('.preview-remove');
    for (var b = 0; b < btns.length; b++) {
      btns[b].addEventListener('click', function() {
        var idx = parseInt(this.getAttribute('data-idx'), 10);
        pendingImages.splice(idx, 1);
        renderPreviews();
      });
    }
  }

  if (window.marked) {
    marked.setOptions({
      breaks: true,
      gfm: true,
      highlight: function(code, lang) {
        if (window.hljs && lang && hljs.getLanguage(lang)) {
          try { return hljs.highlight(code, { language: lang }).value; } catch (e) {}
        }
        if (window.hljs) {
          try { return hljs.highlightAuto(code).value; } catch (e) {}
        }
        return code;
      }
    });

    var renderer = new marked.Renderer();
    renderer.code = function(token) {
      var text = typeof token === 'object' ? token.text : token;
      var lang = typeof token === 'object' ? token.lang : arguments[1];
      var langLabel = lang || 'code';
      var highlighted;
      if (window.hljs && lang && hljs.getLanguage(lang)) {
        highlighted = hljs.highlight(text, { language: lang }).value;
      } else if (window.hljs) {
        try { highlighted = hljs.highlightAuto(text).value; } catch (e) { highlighted = escapeHtml(text); }
      } else {
        highlighted = escapeHtml(text);
      }
      return '<pre><div class="code-header"><span class="code-lang">' + langLabel + '</span><button class="code-copy" type="button" onclick="window.nolsaiCopyCode(this)">Copy</button></div><code class="hljs' + (lang ? ' language-' + lang : '') + '">' + highlighted + '</code></pre>';
    };
    marked.use({ renderer: renderer });
  }

  function copyTextToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text);
    }
    return new Promise(function(resolve, reject) {
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.setAttribute('readonly', '');
      ta.style.position = 'fixed';
      ta.style.left = '-9999px';
      document.body.appendChild(ta);
      ta.select();
      try {
        var ok = document.execCommand('copy');
        document.body.removeChild(ta);
        if (ok) resolve();
        else reject(new Error('Copy failed'));
      } catch (e) {
        document.body.removeChild(ta);
        reject(e);
      }
    });
  }

  function flashCopyButton(btn) {
    btn.classList.add('copied');
    btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Copied!';
    setTimeout(function() {
      btn.classList.remove('copied');
      btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy';
    }, 1500);
  }

  window.nolsaiCopyCode = function(btn) {
    var pre = btn.closest('pre');
    if (!pre) return;
    var code = pre.querySelector('code');
    if (!code) return;
    var text = code.textContent || '';
    copyTextToClipboard(text).then(function() {
      btn.textContent = 'Copied!';
      btn.classList.add('copied');
      setTimeout(function() {
        btn.textContent = 'Copy';
        btn.classList.remove('copied');
      }, 1500);
    }).catch(function() {
      btn.textContent = 'Failed';
      setTimeout(function() { btn.textContent = 'Copy'; }, 1500);
    });
  };

  window.nolsaiCopyResponse = function(btn) {
    var messageEl = btn.closest('.message');
    if (!messageEl) return;
    var raw = messageEl.getAttribute('data-raw-content') || '';
    if (!raw) {
      var body = messageEl.querySelector('.md');
      raw = body ? (body.innerText || body.textContent || '') : '';
    }
    copyTextToClipboard(raw).then(function() {
      flashCopyButton(btn);
    }).catch(function() {
      btn.innerHTML = 'Failed';
      setTimeout(function() {
        btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy';
      }, 1500);
    });
  };

  function contentToPlainText(content) {
    if (Array.isArray(content)) {
      var parts = [];
      for (var i = 0; i < content.length; i++) {
        if (content[i].type === 'text' && content[i].text) parts.push(content[i].text);
      }
      return parts.join('\n');
    }
    return content == null ? '' : String(content);
  }

  function createCopyResponseButton() {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'msg-copy-btn';
    btn.title = 'Copy response';
    btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy';
    btn.addEventListener('click', function() {
      window.nolsaiCopyResponse(btn);
    });
    return btn;
  }

  function renderMarkdown(text) {
    try {
      if (window.marked) return marked.parse(text || '');
      return escapeHtml(text || '');
    } catch (e) {
      return escapeHtml(text || '');
    }
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function(c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function renderAll() {
    var el = $('messages');
    if (!el) return;
    el.innerHTML = '';
    if (messages.length === 0) {
      el.appendChild(createEmptyState());
      return;
    }
    messages.forEach(function(m) { appendMessageEl(m.role, m.content); });
    el.scrollTop = el.scrollHeight;
  }

  function createEmptyState() {
    var div = document.createElement('div');
    div.className = 'empty-state';
    div.id = 'emptyState';
    var logo = (cfg.logoUrl || '');
    div.innerHTML =
      '<div class="empty-icon">' +
        (logo ? '<img src="' + logo + '" alt="Nolsai" class="nolsai-logo-empty">' : '') +
      '</div>' +
      '<div class="empty-title">Start a Conversation</div>' +
      '<div class="empty-desc">Configure your API settings in the sidebar, then send a message to begin chatting with Nolsai.</div>';
    return div;
  }

  function appendMessageEl(role, content, isLoading) {
    var el = $('messages');
    if (!el) return { body: null, meta: null, actions: null, div: null };
    var empty = el.querySelector('.empty-state');
    if (empty) empty.remove();

    var div = document.createElement('div');
    div.className = 'message ' + role;

    var avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = role === 'user'
      ? '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
      : '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a7 7 0 017 7c0 5-7 11-7 11S5 14 5 9a7 7 0 017-7z"/><circle cx="12" cy="9" r="2.5"/></svg>';

    var contentDiv = document.createElement('div');
    contentDiv.className = 'message-content';

    var roleLabel = document.createElement('div');
    roleLabel.className = 'message-role';
    roleLabel.textContent = role === 'user' ? 'You' : 'Nolsai';

    var bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    var body = document.createElement('div');
    body.className = 'md';

    if (isLoading && role === 'assistant') {
      body.innerHTML = '<div class="loading-bubbles"><div class="bubble"></div><div class="bubble"></div><div class="bubble"></div></div>';
    } else {
      renderContent(body, content);
      div.setAttribute('data-raw-content', contentToPlainText(content));
    }
    bubble.appendChild(body);

    var meta = document.createElement('div');
    meta.className = 'message-meta';

    var actions = document.createElement('div');
    actions.className = 'message-actions';
    if (role === 'assistant') {
      var copyBtn = createCopyResponseButton();
      if (isLoading) copyBtn.style.display = 'none';
      actions.appendChild(copyBtn);
    }

    contentDiv.appendChild(roleLabel);
    contentDiv.appendChild(bubble);
    contentDiv.appendChild(actions);
    contentDiv.appendChild(meta);
    div.appendChild(avatar);
    div.appendChild(contentDiv);
    el.appendChild(div);
    el.scrollTop = el.scrollHeight;
    return { body: body, meta: meta, actions: actions, div: div };
  }

  function showCopyButton(messageRef) {
    if (!messageRef || !messageRef.actions) return;
    var btn = messageRef.actions.querySelector('.msg-copy-btn');
    if (btn) btn.style.display = 'inline-flex';
  }

  function setMessageRawContent(messageRef, text) {
    if (!messageRef || !messageRef.div) return;
    messageRef.div.setAttribute('data-raw-content', text || '');
  }

  function updateMessageMeta(metaEl, responseTime, tokens) {
    if (!metaEl) return;
    var metaHtml = '';
    if (responseTime) {
      var seconds = (responseTime / 1000).toFixed(2);
      metaHtml += '<span><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>' + seconds + 's</span>';
    }
    if (tokens) {
      metaHtml += '<span><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>' + tokens + ' tokens</span>';
    }
    metaEl.innerHTML = metaHtml;
  }

  function renderContent(container, content) {
    if (Array.isArray(content)) {
      var imgGrid = document.createElement('div');
      imgGrid.className = 'img-grid';
      var textParts = [];
      for (var i = 0; i < content.length; i++) {
        var part = content[i];
        if (part.type === 'image_url' && part.image_url) {
          var img = document.createElement('img');
          img.src = part.image_url.url;
          imgGrid.appendChild(img);
        } else if (part.type === 'text') {
          textParts.push(part.text);
        }
      }
      if (imgGrid.children.length > 0) container.appendChild(imgGrid);
      if (textParts.length) {
        var textDiv = document.createElement('div');
        textDiv.innerHTML = renderMarkdown(textParts.join('\n'));
        container.appendChild(textDiv);
      }
    } else {
      container.innerHTML = renderMarkdown(content || '');
    }
  }

  function updateMessageEl(bodyEl, content, streaming) {
    if (!bodyEl) return;
    bodyEl.innerHTML = renderMarkdown(content || '') + (streaming ? '<span class="cursor"></span>' : '');
    if ($('messages')) $('messages').scrollTop = $('messages').scrollHeight;
  }

  function setStatus(text, type) {
    type = type || 'ready';
    if ($('statusText')) $('statusText').textContent = text;
    var dot = $('statusDot');
    if (!dot) return;
    dot.className = 'status-dot';
    if (type === 'error') dot.classList.add('error');
    else if (type === 'busy') dot.classList.add('busy');
  }

  function startTimer() {
    var timerEl = $('timer');
    if (!timerEl) return;
    timerEl.style.display = 'inline';
    timerEl.textContent = '0s';
    var seconds = 0;
    timerInterval = setInterval(function() {
      seconds++;
      if (seconds < 60) {
        timerEl.textContent = seconds + 's';
      } else {
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        timerEl.textContent = mins + 'm ' + secs + 's';
      }
    }, 1000);
  }

  function stopTimer() {
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }
  }

  async function streamChat(msgs, bodyEl, metaEl, silent, overallStartTime, messageRef) {
    var assistantText = '';
    var totalTokens = 0;
    var startTime = overallStartTime || Date.now();

    if (!silent) abortController = new AbortController();

    var res = await fetch(cfg.chatUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ messages: msgs }),
      signal: abortController ? abortController.signal : undefined
    });

    if (!res.ok) {
      var errText = await res.text();
      throw new Error('HTTP ' + res.status + ': ' + errText);
    }

    var reader = res.body.getReader();
    var decoder = new TextDecoder();
    var buffer = '';

    while (true) {
      var result = await reader.read();
      if (result.done) break;
      buffer += decoder.decode(result.value, { stream: true });
      var lines = buffer.split('\n');
      buffer = lines.pop();

      for (var li = 0; li < lines.length; li++) {
        var trimmed = lines[li].trim();
        if (!trimmed || trimmed.indexOf('data:') !== 0) continue;
        var data = trimmed.slice(5).trim();
        if (data === '[DONE]') continue;
        try {
          var json = JSON.parse(data);
          if (json.error) throw new Error(json.error);
          var delta = '';
          if (json.choices && json.choices[0]) {
            if (json.choices[0].delta && json.choices[0].delta.content) delta = json.choices[0].delta.content;
            else if (json.choices[0].message && json.choices[0].message.content) delta = json.choices[0].message.content;
          }
          if (delta) {
            assistantText += delta;
            if (!silent && bodyEl) updateMessageEl(bodyEl, assistantText, true);
          }
          if (json.usage && json.usage.total_tokens) totalTokens = json.usage.total_tokens;
        } catch (e) {
          if (e.message && e.message.indexOf('JSON') === -1) throw e;
        }
      }
    }

    var responseTime = Date.now() - startTime;
    if (!totalTokens) totalTokens = Math.ceil(assistantText.length / 4);

    if (!silent && bodyEl) {
      updateMessageEl(bodyEl, assistantText, false);
      if (metaEl) updateMessageMeta(metaEl, responseTime, totalTokens);
      if (messageRef) {
        setMessageRawContent(messageRef, assistantText);
        showCopyButton(messageRef);
      }
    }

    return assistantText;
  }

  async function send() {
    var input = $('input');
    if (!input) return;
    var text = input.value.trim();
    if (!text && pendingImages.length === 0) return;

    var overallStartTime = Date.now();
    var baseUrl = $('baseUrl') ? $('baseUrl').value.trim() : '';
    var apiKey = $('apiKey') ? $('apiKey').value.trim() : '';
    var model = $('modelId') ? $('modelId').value.trim() : '';

    if (!baseUrl || !apiKey || !model) {
      setStatus('Configure API settings first', 'error');
      openSidebar();
      return;
    }

    var imagesToSend = pendingImages.slice();
    var BATCH_SIZE = 10;
    var needsBatching = imagesToSend.length > BATCH_SIZE;

    input.value = '';
    input.style.height = 'auto';
    input.placeholder = 'Type your message...';
    pendingImages = [];
    if ($('fileInput')) $('fileInput').value = '';
    renderPreviews();
    input.focus();

    setBusy(true);
    startTimer();
    abortController = new AbortController();

    try {
      if (!needsBatching) {
        var userContent = [];
        if (text) userContent.push({ type: 'text', text: text });
        for (var i = 0; i < imagesToSend.length; i++) {
          userContent.push({ type: 'image_url', image_url: { url: imagesToSend[i] } });
        }
        var displayContent = (userContent.length === 1 && userContent[0].type === 'text') ? text : userContent;
        messages.push({ role: 'user', content: displayContent });
        appendMessageEl('user', displayContent);
        saveHistory();

        setStatus('Thinking...', 'busy');
        var assistantMsg = appendMessageEl('assistant', '', true);
        var result = await streamChat(messages, assistantMsg.body, assistantMsg.meta, false, overallStartTime, assistantMsg);
        messages.push({ role: 'assistant', content: result });
        saveHistory();
        setStatus('Ready');
      } else {
        var totalImages = imagesToSend.length;
        var batches = [];
        for (var bi = 0; bi < totalImages; bi += BATCH_SIZE) {
          batches.push(imagesToSend.slice(bi, bi + BATCH_SIZE));
        }
        var totalBatches = batches.length;

        appendMessageEl('user', [{ type: 'text', text: (text || 'Analyze these images') + ' (' + totalImages + ' images)' }]);
        var loadingMsg = appendMessageEl('assistant', '', true);
        var accumulatedData = '';

        for (var b = 0; b < totalBatches; b++) {
          if (abortController.signal.aborted) break;
          var batch = batches[b];
          var startIdx = b * BATCH_SIZE + 1;
          var endIdx = Math.min(startIdx + batch.length - 1, totalImages);
          setStatus('Reading images ' + startIdx + '-' + endIdx + ' of ' + totalImages + '...', 'busy');
          updateMessageEl(loadingMsg.body, '*[Reading images ' + startIdx + '-' + endIdx + ' of ' + totalImages + '...]*', true);

          var batchContent = [];
          var prompt = 'Extract all text and data from these images (images ' + startIdx + '-' + endIdx + ' of ' + totalImages + ').\n\nINSTRUCTIONS:\n1. Read every image in this batch carefully.\n2. Extract ALL text, numbers, tables, and visible data.\n3. Return the COMPLETE raw extraction.\n4. Do NOT summarize or analyze.\n5. Include everything you see.\n6. Preserve the structure.\n7. If previous extractions exist, do not repeat them.';
          batchContent.push({ type: 'text', text: prompt });
          for (var j = 0; j < batch.length; j++) {
            batchContent.push({ type: 'image_url', image_url: { url: batch[j] } });
          }

          var contextMessages = [];
          if (accumulatedData) {
            contextMessages.push({ role: 'assistant', content: 'PREVIOUSLY EXTRACTED DATA (do not repeat this):\n' + accumulatedData });
          }

          var stepMessages = messages.concat(contextMessages).concat([{ role: 'user', content: batchContent }]);
          var batchResult = await streamChat(stepMessages, null, null, true);
          accumulatedData += '\n\n--- BATCH ' + (b + 1) + ' (Images ' + startIdx + '-' + endIdx + ') ---\n' + batchResult;
        }

        if (!abortController.signal.aborted) {
          setStatus('Generating response...', 'busy');
          updateMessageEl(loadingMsg.body, '*[Applying your request to extracted data...]*', true);
          var finalPrompt = 'I have extracted ALL data from ' + totalImages + ' images across ' + totalBatches + ' batches. Here is the COMPLETE extracted data:\n\n---EXTRACTED DATA START---\n' + accumulatedData + '\n---EXTRACTED DATA END---\n\nIMPORTANT: Apply the following request to ALL of this data:\n\n' + (text || 'Analyze and summarize all the extracted data.');
          var finalMessages = messages.concat([{ role: 'user', content: finalPrompt }]);
          var finalResult = await streamChat(finalMessages, loadingMsg.body, loadingMsg.meta, false, overallStartTime, loadingMsg);
          var fullUserContent = [{ type: 'text', text: text || 'Analyze these images' }];
          for (var k = 0; k < imagesToSend.length; k++) {
            fullUserContent.push({ type: 'image_url', image_url: { url: imagesToSend[k] } });
          }
          messages.push({ role: 'user', content: fullUserContent });
          messages.push({ role: 'assistant', content: finalResult });
          saveHistory();
          setStatus('Ready');
        }
      }
    } catch (err) {
      if (err.name === 'AbortError') {
        setStatus('Stopped');
      } else {
        appendMessageEl('assistant', '**Error:** ' + err.message);
        setStatus('Error', 'error');
      }
    } finally {
      stopTimer();
      setBusy(false);
      abortController = null;
    }
  }

  async function testConnection() {
    var result = $('testResult');
    var btn = $('testBtn');
    if (!result || !btn) return;

    var baseUrl = $('baseUrl') ? $('baseUrl').value.trim() : '';
    var apiKey = $('apiKey') ? $('apiKey').value.trim() : '';
    var model = $('modelId') ? $('modelId').value.trim() : '';

    if (!baseUrl || !apiKey || !model) {
      result.className = 'test-result error';
      result.textContent = 'Fill all fields first';
      return;
    }

    await saveConfig();
    btn.disabled = true;
    btn.innerHTML = '<svg width="14" height="14" style="animation:nolsaiSpin 1s linear infinite" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v4m0 12v4m10-10h-4M6 12H2"/></svg> Testing...';
    result.className = 'test-result';
    result.textContent = '';

    try {
      var res = await fetch(cfg.testConnectionUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({})
      });
      var data = await res.json();
      if (data.status === 'success' || data.status === 'ok') {
        result.className = 'test-result success';
        result.textContent = '✓ Connected';
      } else {
        result.className = 'test-result error';
        result.textContent = '✗ Failed';
      }
    } catch (err) {
      result.className = 'test-result error';
      result.textContent = '✗ Error';
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Test Connection';
    }
  }

  function stop() {
    if (abortController) abortController.abort();
  }

  async function newChat() {
    if (messages.length) {
      if (window.Swal) {
        var result = await Swal.fire({
          title: 'Clear current chat?',
          text: 'This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Clear',
          cancelButtonText: 'Cancel',
          reverseButtons: true
        });
        if (!result.isConfirmed) return;
      } else if (!window.confirm('Clear current chat?')) {
        return;
      }
    }
    messages = [];
    saveHistory();
    renderAll();
    setStatus('Ready');
  }

  function exportChat() {
    var blob = new Blob([JSON.stringify(messages, null, 2)], { type: 'application/json' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'nolsai-chat-' + new Date().toISOString().slice(0, 10) + '.json';
    a.click();
    URL.revokeObjectURL(a.href);
  }

  async function showVisitors() {
    try {
      setStatus('Loading visitors...', 'busy');
      var res = await fetch(cfg.visitorsUrl, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      var data = await res.json();
      setStatus('Ready');

      if (data.status !== 'success' || !data.data) {
        if (window.Swal) Swal.fire({ title: 'Error', text: data.message || 'Failed to load visitors', icon: 'error' });
        return;
      }

      var visitors = data.data.visitors || [];
      var total = data.data.total || visitors.length;
      var withQuestions = visitors.filter(function(v) { return v.question; }).length;
      var rows = visitors.slice(-50).reverse();
      var html = '<div class="nolsai-visitors-wrap"><p><strong>Total:</strong> ' + total + ' &nbsp;|&nbsp; <strong>With questions:</strong> ' + withQuestions + '</p>';
      html += '<table><thead><tr><th>IP</th><th>Question</th><th>Source</th><th>Time</th></tr></thead><tbody>';
      for (var i = 0; i < rows.length; i++) {
        var v = rows[i];
        html += '<tr><td>' + escapeHtml(v.ip || '') + '</td><td>' + escapeHtml(v.question || '-') + '</td><td>' + escapeHtml(v.source || '-') + '</td><td>' + escapeHtml(v.timestamp || '-') + '</td></tr>';
      }
      html += '</tbody></table><p style="margin-top:8px;font-size:11px;opacity:.7">Showing last 50 of ' + total + ' records</p></div>';

      if (window.Swal) {
        Swal.fire({
          title: 'Visitor Analytics',
          html: html,
          width: '800px',
          confirmButtonText: 'Close'
        });
      }
    } catch (e) {
      setStatus('Error', 'error');
      if (window.Swal) Swal.fire({ title: 'Error', text: e.message, icon: 'error' });
    }
  }

  function setBusy(busy) {
    if ($('sendBtn')) $('sendBtn').style.display = busy ? 'none' : 'flex';
    if ($('stopBtn')) $('stopBtn').style.display = busy ? 'flex' : 'none';
    if ($('input')) $('input').disabled = busy;
  }

  var input = $('input');
  if (input) {
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if ($('sendBtn') && !$('sendBtn').disabled) send();
      }
    });
    input.addEventListener('input', function() {
      input.style.height = 'auto';
      input.style.height = Math.min(input.scrollHeight, 200) + 'px';
    });
  }

  if ($('sendBtn')) $('sendBtn').addEventListener('click', send);
  if ($('stopBtn')) $('stopBtn').addEventListener('click', stop);
  if ($('testBtn')) $('testBtn').addEventListener('click', testConnection);
  if ($('newChatBtn')) $('newChatBtn').addEventListener('click', newChat);
  if ($('exportChatBtn')) $('exportChatBtn').addEventListener('click', exportChat);
  if ($('visitorsBtn')) $('visitorsBtn').addEventListener('click', showVisitors);

  loadConfig();
  if (input) input.focus();
})();
