# DevDocs

PHP 8 and Bootstrap 5 tutorial portal with a learner library, admin publishing tools, lesson quizzes, local progress, and optional OpenAI assistance.

## Local setup

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Copy `.env.example` to `.env` and set database credentials if your XAMPP MySQL account differs from the defaults.
3. Import `database/schema.sql` through phpMyAdmin. The script creates the `devdocs` database and seeds sample courses, a lesson, and a quiz. It is safe to run again.
4. Open `http://localhost/mytutorials/`.
5. Visit `http://localhost/mytutorials/admin/setup.php` from this computer to create the first administrator. Use a long unique password. Remote first-admin setup requires a private `ADMIN_SETUP_KEY` in `.env`.

## Features

- Learners can browse published courses and lessons, search lesson content, answer quizzes, save lessons, and keep completion progress in the current browser.
- Admins can create, edit, publish, and delete courses and lessons. Lesson quiz changes are saved with the lesson.
- Admin lesson drafts and natural-language search can use OpenAI after an explicit consent checkbox and confirmation. AI-generated content remains unpublished until an admin reviews and saves it.
- AI requests send only the context needed for that action. Submitted code is reviewed as text and is never executed by the server. Requests are limited to 10 per session per minute.

## OpenAI

Add an OpenAI API key to the local `.env` file as `OPENAI_API_KEY`. The key is read only by PHP and must never be committed or pasted into browser JavaScript. AI requests are unavailable until a key is configured; the rest of the portal works without one. OpenAI usage may incur charges.

## Save-to-GitHub automation

This workspace has a local VS Code task that watches file changes, waits five seconds after the last change, creates a commit, and pushes `main` to `origin`. The task settings are excluded from Git so another clone will not auto-publish its changes. Because this repository is public, every saved change is published after the debounce; keep `.env` and other secrets out of tracked files.