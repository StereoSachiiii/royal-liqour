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

Although the system can be deployed, there are still some essential refactoring and advanced feature tasks that need to be worked on for the long-term stability, security, and scalability.

1. API & Backend Refactoring

The initiated task will help in making the core PHP logic more robust and easier to maintain:

Validation Refactor: Merge and standardize validation logic across all the Validator files (admin/validators/) to guarantee consistency and eliminate security vulnerabilities.

User API Endpoint Refactor: Break down and clarify user-related API endpoints to observe the single responsibility principle (e.g., dedicated endpoints for Auth and Profile).

Took 41k lines of code. but it served its purpose of learning to implement session handling securely , using middleware for structuring , REST API design. Database denormalization , OOP design for a full system with 18 models, indexing ACID properties, triggers, rate limitng , validation, refractoring, MVC + repository pattern, SSE. this also includes a Single Page Application for the admin page using React Like pattern without state(essentially conditional rendering) debounces queries, searching, sorting. After proposed refractoring is done this system will be good to go and could be implemented to use with upto multiple thousand users. This is still a work in progress. 

