# Fleet Management System - Project Report

## Overview

The Fleet Management System is a web-based application designed to help organizations manage their vehicles, drivers, maintenance records, and fuel transactions efficiently. The system provides a user-friendly dashboard, secure authentication, and dynamic data management features.

---

## Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- Apache/Nginx web server
- Composer (optional, if you use PHP packages)
- Git

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/CSE311L-fleet-management-system.git
   cd CSE311L-fleet-management-system

2. **Edit the database configuration:**
    Open the `config.php` file and update the following variables with your database credentials:
    ```php
    $host = "localhost"; // Database host
    $username = "root"; // Your MySQL username
    $password = ""; // Your MySQL password (leave empty if no password)
    $db_name = "fleetopz_users"; // Database name
    ```
    Ensure these values match your local or server database setup.

## Features

### 1. User Authentication
- **Description:** Users must register and log in to access the system. Each user has a separate database for their fleet data, ensuring privacy and data isolation.
- **Purpose:** Secure access and personalized data management.

### 2. Dashboard
- **Description:** The dashboard provides an overview of the system, including navigation to different modules (Vehicles, Drivers, Maintenance, Fuel).
- **Purpose:** Centralized access to all fleet management features.

### 3. Vehicles Management
- **Description:** Users can add, edit, delete, and view vehicles. Each vehicle record includes details such as model, year, license plate, status, and assigned driver.
- **Purpose:** Maintain an up-to-date inventory of all vehicles.

### 4. Drivers Management
- **Description:** Users can manage driver profiles, including name, contact, license, status, assigned vehicle, hire date, and salary.
- **Purpose:** Track driver assignments and details.

### 5. Maintenance Records
- **Description:** Users can log and manage maintenance activities for vehicles, including service type, date, status, and cost.
- **Purpose:** Ensure timely maintenance and track service history.

### 6. Fuel Transactions
- **Description:** Users can record fuel purchases and consumption, including date, vehicle, driver, fuel type, amount, and cost.
- **Purpose:** Monitor fuel usage and expenses.

### 7. Dynamic Search
- **Description:** Each module features a dynamic search box. For example, on the Vehicles page, users can search for vehicles by model or license plate, and results update in real-time.
- **Purpose:** Quickly find and filter records without reloading the page.

### 8. Responsive Design
- **Description:** The interface adapts to different screen sizes, providing a seamless experience on both desktop and mobile devices.
- **Purpose:** Accessibility and usability across devices.

### 9. User Profile & Logout
- **Description:** The user’s email is displayed in the navbar. Clicking it reveals a dropdown with a logout button.
- **Purpose:** Easy access to account actions.

---

## Technologies Used

- **Frontend:** HTML, CSS (TailwindCSS), JavaScript
- **Backend:** PHP (Procedural & MySQLi)
- **Database:** MySQL/MariaDB
- **Web Server:** Apache/Nginx

---
