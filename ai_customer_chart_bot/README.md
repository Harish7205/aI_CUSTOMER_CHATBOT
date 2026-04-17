# AI Customer Support Chatbot Builder

Businesses upload FAQs or company documents, and the system creates an AI chatbot they can embed on their website.

## Features

- **Business account registration** – Multi-tenant support
- **Knowledge base upload** – Documents (txt, pdf, doc, docx, md, csv)
- **FAQ manager** – Add/edit/delete Q&A pairs
- **AI chatbot** – Trained on uploaded content via OpenAI API
- **Chat widget** – Embeddable JavaScript snippet
- **Chat logs** – View conversation history
- **Analytics dashboard** – Chats, messages, visitors
- **Embed code management** – Copy-paste script for your site

## Tech Stack

- **Frontend:** HTML, CSS, Bootstrap 5, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **AI:** OpenAI API (GPT-3.5-turbo)
- **Document parsing:** PHP (txt, md, csv, docx)

## Setup

### 1. Database

```bash
mysql -u root -p < database/schema.sql
```

Or import `database/schema.sql` via phpMyAdmin.

### 2. Configuration

Edit `config/config.php`:

- Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` for MySQL
- Set `APP_URL` to your base URL (e.g. `http://localhost/ai_customer_chart_bot`)
- Set `OPENAI_API_KEY` (or use environment variable)

### 3. Permissions

Ensure the `uploads/` directory exists and is writable:

```bash
mkdir uploads
chmod 755 uploads
```

### 4. Run

- Place the project in your web server document root (e.g. XAMPP `htdocs`)
- Visit `http://localhost/ai_customer_chart_bot`
- Register a business account, create a bot, upload documents/FAQs, and get your embed code

## Project Structure

```
ai_customer_chart_bot/
├── admin/           # Admin panel (dashboard, bots, documents, FAQs, chats, analytics, embed)
├── api/             # Chat API, bot config
├── config/          # Database, app config
├── database/        # SQL schema
├── includes/        # Auth, DocumentParser
├── uploads/         # Uploaded documents
├── widget/          # Embeddable chat widget JS
├── index.php
├── login.php
├── register.php
├── logout.php
└── test-widget.html # Test page for widget
```

## Embed Code

After creating a bot, go to **Embed Code** in the admin panel. Add the generated script to your website:

```html
<script src="YOUR_APP_URL/widget/chat-widget.js?bot_id=YOUR_BOT_ID"></script>
```

## OpenAI API

The chatbot uses GPT-3.5-turbo. Set your API key in `config/config.php` or via `OPENAI_API_KEY` environment variable.

## PDF Parsing

For PDF support, you can install `smalot/pdfparser` via Composer:

```bash
composer require smalot/pdfparser
```

Or use `pdftotext` (from poppler-utils) if available on the system.
