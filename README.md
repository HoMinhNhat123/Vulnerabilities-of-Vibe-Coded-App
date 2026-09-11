# Vulnerabilities of Vibe-Coded Apps

A research project testing how secure (or insecure) AI-generated web applications are by default — when the AI is given only functional requirements, with no security guidance one way or the other.

## Motivation

"Vibe coding" — generating full applications from prompts with little to no manual review (especially happened newbie fullstack development) — is becoming increasingly common. This project asks a simple question:

> When an AI is asked to build a typical web app (auth, user content, file uploads, search, admin panel) with *no explicit security requirements*, what does it get wrong by default?

Each AI is given the same neutral prompt (no vulnerabilities requested, no security requirements specified) and the resulting app is reviewed for common weaknesses — SQL/NoSQL injection, XSS, broken authentication, insecure direct object references (IDOR), unsafe file uploads, and missing authorization checks.

## Status

Work in progress — all apps are currently being built in [Cursor](https://cursor.com) (I'm a student therefore broke af).

Last updated: 9/11/2026

## Structure

The project is organized into four sections, one per model:

- `/gpt` — app generated using GPT
- `/claude` — app generated using Claude
- `/gemini` — app generated using Gemini
- `/deepseek` — app generated using DeepSeek

Each folder will contain:
- The generated source code
- The exact prompt used
- A write-up of vulnerabilities found, with severity and how they were identified

## Methodology

1. **Same prompt, every model.** No security-specific instructions are included — just normal feature requirements (user registration/login, posts, comments, search, file upload, admin panel).
2. **Manual + automated review.** Code is reviewed by hand and tested with tools such as `sqlmap`, Burp Suite, and manual exploit attempts.
3. **Findings are logged per model** using a consistent checklist (loosely based on the OWASP Top 10) so results are comparable across models.

## Disclaimer

This project is for educational and research purposes only. All testing is performed against locally-hosted, self-generated applications — not live or third-party sites. None of the code here is intended for production use.
