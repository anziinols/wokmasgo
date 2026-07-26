/* Nolsai AI Chat Styles */
.main-content {
  padding: 0 !important;
  min-height: calc(100vh - 140px);
}

.footer {
  display: none;
}

.nolsai-app {
  --nolsai-bg: #0a0a0f;
  --nolsai-bg-grad: linear-gradient(135deg, #0a0a0f 0%, #111118 50%, #0d0d14 100%);
  --nolsai-surface: #13131d;
  --nolsai-surface-hover: #1a1a27;
  --nolsai-surface-active: #1f1f2e;
  --nolsai-border: rgba(255,255,255,.06);
  --nolsai-border-bright: rgba(255,255,255,.1);
  --nolsai-text: #f0f0f5;
  --nolsai-text-sec: #a0a0b8;
  --nolsai-text-muted: #6b6b80;
  --nolsai-red: #ef4444;
  --nolsai-red-glow: rgba(239,68,68,.15);
  --nolsai-red-soft: #dc2626;
  --nolsai-green: #22c55e;
  --nolsai-green-glow: rgba(34,197,94,.15);
  --nolsai-radius: 12px;
  --nolsai-radius-sm: 8px;
  --nolsai-radius-xs: 6px;
  --nolsai-shadow: 0 4px 24px rgba(0,0,0,.4);
  --nolsai-shadow-lg: 0 8px 40px rgba(0,0,0,.5);
  --nolsai-transition: all .2s cubic-bezier(.4,0,.2,1);

  display: flex;
  height: calc(100vh - 82px);
  height: calc(100dvh - 82px);
  position: relative;
  background: var(--nolsai-bg);
  background-image: var(--nolsai-bg-grad);
  color: var(--nolsai-text);
  font-family: 'Inter', 'Poppins', system-ui, -apple-system, sans-serif;
  font-size: 14px;
  -webkit-font-smoothing: antialiased;
  overflow: hidden;
}

.nolsai-app *,
.nolsai-app *::before,
.nolsai-app *::after {
  box-sizing: border-box;
}

.nolsai-logo-sm {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  object-fit: cover;
}

.nolsai-logo-top {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
}

.nolsai-logo-empty {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  object-fit: cover;
}

/* Sidebar */
.nolsai-app .sidebar {
  width: 320px;
  background: var(--nolsai-surface);
  border-right: 1px solid rgba(34,197,94,.15);
  display: flex;
  flex-direction: column;
  z-index: 100;
  transition: transform .3s cubic-bezier(.4,0,.2,1);
  position: relative;
  box-shadow: 5px 0 30px rgba(34,197,94,.08);
  flex-shrink: 0;
}

.nolsai-app .sidebar-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--nolsai-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.nolsai-app .sidebar-header h2 {
  font-size: 15px;
  font-weight: 600;
  letter-spacing: -.3px;
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  color: var(--nolsai-text);
}

.nolsai-app .sidebar-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px;
}

.nolsai-app .sidebar-section {
  margin-bottom: 24px;
}

.nolsai-app .sidebar-section-title {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--nolsai-text-muted);
  margin-bottom: 12px;
}

.nolsai-app .field {
  margin-bottom: 16px;
}

.nolsai-app .field label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  color: var(--nolsai-text-sec);
  margin-bottom: 6px;
}

.nolsai-app .field input,
.nolsai-app .field textarea {
  width: 100%;
  background: var(--nolsai-bg);
  border: 1px solid var(--nolsai-border-bright);
  color: var(--nolsai-text);
  padding: 10px 14px;
  border-radius: var(--nolsai-radius-sm);
  font-family: inherit;
  font-size: 13px;
  outline: none;
  transition: var(--nolsai-transition);
}

.nolsai-app .field input:focus,
.nolsai-app .field textarea:focus {
  border-color: var(--nolsai-red);
  box-shadow: 0 0 0 3px var(--nolsai-red-glow);
}

.nolsai-app .field textarea {
  resize: vertical;
  min-height: 80px;
  line-height: 1.5;
}

.nolsai-app .field input::placeholder,
.nolsai-app .field textarea::placeholder {
  color: var(--nolsai-text-muted);
}

/* Buttons */
.nolsai-app .btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: var(--nolsai-radius-sm);
  font-family: inherit;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: var(--nolsai-transition);
  border: none;
  outline: none;
}

.nolsai-app .btn-primary {
  background: var(--nolsai-red-soft);
  color: #fff;
}

.nolsai-app .btn-primary:hover:not(:disabled) {
  background: var(--nolsai-red);
  box-shadow: 0 0 20px var(--nolsai-red-glow);
}

.nolsai-app .btn-ghost {
  background: transparent;
  color: var(--nolsai-text-sec);
  border: 1px solid var(--nolsai-border-bright);
}

.nolsai-app .btn-ghost:hover:not(:disabled) {
  background: var(--nolsai-surface-hover);
  color: var(--nolsai-text);
  border-color: var(--nolsai-text-muted);
}

.nolsai-app .btn-icon {
  width: 40px;
  height: 40px;
  padding: 0;
  background: var(--nolsai-surface-hover);
  color: var(--nolsai-text-sec);
  border: 1px solid var(--nolsai-border);
  border-radius: var(--nolsai-radius-sm);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--nolsai-transition);
  flex-shrink: 0;
}

.nolsai-app .btn-icon:hover:not(:disabled) {
  background: var(--nolsai-surface-active);
  color: var(--nolsai-text);
  border-color: var(--nolsai-text-muted);
}

.nolsai-app .btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.nolsai-app .btn:disabled {
  opacity: .4;
  cursor: not-allowed;
}

.nolsai-app .test-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
}

.nolsai-app .test-result {
  font-size: 12px;
  font-weight: 500;
}

.nolsai-app .test-result.success {
  color: var(--nolsai-green);
}

.nolsai-app .test-result.error {
  color: var(--nolsai-red);
}

/* Main */
.nolsai-app .main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
}

.nolsai-app .topbar {
  height: 60px;
  background: var(--nolsai-surface);
  border-bottom: 1px solid rgba(34,197,94,.12);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  flex-shrink: 0;
  box-shadow: 0 3px 15px rgba(34,197,94,.06);
  z-index: 10;
}

.nolsai-app .topbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  overflow: hidden;
}

.nolsai-app .topbar-title {
  font-size: 15px;
  font-weight: 600;
  letter-spacing: -.3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--nolsai-text);
}

.nolsai-app .topbar-model {
  font-size: 11px;
  color: var(--nolsai-text-muted);
  background: var(--nolsai-surface-hover);
  padding: 4px 10px;
  border-radius: 20px;
  border: 1px solid var(--nolsai-border);
  white-space: nowrap;
}

.nolsai-app .topbar-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

/* Messages */
.nolsai-app .messages {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  scroll-behavior: smooth;
}

.nolsai-app .message {
  display: flex;
  gap: 14px;
  max-width: 880px;
  width: 100%;
  animation: nolsaiFadeIn .3s ease;
}

@keyframes nolsaiFadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

.nolsai-app .message.user {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.nolsai-app .message-avatar {
  width: 36px;
  height: 36px;
  border-radius: var(--nolsai-radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 14px;
  font-weight: 600;
}

.nolsai-app .message.user .message-avatar {
  background: var(--nolsai-red-glow);
  color: var(--nolsai-red);
  border: 1px solid rgba(239,68,68,.2);
}

.nolsai-app .message.assistant .message-avatar {
  background: var(--nolsai-green-glow);
  color: var(--nolsai-green);
  border: 1px solid rgba(34,197,94,.2);
}

.nolsai-app .message-content {
  flex: 1;
  min-width: 0;
}

.nolsai-app .message-role {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-bottom: 6px;
  color: var(--nolsai-text-muted);
}

.nolsai-app .message.user .message-role {
  text-align: right;
}

.nolsai-app .message-bubble {
  padding: 14px 18px;
  border-radius: var(--nolsai-radius);
  line-height: 1.65;
  word-wrap: break-word;
  box-shadow: 0 0 15px rgba(34,197,94,.15), 0 0 5px rgba(34,197,94,.1);
}

.nolsai-app .message.user .message-bubble {
  background: linear-gradient(135deg, rgba(239,68,68,.1) 0%, rgba(239,68,68,.05) 100%);
  border: 1px solid rgba(239,68,68,.15);
  border-radius: var(--nolsai-radius) var(--nolsai-radius) var(--nolsai-radius-xs) var(--nolsai-radius);
  box-shadow: 0 0 15px rgba(239,68,68,.12), 0 0 5px rgba(239,68,68,.08);
}

.nolsai-app .message.assistant .message-bubble {
  background: var(--nolsai-surface);
  border: 1px solid rgba(34,197,94,.2);
  border-radius: var(--nolsai-radius) var(--nolsai-radius) var(--nolsai-radius) var(--nolsai-radius-xs);
  box-shadow: 0 0 20px rgba(34,197,94,.15), 0 0 8px rgba(34,197,94,.1), inset 0 0 20px rgba(34,197,94,.03);
}

/* Markdown */
.nolsai-app .md h1,
.nolsai-app .md h2,
.nolsai-app .md h3,
.nolsai-app .md h4,
.nolsai-app .md h5,
.nolsai-app .md h6 {
  margin: 16px 0 8px;
  font-weight: 600;
  line-height: 1.3;
  color: var(--nolsai-text);
}

.nolsai-app .md h1 {
  font-size: 1.4em;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--nolsai-border);
}

.nolsai-app .md h2 {
  font-size: 1.2em;
  padding-bottom: 6px;
  border-bottom: 1px solid var(--nolsai-border);
}

.nolsai-app .md h3 { font-size: 1.1em; }
.nolsai-app .md p { margin: 8px 0; }
.nolsai-app .md ul,
.nolsai-app .md ol { margin: 8px 0; padding-left: 24px; }
.nolsai-app .md li { margin: 4px 0; }

.nolsai-app .md blockquote {
  margin: 12px 0;
  padding: 10px 16px;
  border-left: 3px solid var(--nolsai-red);
  background: var(--nolsai-red-glow);
  border-radius: 0 var(--nolsai-radius-sm) var(--nolsai-radius-sm) 0;
  color: var(--nolsai-text-sec);
}

.nolsai-app .md a {
  color: var(--nolsai-red);
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: var(--nolsai-transition);
}

.nolsai-app .md a:hover { border-bottom-color: var(--nolsai-red); }
.nolsai-app .md hr { border: none; border-top: 1px solid var(--nolsai-border); margin: 16px 0; }

.nolsai-app .md table {
  border-collapse: collapse;
  margin: 12px 0;
  width: 100%;
  font-size: .92em;
  overflow-x: auto;
  display: block;
}

.nolsai-app .md th,
.nolsai-app .md td {
  border: 1px solid var(--nolsai-border);
  padding: 8px 14px;
  text-align: left;
}

.nolsai-app .md th {
  background: var(--nolsai-surface-hover);
  font-weight: 600;
}

.nolsai-app .md tr:nth-child(even) { background: rgba(255,255,255,.02); }

.nolsai-app .md code {
  background: rgba(255,255,255,.08);
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'SF Mono', Menlo, Consolas, monospace;
  font-size: .88em;
}

.nolsai-app .md pre {
  background: #08080d;
  border: 1px solid var(--nolsai-border);
  padding: 16px;
  border-radius: var(--nolsai-radius-sm);
  overflow-x: auto;
  margin: 12px 0;
  font-family: 'SF Mono', Menlo, Consolas, monospace;
  font-size: .86em;
  line-height: 1.6;
  position: relative;
}

.nolsai-app .md pre code {
  background: transparent;
  padding: 0;
  font-size: inherit;
}

.nolsai-app .code-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--nolsai-border);
}

.nolsai-app .code-lang {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: var(--nolsai-text-muted);
  font-weight: 600;
}

.nolsai-app .code-copy {
  font-size: 11px;
  color: var(--nolsai-text-muted);
  background: var(--nolsai-surface-hover);
  border: 1px solid var(--nolsai-border);
  padding: 4px 10px;
  border-radius: var(--nolsai-radius-xs);
  cursor: pointer;
  transition: var(--nolsai-transition);
}

.nolsai-app .code-copy:hover {
  color: var(--nolsai-text);
  border-color: var(--nolsai-text-muted);
}

.nolsai-app .code-copy.copied {
  color: var(--nolsai-green);
  border-color: rgba(34,197,94,.4);
}

.nolsai-app .message-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
}

.nolsai-app .msg-copy-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 500;
  font-family: inherit;
  color: var(--nolsai-text-muted);
  background: var(--nolsai-surface-hover);
  border: 1px solid var(--nolsai-border);
  padding: 5px 10px;
  border-radius: var(--nolsai-radius-xs);
  cursor: pointer;
  transition: var(--nolsai-transition);
}

.nolsai-app .msg-copy-btn:hover {
  color: var(--nolsai-text);
  border-color: var(--nolsai-text-muted);
  background: var(--nolsai-surface-active);
}

.nolsai-app .msg-copy-btn.copied {
  color: var(--nolsai-green);
  border-color: rgba(34,197,94,.4);
  background: var(--nolsai-green-glow);
}

.nolsai-app .msg-copy-btn svg {
  flex-shrink: 0;
}

.nolsai-app .md img {
  max-width: 100%;
  max-height: 400px;
  border-radius: var(--nolsai-radius-sm);
  margin: 8px 0;
  display: block;
  cursor: pointer;
}

.nolsai-app .img-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 8px 0;
}

.nolsai-app .img-grid img {
  max-width: 180px;
  max-height: 140px;
  border-radius: var(--nolsai-radius-xs);
}

/* Composer */
.nolsai-app .composer {
  padding: 16px 24px 20px;
  background: var(--nolsai-surface);
  border-top: 1px solid rgba(34,197,94,.12);
  flex-shrink: 0;
  box-shadow: 0 -3px 15px rgba(34,197,94,.06);
}

.nolsai-app .composer-inner {
  max-width: 880px;
  margin: 0 auto;
}

.nolsai-app .preview-bar {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
  overflow-x: auto;
  padding: 4px 0;
}

.nolsai-app .preview-bar.hidden { display: none; }

.nolsai-app .preview-thumb {
  position: relative;
  flex-shrink: 0;
  width: 60px;
  height: 60px;
  border-radius: var(--nolsai-radius-sm);
  overflow: hidden;
  border: 1px solid var(--nolsai-border);
}

.nolsai-app .preview-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.nolsai-app .preview-remove {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 20px;
  height: 20px;
  background: rgba(0,0,0,.8);
  color: #fff;
  border: none;
  border-radius: 50%;
  font-size: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nolsai-app .preview-remove:hover { background: var(--nolsai-red); }

.nolsai-app .composer-row {
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.nolsai-app .composer textarea {
  flex: 1;
  background: var(--nolsai-bg);
  border: 1px solid var(--nolsai-border-bright);
  color: var(--nolsai-text);
  padding: 12px 16px;
  border-radius: var(--nolsai-radius);
  font-family: inherit;
  font-size: 14px;
  resize: none;
  outline: none;
  min-height: 48px;
  max-height: 200px;
  line-height: 1.5;
  transition: var(--nolsai-transition);
}

.nolsai-app .composer textarea:focus {
  border-color: var(--nolsai-red);
  box-shadow: 0 0 0 3px var(--nolsai-red-glow);
}

.nolsai-app .composer textarea::placeholder { color: var(--nolsai-text-muted); }

.nolsai-app .composer-send {
  width: 48px;
  height: 48px;
  background: var(--nolsai-red-soft);
  color: #fff;
  border: none;
  border-radius: var(--nolsai-radius);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--nolsai-transition);
  flex-shrink: 0;
}

.nolsai-app .composer-send:hover:not(:disabled) {
  background: var(--nolsai-red);
  box-shadow: 0 0 20px var(--nolsai-red-glow);
  transform: scale(1.02);
}

.nolsai-app .composer-send:disabled {
  opacity: .4;
  cursor: not-allowed;
}

.nolsai-app .composer-send svg {
  width: 20px;
  height: 20px;
}

.nolsai-app .composer-stop {
  width: 48px;
  height: 48px;
  background: rgba(239,68,68,.1);
  color: var(--nolsai-red);
  border: 1px solid rgba(239,68,68,.2);
  border-radius: var(--nolsai-radius);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--nolsai-transition);
  flex-shrink: 0;
}

.nolsai-app .composer-stop:hover {
  background: var(--nolsai-red-glow);
  border-color: var(--nolsai-red);
}

.nolsai-app .sidebar-toggle {
  display: none;
  width: 40px;
  height: 40px;
  background: var(--nolsai-surface-hover);
  color: var(--nolsai-text-sec);
  border: 1px solid var(--nolsai-border);
  border-radius: var(--nolsai-radius-sm);
  cursor: pointer;
  align-items: center;
  justify-content: center;
  transition: var(--nolsai-transition);
  flex-shrink: 0;
}

.nolsai-app .sidebar-toggle:hover {
  background: var(--nolsai-surface-active);
  color: var(--nolsai-text);
}

.nolsai-app .sidebar-close-btn {
  display: none;
  width: 32px;
  height: 32px;
  background: var(--nolsai-surface-hover);
  color: var(--nolsai-text-sec);
  border: 1px solid var(--nolsai-border);
  border-radius: var(--nolsai-radius-xs);
  cursor: pointer;
  align-items: center;
  justify-content: center;
  transition: var(--nolsai-transition);
  flex-shrink: 0;
}

.nolsai-app .sidebar-close-btn:hover {
  background: var(--nolsai-surface-active);
  color: var(--nolsai-text);
}

.nolsai-app .sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.6);
  z-index: 90;
  backdrop-filter: blur(4px);
  opacity: 0;
  transition: opacity .3s;
}

.nolsai-app .sidebar-overlay.active {
  display: block;
  opacity: 1;
}

.nolsai-app .status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--nolsai-green);
  animation: nolsaiPulse 2s infinite;
}

.nolsai-app .status-dot.error {
  background: var(--nolsai-red);
  animation: none;
}

.nolsai-app .status-dot.busy {
  background: var(--nolsai-red);
  animation: nolsaiPulse 1s infinite;
}

@keyframes nolsaiPulse {
  0%, 100% { opacity: 1; }
  50% { opacity: .4; }
}

.nolsai-app .cursor::after {
  content: "▊";
  color: var(--nolsai-red);
  animation: nolsaiBlink 1s infinite;
  margin-left: 2px;
  font-size: .9em;
}

@keyframes nolsaiBlink {
  0%, 100% { opacity: 1; }
  50% { opacity: .2; }
}

.nolsai-app .loading-bubbles {
  display: flex;
  gap: 6px;
  padding: 8px 0;
  align-items: center;
}

.nolsai-app .loading-bubbles .bubble {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--nolsai-green);
  box-shadow: 0 0 10px var(--nolsai-green), 0 0 20px rgba(34,197,94,.5);
  animation: nolsaiBubble 1.4s infinite ease-in-out;
}

.nolsai-app .loading-bubbles .bubble:nth-child(1) { animation-delay: 0s; }
.nolsai-app .loading-bubbles .bubble:nth-child(2) { animation-delay: .2s; }
.nolsai-app .loading-bubbles .bubble:nth-child(3) { animation-delay: .4s; }

@keyframes nolsaiBubble {
  0%, 80%, 100% { transform: scale(.6); opacity: .4; }
  40% { transform: scale(1); opacity: 1; }
}

.nolsai-app .message-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
  font-size: 11px;
  color: var(--nolsai-text-muted);
}

.nolsai-app .message-meta svg {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
}

.nolsai-app .message-meta span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.nolsai-app .message.user .message-meta {
  justify-content: flex-end;
}

.nolsai-app ::-webkit-scrollbar { width: 6px; }
.nolsai-app ::-webkit-scrollbar-track { background: transparent; }
.nolsai-app ::-webkit-scrollbar-thumb {
  background: var(--nolsai-border-bright);
  border-radius: 3px;
}
.nolsai-app ::-webkit-scrollbar-thumb:hover { background: var(--nolsai-text-muted); }

.nolsai-app #fileInput { display: none; }

.nolsai-app .empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  padding: 40px;
  animation: nolsaiFadeIn .5s ease;
}

.nolsai-app .empty-icon {
  width: 80px;
  height: 80px;
  background: var(--nolsai-red-glow);
  border: 1px solid rgba(239,68,68,.15);
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  box-shadow: 0 0 30px rgba(34,197,94,.15), 0 0 15px rgba(34,197,94,.1);
}

.nolsai-app .empty-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--nolsai-text);
}

.nolsai-app .empty-desc {
  font-size: 13px;
  color: var(--nolsai-text-muted);
  max-width: 300px;
  line-height: 1.6;
}

/* Visitors modal table */
.nolsai-visitors-wrap {
  max-height: 400px;
  overflow: auto;
  text-align: left;
  font-size: 12px;
}

.nolsai-visitors-wrap table {
  width: 100%;
  border-collapse: collapse;
}

.nolsai-visitors-wrap th,
.nolsai-visitors-wrap td {
  border: 1px solid rgba(255,255,255,.08);
  padding: 6px 8px;
  vertical-align: top;
}

.nolsai-visitors-wrap th {
  background: rgba(34,197,94,.1);
  position: sticky;
  top: 0;
}

@media (max-width: 768px) {
  .nolsai-app .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    transform: translateX(-100%);
    box-shadow: var(--nolsai-shadow-lg);
    width: 85vw;
    max-width: 320px;
  }
  .nolsai-app .sidebar.open { transform: translateX(0); }
  .nolsai-app .sidebar-close-btn { display: flex; }
  .nolsai-app .sidebar-toggle { display: flex; }
  .nolsai-app .main { flex: 1 1 100%; width: 100%; min-width: 0; }
  .nolsai-app .topbar { padding: 0 12px; height: 56px; gap: 8px; }
  .nolsai-app .messages { padding: 12px; }
  .nolsai-app .composer { padding: 10px 12px 14px; }
  .nolsai-app .message { max-width: 100%; }
  .nolsai-app .message-avatar { width: 30px; height: 30px; font-size: 11px; }
  .nolsai-app .message-bubble { padding: 10px 12px; }
  .nolsai-app .composer textarea { font-size: 16px; padding: 10px 14px; }
  .nolsai-app .topbar-model { display: none; }
  .nolsai-app .topbar-title { font-size: 14px; }
}

@media (max-width: 480px) {
  .nolsai-app .sidebar { width: 92vw; }
  .nolsai-app .composer-send,
  .nolsai-app .composer-stop { width: 42px; height: 42px; }
  .nolsai-app .btn-icon { width: 36px; height: 36px; }
  .nolsai-app .preview-thumb { width: 52px; height: 52px; }
  .nolsai-app .message-bubble { padding: 8px 10px; font-size: 13px; }
  .nolsai-app .sidebar-header { padding: 14px 16px; }
  .nolsai-app .sidebar-body { padding: 14px 16px; }
  .nolsai-app .field input,
  .nolsai-app .field textarea { padding: 8px 12px; }
  .nolsai-app .composer-row { gap: 8px; }
}

/* SweetAlert2 theme */
.swal2-popup {
  background: #13131d !important;
  border: 1px solid rgba(34,197,94,.2) !important;
  border-radius: 16px !important;
  box-shadow: 0 8px 40px rgba(0,0,0,.6), 0 0 30px rgba(34,197,94,.15) !important;
  font-family: 'Inter', system-ui, sans-serif !important;
  color: #f0f0f5 !important;
}

.swal2-title {
  color: #f0f0f5 !important;
  font-size: 1.25rem !important;
  font-weight: 600 !important;
}

.swal2-html-container {
  color: #a0a0b8 !important;
  font-size: .95rem !important;
}

.swal2-confirm {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  color: #fff !important;
  border: none !important;
  border-radius: 10px !important;
  font-weight: 600 !important;
  padding: 12px 28px !important;
}

.swal2-cancel {
  background: #1f1f2e !important;
  color: #a0a0b8 !important;
  border: 1px solid rgba(255,255,255,.06) !important;
  border-radius: 10px !important;
  font-weight: 500 !important;
  padding: 12px 28px !important;
}

@keyframes nolsaiSpin {
  from { transform: rotate(0); }
  to { transform: rotate(360deg); }
}
