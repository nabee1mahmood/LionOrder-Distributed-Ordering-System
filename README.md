# LionOrder Distributed Ordering System

## Overview

LionOrder is a distributed web-based system designed to let customers build and place custom salad orders using fresh ingredients from a local farm. The system handles customer authentication, tracks inventory across store and warehouse locations, and manages the flow of ingredients from farm to table.

This is the first stage of development, focusing on system design, API structure, and containerized services using Docker.

---

## Project Requirements

### Key Features

- **Customer Login & Order History**  
  Only registered users can log in and place orders. Each customer can view their current and past orders.

- **Custom Salad Builder**  
  Customers select ingredients and quantities, and the system calculates the total cost based on unit pricing.

- **Inventory Tracking**  
  Inventory is checked before any order is confirmed. If the store runs low, items are restocked in bulk from the warehouse (not per order).

- **Warehouse Integration**  
  Each store pulls ingredients in batches from the warehouse. The warehouse itself is restocked when crops are harvested.

- **Order Validation**  
  Orders using out-of-stock items are rejected. The system never allows an order that can’t be fulfilled.

---

## Tech Stack

- **Frontend:** HTML/CSS with Bootstrap
- **Backend Services:** Python (FastAPI), PHP
- **Containerization:** Docker
- **Database:** MariaDB 


---

## Report 




