# Apileon — Release v0.1.0
**Release Date**: 2025-08-19

---

## 🚀 Highlights
- **Initial public release** of Apileon — a lightweight, enterprise-grade PHP framework built exclusively for **RESTful API development**.
- Introduces a **REST-first architecture**, aiming for clean, performant, and scalable backends.

---

## 🆕 New Features
- **Routing System**: Define routes with ease using simple syntax (e.g., `Route::get(...)`, `Route::post(...)`).
- **Middleware Support**: Includes a middleware pipeline for authentication, logging, and rate limiting.
- **JSON-First Responses**: Designed from the ground up for JSON—no HTML bloat.
- **Project Scaffolding via Composer**: Quick-start your API project with `composer create-project apileon/framework my-api`.

---

## ✨ Enhancements
- **Enterprise-Ready Structure**: Organized project layout with dedicated folders (`app/`, `config/`, `routes/`, `tests/`).
- **Ready for CI/CD**: PHPUnit configured, facilitating early testing and continuous integration.

---

## 🐞 Fixes & Improvements
- Initial test suite added for core routing functionality.
- Default error handling and HTTP response formatting refined for clean JSON output.

---

## ⚠️ Breaking Changes
- This is the first public release — no breaking changes at present.

---

## 🔄 Upgrade Notes (v0.1.0 → Future Versions)
- Expect improvements to configurability (e.g., database, caching) in upcoming v0.2.0.
- Future enhancements to include request validation, auto-generated API docs, and expanded middleware.

---

## 📦 How to Get Started
```bash
composer create-project apileon/framework my-api
cd my-api
# Define routes in routes/api.php
php -S localhost:8000 -t public
curl http://localhost:8000/hello
```

---

Thank you for trying out **Apileon**! Please report bugs or suggest improvements via Issues or Pull Requests. 🚀
