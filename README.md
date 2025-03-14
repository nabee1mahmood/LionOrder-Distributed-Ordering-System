🥗 LionOrder Distributed Ordering System
This project is for building a custom salad ordering system using locally sourced ingredients.

🚀 Overview
This project was created for our Software Engineering course. LionOrder is a web-based system that lets customers build custom salads using available ingredients. The system tracks inventory, manages orders, and allows users to log in to view their order history. If ingredients are out of stock, the system can transfer more from the warehouse in batches to avoid small, costly shipments.

💻 Technologies Used
Front End:

HTML
CSS
Bootstrap
Back End:

PHP (for handling sessions)
FastAPI (for building APIs)
JSON (for data exchange)

Database:

MariaDB (for storing user, order, and inventory data)
Containerization:

Docker (for running microservices)
🔑 Features
✅ User login and account management
✅ Create and customize salad orders
✅ Real-time inventory updates after each order
✅ Batch inventory transfers from the warehouse
✅ Order history for customers

🛠️ System Architecture
The system is built using a microservices setup:

Frontend: Built with HTML, CSS, and Bootstrap for a clean, user-friendly experience.
API: Developed with FastAPI and Docker to handle order processing and inventory updates.
Database: MariaDB is used to store customer info, orders, and inventory data.
Warehouse: Manages batch transfers to keep stock levels updated.

👥 Team Contributions

Nabeel Mahmood:

Built the front end using HTML, CSS, and Bootstrap.
Created the Login, Order, Manage Account, and Create Account pages.
Designed the project logo and pushed updates to GitHub.
Helped with the presentation and project report.

Josh:

Developed the back-end API using FastAPI.
Set up Docker containers and connected them with MariaDB.
Handled customer login, order processing, and inventory updates.

Joe:

Managed inventory and warehouse logic.
Built the system for batch transfers and real-time inventory updates.
Tested and fixed issues with the inventory system.
