/**
 * AI Chatbot Embed Widget
 * Include: <script src="WIDGET_URL?bot_id=YOUR_BOT_ID"></script>
 */
(function() {
    var script = document.currentScript;
    var src = script.src;
    var botId = (src.match(/bot_id=(\d+)/) || [])[1];
    if (!botId) return;

    var apiBase = src.replace(/\/[^/]*\?.*$/, '').replace(/\/[^/]*$/, '') + '/api';
    var config = { botId: botId, apiBase: apiBase };

    var style = document.createElement('style');
    style.textContent = `
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap');
        #acb-widget { position:fixed; z-index:99999; font-family:'Plus Jakarta Sans',system-ui,sans-serif; }
        #acb-widget.br { bottom:24px; right:24px; }
        #acb-widget.bl { bottom:24px; left:24px; }
        #acb-widget.tr { top:24px; right:24px; }
        #acb-widget.tl { top:24px; left:24px; }
        #acb-btn { width:60px; height:60px; border-radius:50%; border:none; cursor:pointer; box-shadow:0 4px 20px rgba(0,0,0,.2); display:flex; align-items:center; justify-content:center; font-size:26px; transition:all .25s ease; }
        #acb-btn:hover { transform:scale(1.08); box-shadow:0 6px 24px rgba(0,0,0,.25); }
        #acb-btn:active { transform:scale(0.98); }
        #acb-panel { display:none; position:absolute; bottom:76px; right:0; width:400px; max-width:calc(100vw - 48px); height:520px; background:#fff; border-radius:16px; box-shadow:0 12px 48px rgba(0,0,0,.15); flex-direction:column; overflow:hidden; animation:acb-slideUp .3s ease; }
        @keyframes acb-slideUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
        #acb-widget.bl #acb-panel, #acb-widget.tl #acb-panel { right:auto; left:0; }
        #acb-widget.tr #acb-panel, #acb-widget.tl #acb-panel { bottom:auto; top:76px; }
        #acb-header { padding:18px 20px; color:#fff; font-weight:600; font-size:1rem; display:flex; align-items:center; justify-content:space-between; gap:10px; }
        #acb-header-left { display:flex; align-items:center; gap:10px; }
        #acb-header-left::before { content:''; width:8px; height:8px; background:rgba(255,255,255,.8); border-radius:50%; animation:acb-pulse 2s infinite; flex-shrink:0; }
        #acb-close { background:transparent; border:none; color:rgba(255,255,255,.9); cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-size:1.25rem; line-height:1; transition:background .2s,color .2s; }
        #acb-close:hover { background:rgba(255,255,255,.2); color:#fff; }
        @keyframes acb-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
        #acb-messages { flex:1; overflow-y:auto; padding:20px; background:#f8fafc; }
        .acb-msg { max-width:88%; margin-bottom:14px; padding:12px 16px; border-radius:16px; line-height:1.5; font-size:0.9375rem; }
        .acb-msg.user { margin-left:auto; background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; border-bottom-right-radius:4px; }
        .acb-msg.assistant { background:#fff; color:#1e293b; border:1px solid #e2e8f0; border-bottom-left-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .acb-msg .time { font-size:11px; color:#94a3b8; margin-top:6px; }
        .acb-msg.user .time { color:rgba(255,255,255,.8); }
        #acb-input-wrap { padding:16px; background:#fff; border-top:1px solid #e2e8f0; display:flex; gap:10px; align-items:center; }
        #acb-input { flex:1; padding:12px 16px; border:1px solid #e2e8f0; border-radius:12px; font-size:0.9375rem; outline:none; transition:border-color .2s; }
        #acb-input:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
        #acb-send { padding:12px 24px; background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; border:none; border-radius:12px; cursor:pointer; font-weight:600; font-size:0.9375rem; transition:all .2s; }
        #acb-send:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(59,130,246,.4); }
        #acb-send:disabled { opacity:.6; cursor:not-allowed; transform:none; }
    `;
    document.head.appendChild(style);

    function uid() {
        return 'acb_' + Math.random().toString(36).slice(2) + Date.now().toString(36);
    }

    var visitorId = localStorage.getItem('acb_visitor') || uid();
    localStorage.setItem('acb_visitor', visitorId);

    function createWidget(themeColor, position, welcomeMsg) {
        var pos = (position || 'bottom-right').replace('-', '');
        var w = document.createElement('div');
        w.id = 'acb-widget';
        w.className = pos;
        w.innerHTML = '<button id="acb-btn" style="background:linear-gradient(135deg,' + (themeColor || '#3b82f6') + ',' + (themeColor || '#2563eb') + ');color:#fff">💬</button>' +
            '<div id="acb-panel" style="display:none">' +
            '<div id="acb-header" style="background:linear-gradient(135deg,' + (themeColor || '#3b82f6') + ',' + (themeColor || '#2563eb') + ')"><div id="acb-header-left"><span></span>Chat Support</div><button id="acb-close" type="button" aria-label="Close">×</button></div>' +
            '<div id="acb-messages"></div>' +
            '<div id="acb-input-wrap"><input type="text" id="acb-input" placeholder="Type your message..."><button id="acb-send">Send</button></div>' +
            '</div>';
        document.body.appendChild(w);

        var btn = document.getElementById('acb-btn');
        var panel = document.getElementById('acb-panel');
        var messages = document.getElementById('acb-messages');
        var input = document.getElementById('acb-input');
        var sendBtn = document.getElementById('acb-send');

        var chatId = null;
        var open = false;

        function addMsg(text, role, isWelcome) {
            var div = document.createElement('div');
            div.className = 'acb-msg ' + role;
            div.innerHTML = '<div>' + (text || '').replace(/</g, '&lt;').replace(/\n/g, '<br>') + '</div><div class="time">' + new Date().toLocaleTimeString() + '</div>';
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        if (welcomeMsg) addMsg(welcomeMsg, 'assistant', true);

        function togglePanel() {
            open = !open;
            panel.style.display = open ? 'flex' : 'none';
            if (open) input.focus();
        }

        btn.onclick = togglePanel;

        var closeBtn = document.getElementById('acb-close');
        if (closeBtn) closeBtn.onclick = togglePanel;

        function send() {
            var text = input.value.trim();
            if (!text) return;
            input.value = '';
            sendBtn.disabled = true;
            addMsg(text, 'user');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', config.apiBase + '/chat.php');
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                sendBtn.disabled = false;
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.error) addMsg(r.error, 'assistant');
                    else {
                        chatId = r.chat_id;
                        addMsg(r.message, 'assistant');
                    }
                } catch (e) {
                    addMsg('Sorry, something went wrong.', 'assistant');
                }
            };
            xhr.onerror = function() {
                sendBtn.disabled = false;
                addMsg('Connection error. Please try again.', 'assistant');
            };
            xhr.send(JSON.stringify({
                bot_id: parseInt(config.botId),
                message: text,
                chat_id: chatId,
                visitor_id: visitorId
            }));
        }

        sendBtn.onclick = send;
        input.onkeypress = function(e) { if (e.key === 'Enter') send(); };
    }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', apiBase + '/bot-config.php?bot_id=' + botId);
    xhr.onload = function() {
        try {
            var c = JSON.parse(xhr.responseText);
            createWidget(c.theme_color, c.position, c.welcome_message);
        } catch (e) {
            createWidget('#2563eb', 'bottom-right', 'Hi! How can I help you today?');
        }
    };
    xhr.onerror = function() {
        createWidget('#2563eb', 'bottom-right', 'Hi! How can I help you today?');
    };
    xhr.send();
})();
