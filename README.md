Intermediate E-Commerce & Inventory Management System

🚧 Project Status: WORK IN PROGRESS (WIP)

The project is an extensive e-commerce and inventory management system that is mainly built in PHP and employs a strong Model-View-Controller (MVC) and Repository/Service pattern.

Current Milestone Achieved: MVP Deployable

The main system is now very stable and regarded as the Minimum Viable Product (MVP). It is set for public launch and processes the following basic workflows:

User Management: Complete user authentication (login/registration) and profile administration.

Catalog Management: Full CRUD (Create, Read, Update, Delete) operations for Products, Categories, Suppliers, and Warehouses.

Core E-Commerce Flow: Supports the entire process from Product Browsing, Shopping Cart Management, to Full Order Creation and Payment Processing.

Inventory & Stock: Mechanism for monitoring and controlling stock levels (StockController, StockService).

Admin Panel: A complete and fully operational Admin Panel for controlling all entities and examining reports.

📝 Critical Next Steps & Technical Debt (Post-Exams Focus)

Although the system can be deployed, there are still some essential refactoring and advanced feature tasks that need to be worked on for the long-term stability, security, and scalability.

1. API & Backend Refactoring

The initiated task will help in making the core PHP logic more robust and easier to maintain:

Validation Refactor: Merge and standardize validation logic across all the Validator files (admin/validators/) to guarantee consistency and eliminate security vulnerabilities.

User API Endpoint Refactor: Break down and clarify user-related API endpoints to observe the single responsibility principle (e.g., dedicated endpoints for Auth and Profile).
