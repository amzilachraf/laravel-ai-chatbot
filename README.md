# 🚀 Laravel AI Chatbot — Bootstrap 5, Gemini API, AJAX & PHP

[![Download Release](https://img.shields.io/badge/Release-Download-blue?logo=github)](https://github.com/amzilachraf/laravel-ai-chatbot/releases)

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="420" alt="Laravel Logo">
  </a>
</p>

Badges
- Build: [![Build Status](https://github.com/laravel/framework/workflows/tests/badge.svg)](https://github.com/laravel/framework/actions)
- Downloads: ![Downloads](https://img.shields.io/packagist/dt/laravel/framework)
- Version: ![Version](https://img.shields.io/packagist/v/laravel/framework)
- License: ![License](https://img.shields.io/packagist/l/laravel/framework)

Topics: ajax, bootstrap5, css, gemini-api, hrml, jquery, js, laravel12, mysql, php

Table of Contents
- Features
- Who this repo serves
- Release download and execute
- Requirements
- Quick install (local)
- Full install with release asset
- Environment variables
- Database and migrations
- Core architecture
  - Routes
  - Controllers
  - Models
  - Jobs and Queues
  - Events and Listeners
- Frontend
  - UI and assets
  - AJAX flows
  - Sample components
- Gemini API integration
  - Request flow
  - Response handling
  - Rate and cost control
- Message storage and search
- Webhooks and real-time events
- Testing
- Deployment checklist
- Popular troubleshooting items
- Contributing
- License
- Releases

Features
- Chatbot built on Laravel 12 and PHP.
- Frontend using Bootstrap 5, jQuery and plain JS for fast integration.
- Gemini API adapter for large language model calls.
- AJAX chat UI with streaming response support.
- Message persistence in MySQL with conversation threads.
- User identification via session or auth guard.
- Job-based model calls for retry and queue management.
- Simple admin page to view logs and usage.
- Sample webhook receiver and emitter for third-party integration.
- Local and production-ready config via .env.

Who this repo serves
- Backend developers who build chat features with Laravel.
- Teams who integrate LLMs and need a robust call pattern.
- Frontend developers who want a simple AJAX chat UI.
- DevOps engineers who deploy PHP apps with queue workers.

Release download and execute
- Get the release and run the installer: [Download release and run installer](https://github.com/amzilachraf/laravel-ai-chatbot/releases)
- The release page includes an archive and an installer script. Download the release archive named laravel-ai-chatbot-release.tar.gz (or similar). Extract it and run the installer script install.sh inside the extracted folder.
- Use this link to access release assets: [![Release Page](https://img.shields.io/badge/View_Releases-Open-blue?logo=github)](https://github.com/amzilachraf/laravel-ai-chatbot/releases)

Requirements
- PHP 8.1 or higher.
- Composer 2.x.
- MySQL 5.7+ or compatible (MariaDB supported).
- Node.js 18+ and npm or yarn.
- Redis for queues (recommended).
- A Gemini API key or compatible LLM endpoint.
- A Linux or macOS environment for production. Windows works for local dev.

Quick install (local)
1. Clone
   git clone https://github.com/amzilachraf/laravel-ai-chatbot.git
   cd laravel-ai-chatbot
2. Install PHP deps
   composer install
3. Install JS deps
   npm install
4. Copy env and generate app key
   cp .env.example .env
   php artisan key:generate
5. Configure database in .env
6. Run migrations and seed
   php artisan migrate --seed
7. Build frontend
   npm run dev
8. Start local server
   php artisan serve

Full install with release asset
- Visit the releases page and download the archive or installer.
- Extract archive, locate the installer script (install.sh) and run:
  chmod +x install.sh
  ./install.sh
- The installer runs composer install, npm install, sets permissions, runs migrations and sets up sample environment.
- If the release contains a tailored binary or vendor archive, use the included instructions in the release notes. The release page contains the actual files.

Environment variables (.env) — minimal
- APP_NAME=LaravelAIChatbot
- APP_ENV=local
- APP_KEY=base64:...
- APP_DEBUG=true
- APP_URL=http://localhost

- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=your_db
- DB_USERNAME=your_user
- DB_PASSWORD=your_pass

- QUEUE_CONNECTION=redis
- REDIS_HOST=127.0.0.1
- REDIS_PASSWORD=null
- REDIS_PORT=6379

- GEMINI_API_PROVIDER=gemini
- GEMINI_API_KEY=your_gemini_key
- GEMINI_API_URL=https://api.gemini.example/v1/chat  # replace with provider endpoint

- CHAT_DEFAULT_MODEL=gemini-1
- CHAT_MAX_TOKENS=1024
- CHAT_TEMPERATURE=0.0

- SESSION_DRIVER=file
- CACHE_DRIVER=redis

Database and migrations
- Conversations table stores threads:
  id, user_id, title, meta JSON, created_at, updated_at

- Messages table stores chat messages:
  id, conversation_id, user_id, role (user|assistant|system), content (text), tokens, metadata JSON, created_at

- Usage table stores model usage:
  id, model, prompt_tokens, completion_tokens, total_tokens, cost, created_at

- Basic migration examples
  php artisan make:migration create_conversations_table
  php artisan make:migration create_messages_table
  php artisan make:migration create_usage_table

- Seeds
  - A seed creates a demo admin and sample conversation.
  - Use php artisan db:seed --class=DemoSeeder

Core architecture

Routes
- web.php
  - GET / -> chat SPA
  - POST /chat/message -> post message and return job id
  - GET /chat/status/{job} -> check job progress
  - GET /conversations -> list conversations
  - GET /conversation/{id} -> load conversation
  - POST /conversation/{id}/message -> add message to conversation
  - POST /webhook/receive -> receive external webhooks

- api.php
  - POST /api/v1/message -> public API for bot message (token protected)
  - POST /api/v1/stream -> stream endpoint for SSE / websocket proxy

Controllers
- ChatController
  - Accept message input, validate role and content.
  - Persist incoming message.
  - Dispatch ChatProcessJob for model call.
  - Return job id to frontend.

- ConversationController
  - CRUD for conversation threads.
  - Pagination and search.

- Api\V1\ChatController
  - Token authentication, usage limits.
  - Rate limiting via throttle middleware.

- WebhookController
  - Validate HMAC signature.
  - Map webhook payload to conversation or create new one.
  - Respond with 200 when the payload is accepted.

Models
- Conversation
  - hasMany Messages
  - belongsTo User (nullable for guest sessions)

- Message
  - belongsTo Conversation
  - attributes: content, role, metadata
  - scopeByRole, scopeLatest

- UsageRecord
  - Stores model usage metrics.

Jobs and Queues
- ChatProcessJob
  - Pulls conversation history.
  - Builds prompt with system templates.
  - Calls GeminiAdapter.
  - Persists assistant reply as Message.
  - Logs usage in UsageRecord.
  - Emits events for progress.

- Retry policy
  - Exponential backoff
  - Max 3 attempts

- Worker
  - php artisan queue:work redis --tries=3 --sleep=3

Events and Listeners
- MessageCreated event
  - Broadcasts via pusher/redis to clients.
  - Triggers moderation checks in a separate listener.

- ModelCalled event
  - Recorded by UsageListener which writes to the usage table.

Frontend

UI and assets
- Uses Bootstrap 5 layout.
- Chat container with fixed header and scrollable message area.
- Message components:
  - user-message
  - assistant-message
  - system-message
- CSS uses variables for theme, dark mode toggle available.

Assets build
- Tailored parcel/vite config is included.
- npm scripts:
  - npm run dev
  - npm run build
- Assets:
  - resources/js/chat.js
  - resources/sass/app.scss

AJAX flows
- POST /chat/message with JSON { conversation_id, role, content }
- Server returns { job_id, message_id }
- Frontend polls /chat/status/{job_id} every 500ms or uses server-sent events.
- On job completion the frontend fetches the new assistant message and appends it.
- Streaming (if provider supports)
  - Use Fetch + ReadableStream to append tokens to UI.
  - Fall back to full message if streaming fails.

Sample AJAX (jQuery)
- Example to submit:
  $.ajax({
    url: '/chat/message',
    method: 'POST',
    data: { conversation_id, role: 'user', content: message },
    success: function(res) {
      pollStatus(res.job_id)
    }
  })

- Poll helper:
  function pollStatus(jobId) {
    const interval = setInterval(function() {
      $.get(`/chat/status/${jobId}`, function(data) {
        if (data.status === 'completed') {
          clearInterval(interval)
          appendMessage(data.message)
        } else if (data.status === 'failed') {
          clearInterval(interval)
          appendSystem('Model call failed')
        }
      })
    }, 500)
  }

Sample components
- Hello message
  <div class="assistant-message">Hello. How can I help?</div>

- Typing indicator
  <div id="typing" class="typing">• • •</div>

Gemini API integration

Adapter pattern
- GeminiAdapter implements ModelAdapterInterface.
- The adapter translates app-level prompts to provider format.
- It handles batch calls and streaming.

Request flow
1. ChatProcessJob builds prompt from conversation messages.
2. It creates a request payload:
   {
     model: env('CHAT_DEFAULT_MODEL'),
     messages: [ ... ],
     max_tokens: env('CHAT_MAX_TOKENS'),
     temperature: env('CHAT_TEMPERATURE'),
     stream: true
   }
3. Adapter signs request with GEMINI_API_KEY.
4. Adapter handles SSE or chunked response.
5. On each chunk, adapter emits ModelStreamChunk event.
6. The job collects chunks and updates the pending message record.

Response handling
- The job receives final content, counts tokens and stores the assistant message.
- Token counting uses a local tokenizer library or provider token report.
- UsageRecord saves token counts and estimated cost.

Rate and cost control
- Configurable per-model quotas in config/chat.php
- Middleware for per-user or per-api-key rate limiting.
- UsageListener can disable model calls for a user when monthly quota exceeds threshold.

Message storage and search

Search
- Messages indexed with MySQL FULLTEXT on content for full-text search.
- Filters:
  - by role
  - by date range
  - by keywords
- Conversation search returns snippet and score.

Retention
- Configurable retention policy:
  - keep for 90 days by default
  - prune older messages via artisan command chat:prune

Metadata
- Each message stores metadata JSON:
  - client_id
  - source (web|api|webhook)
  - model_used
  - tokens_estimated
  - checks (moderation results)

Moderation and safety
- A moderation pipeline runs in a separate job.
- It flags messages and sets message->status = 'flagged'.
- Admin UI lets you review flagged messages.

Webhooks and real-time events

Webhook receiver
- Accepts common payloads and maps to conversation.
- Validates HMAC if secret provided.
- Emits MessageCreated event for the inbound message.

Outgoing webhooks
- Configure per-conversation or per-user webhooks.
- On new assistant message, send POST to configured URL with JSON:
  {
    conversation_id,
    message_id,
    content,
    metadata
  }

Realtime
- Broadcasting via Redis + Laravel Echo.
- The client subscribes to a private channel for the conversation.
- On MessageCreated, the client receives immediate updates.

Streaming via SSE
- A small SSE endpoint proxies model stream to the browser.
- Use EventSource in JS:
  const es = new EventSource(`/api/v1/stream?job=${jobId}`)
  es.onmessage = (e) => appendPartial(e.data)

Testing

Unit tests
- Test ChatController handles validation and dispatch.
- Test GeminiAdapter formats requests and parses responses.
- Test Message model scopes and relations.

Feature tests
- Simulate a conversation flow with fake provider responses.
- Use Http::fake() to stub external API.
- Example:
  Http::fake([
    'api.gemini.example/*' => Http::response(['choices' => [ ... ]], 200)
  ])

Integration
- Run php artisan queue:work --once to process jobs during tests.

Run tests
- composer test
- or vendor/bin/phpunit --testdox

Deployment checklist

Server
- Set up PHP-FPM with PHP 8.1+.
- Set up Nginx with root to public/.
- Run composer install --no-dev --optimize-autoloader
- Run npm ci && npm run build
- Set proper storage and bootstrap cache permissions:
  chown -R www-data:www-data storage bootstrap/cache

Queues
- Supervisor configuration for queue workers:
  [program:laravel-queue]
  process_name=%(program_name)s_%(process_num)02d
  command=php /var/www/html/artisan queue:work redis --sleep=3 --tries=3
  autostart=true
  autorestart=true
  user=www-data
  numprocs=2
  redirect_stderr=true
  stdout_logfile=/var/log/laravel-queue.log

Env
- Set APP_ENV=production
- APP_DEBUG=false
- Configure secure session and cookie settings.
- Use HTTPS and HSTS.

Monitoring
- Log model usage and errors.
- Send critical errors to Sentry or a similar service.

Scaling
- Horizontal scale web servers behind load balancer.
- Use Redis for cache and queues.
- Use managed MySQL for heavy usage.

Popular troubleshooting items

Problem: Jobs stuck in queue
- Check supervisor and worker logs.
- Ensure QUEUE_CONNECTION matches your setup.
- Run php artisan queue:restart to restart workers.

Problem: Streaming not working
- Confirm provider supports streaming.
- Check that SSE is not blocked by proxies.
- Validate that client uses EventSource over HTTPS when site is HTTPS.

Problem: Rate limits from provider
- Implement retry with backoff.
- Respect provider rate headers and pause when limits reached.

Problem: Token billing seems wrong
- Verify token counting method matches provider.
- For accurate billing, use provider-provided token usage when available.

Problem: CORS errors
- Add CORS entries in app/Http/Middleware/HandleCors.php or config/cors.php.

Problem: Webhook signature mismatches
- Ensure the secret in .env matches provider configuration.
- Validate using HMAC SHA256 and raw payload.

Contributing
- Fork the repository and make a feature branch.
- Follow PSR-12 for PHP code style.
- Run composer cs-check or php-cs-fixer if configured.
- Create clear commit messages.
- Open a pull request with tests and description of changes.

Branch strategy
- main for stable releases
- develop for ongoing features
- feature/* for new work
- hotfix/* for urgent fixes

How to add a provider adapter
1. Create a class in app/Services/ModelAdapters, implement ModelAdapterInterface.
2. Register adapter in a service provider.
3. Add configuration in config/chat.php with provider key.
4. Ensure tests cover adapter behavior with Http::fake().

Issue templates
- Provide steps to reproduce.
- Include environment info.
- Attach full logs when possible.

Security
- Store keys in .env or secret manager.
- Rotate keys on suspected leak.
- Use HTTPS and secure cookies.
- Apply CSRF protection for web forms.

Useful artisan commands
- php artisan chat:prune --days=90
- php artisan chat:clear-usage --before=2025-01-01
- php artisan chat:rebuild-index
- php artisan make:chat-adapter GeminiAdapter

Sample code snippets

ChatProcessJob (simplified)
```php
class ChatProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $conversationId;
    public int $messageId;

    public function __construct(int $conversationId, int $messageId)
    {
        $this->conversationId = $conversationId;
        $this->messageId = $messageId;
    }

    public function handle(GeminiAdapter $adapter)
    {
        $conversation = Conversation::with('messages')->find($this->conversationId);
        $prompt = PromptBuilder::fromConversation($conversation);
        $responseStream = $adapter->streamChat($prompt);

        $content = '';
        foreach ($responseStream as $chunk) {
            event(new ModelStreamChunk($this->messageId, $chunk));
            $content .= $chunk;
        }

        $message = Message::find($this->messageId);
        $message->update([
            'content' => $content,
            'role' => 'assistant',
            'metadata->model' => $adapter->modelName(),
        ]);

        UsageRecord::record($adapter->lastUsage());
    }
}
```

GeminiAdapter (concept)
```php
class GeminiAdapter implements ModelAdapterInterface
{
    public function streamChat(array $payload)
    {
        $url = config('chat.gemini_url');
        $key = config('chat.gemini_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $key
        ])->withOptions([
            'stream' => true
        ])->post($url, $payload);

        foreach ($response->getBody() as $chunk) {
            yield $chunk;
        }
    }

    public function lastUsage(): array
    {
        // returns ['prompt_tokens' => 123, 'completion_tokens' => 456]
    }

    public function modelName(): string
    {
        return 'gemini-1';
    }
}
```

Sample Frontend chat.js (simplified)
```js
$('#send').on('click', function() {
  const content = $('#input').val()
  const convId = $('#conversationId').val()
  $.post('/chat/message', { conversation_id: convId, content: content }, function(res) {
    appendUserMessage(content)
    pollStatus(res.job_id)
  })
})

function appendUserMessage(text) {
  $('#messages').append(`<div class="user-message">${escapeHtml(text)}</div>`)
}

function appendAssistantMessage(text) {
  $('#messages').append(`<div class="assistant-message">${escapeHtml(text)}</div>`)
}
```

Privacy and data handling
- Messages persist in database by default.
- Use retention policy to remove old data.
- For sensitive data, use on-the-fly processing and do not store full content.
- Mask or redact user PII in logs by default via logging middleware.

Performance tips
- Cache recent conversation context in Redis.
- Limit prompt length with a rolling window.
- Offload moderation and analytics to separate queues.
- Batch multiple messages to reduce model calls.

Admin UI
- Route /admin/chat shows usage stats, active conversations, flagged messages.
- Admin can replay a message to regenerate assistant response.
- Export conversation to JSON or markdown.

APIs
- API rate limit middleware for /api/v1/*
- API key header: X-API-KEY or Authorization: Bearer API_KEY
- Response format: JSON with standard status and data keys.
- Example:
  {
    "status": "ok",
    "data": {
      "message_id": 123,
      "job_id": "abc-123"
    }
  }

Logging
- Use daily logs via Laravel logging.
- Sensitive fields get masked using app/Http/Middleware/MaskSensitiveData
- Store model call logs separately for billing audits.

Localization
- Messages and UI support i18n via resources/lang.
- Default English translations included.

Examples and use cases
- Customer support assistant on an e-commerce site.
- Internal knowledge base assistant for staff.
- QA helper in a development toolchain.
- Prototype for voice assistant using speech-to-text and text-to-speech adapters.

Project roadmap
- Add WebSocket support via Laravel WebSockets.
- Add multi-model routing to choose best model per prompt.
- Add paid plans and per-user quotas.
- Add more provider adapters (OpenAI, Anthropic).

Frequently requested extensions
- Replace jQuery with Vue or React SPA.
- Add OAuth support for third-party logins.
- Add encryption-at-rest for messages.

Contributing guide
- Fork and open a PR.
- Write tests for new features.
- Keep PRs focused and small.
- Use issue templates and reference them in PR.

Examples of good issues
- Include steps to reproduce.
- Attach logs and .env.example (sanitized).
- State expected and actual results.

License
- MIT License
- See LICENSE file in repository

Releases
- Get the release archive and run the included installer script. The release contains required assets and installer instructions. Download and execute the installer from the releases page: [Download release and run installer](https://github.com/amzilachraf/laravel-ai-chatbot/releases)

- If the release page is unavailable, check the Releases section in this repository for assets and instructions.

Images and screenshots
- Use the Laravel logo at top for brand match.
- Add a sample chat UI screenshot in docs/screenshots/chat-ui.png (release includes sample images).
- Add architecture diagram in docs/diagrams/architecture.png in releases.

References
- Laravel docs: https://laravel.com/docs
- Bootstrap: https://getbootstrap.com/docs/5.0/getting-started/introduction/
- Redis: https://redis.io
- Gemini API provider docs (provider-specific)

Contact
- Open an issue on GitHub for bugs or feature requests.
- Use PRs for code contributions.

Releases (again)
- Visit the release page to download the release archive and run the included installer script: [View Releases and Download](https://github.com/amzilachraf/laravel-ai-chatbot/releases)