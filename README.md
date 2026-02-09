# Royal Liquor - Premium E-Commerce Platform

A production-grade e-commerce application built strictly with **Vanilla PHP** and **Pure JavaScript**.

> 🚫 **No Frameworks**.

## 🌟 Key Highlights

### ⚡ Pure "Vanilla" Stack
Built from scratch to demonstrate understanding of web fundamentals without relying on abstraction layers.
*   **Zero Dependencies**: No composer packages or npm libraries for core functionality.
*   **Custom Router**: Built a Regex-based routing engine from the ground up.
*   **Custom Architecture**: Implemented MVC & Dependency Injection manually.

### 🤖 Advanced Features
*   **AI Product Recognition**: Upload an image to find matching liquor bottles (WIP).
*   **Smart Recipe Engine**: "I Can Make This" filtering based on user's purchase history.
*   **Dynamic Cart**: Full AJAX cart system with real-time stock validation.

## 🚀 Store Features
*   **Product Catalog**: Advanced search, filtering, and categorization.
*   **Cocktail Recipes**: Interactive recipe guide with ingredient mapping.
*   **Shopping Cart**: Persistent cart with session synchronization.
*   **Checkout**: Multi-step flow with address book and payment method selection.
*   **User Dashboard**: Order history, wishlist, and profile management.
*   **Security**: CSRF protection, BCrypt hashing, and session hardening.

## 🛠️ Technical Capabilities

*   **Backend**: PHP 8.2+ (Strict Types)
*   **Database**: PostgreSQL (Complex schemas, JSONB, Foreign Keys)
*   **Frontend**: ES Modules (ES6+), CSS Variables, Grid/Flexbox
*   **Validation**: Custom server-side validation engine (WIP).

## 📦 Project Structure

*   `public/` - Public-facing website (Shop, Cart, Recipes)
    *   `utils/api-helper.js` - Centralized API client
*   `admin/` - Internal Management System
    *   `api/` - JSON API Endpoints
    *   `manage/` - Admin UI Screens
    *   `core/` - System Core (Router, DB, Auth)

## 🔧 Setup & Installation

1.  **Database**: Import `schema.sql` into your PostgreSQL database.
2.  **Config**: Copy `.env.example` to `.env` and update credentials.
3.  **Server**: Point Apache/Nginx to the project root.
4.  **Access**:
    *   Store: `http://localhost/royal-liquor/public/`
    *   Admin: `http://localhost/royal-liquor/admin/`

## 🔐 Security
*   **CSRF Protection**: All POST/PUT/DELETE requests require tokens.
*   **Authentication**: Session-based auth with secure password hashing.
*   **Validation**: Server-side validation layers.

---
*Built as a learning project for advanced full-stack patterns.*
