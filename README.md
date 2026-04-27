# 🚀 Eventify: Objective Event Management System

Eventify is a premium, Swiss Modern-inspired event management system designed for seamless campus and organizational event tracking, registration, and pass generation.

---

## 🛠️ Installation & Setup

Follow these steps to get the system running on your local machine using XAMPP.

### 1. Place Project in htdocs
Move the entire `Eventify` folder into your XAMPP's `htdocs` directory:
`C:\xampp\htdocs\Eventify`

### 2. Start MySQL & Apache
Open your **XAMPP Control Panel** and ensure both **Apache** and **MySQL** are running.

### 3. One-Hit Automated Setup
We have included an automated setup script that handles folder permissions and database initialization. Open your browser and navigate to:
👉 **[http://localhost/Eventify/setup.php](http://localhost/Eventify/setup.php)**

> [!NOTE]
> The system will automatically create the `eventify` database and all required tables if they don't exist. It also sets up the `uploads/` folder for event banners and gallery photos.

---

## 🖥️ System Flow & Endpoints

The system is divided into two main parts: the **Public Portal** for attendees and the **Admin Portal** for management.

### 🌐 Public Portal (Attendees)

1.  **Home Page**: Browse all upcoming events.
    *   **URL**: [http://localhost/Eventify/frontend/index.html](http://localhost/Eventify/frontend/index.html)
    *   *Features*: Modern Swiss-grid layout, Event Gallery viewer.

2.  **Event Registration**: Register for a specific event.
    *   **URL**: `http://localhost/Eventify/frontend/register.html?id=[EVENT_ID]`
    *   *Note*: Access this by clicking "Register" on any event card from the Home page.

3.  **Check Status**: Check if your registration has been approved.
    *   **URL**: [http://localhost/Eventify/frontend/check-registration.html](http://localhost/Eventify/frontend/check-registration.html)
    *   *Flow*: Enter your email to see a list of your registrations.

4.  **Download Pass**: Approved participants can download their digital pass.
    *   **URL**: `http://localhost/Eventify/frontend/pass.html?id=[PARTICIPANT_ID]`
    *   *Features*: Printable Swiss Modern design with a unique identifier.

---

### 🔐 Admin Portal (Management)

1.  **Admin Login**: Secure access to management tools.
    *   **URL**: [http://localhost/Eventify/frontend/admin/index.html](http://localhost/Eventify/frontend/admin/index.html)
    *   **Credentials**:
        *   **Username**: `admin`
        *   **Password**: `password` (Note: Default hashed password is `admin123` in SQL, but `password` is common for test suites. Use `admin` / `password` for initial access).

2.  **Dashboard**: Overview of system statistics.
    *   **URL**: [http://localhost/Eventify/frontend/admin/dashboard.html](http://localhost/Eventify/frontend/admin/dashboard.html)

3.  **Event Management**: Create events, upload banners, and manage galleries.
    *   **URL**: [http://localhost/Eventify/frontend/admin/events.html](http://localhost/Eventify/frontend/admin/events.html)

4.  **Participant Approval**: Approve or Reject registrations.
    *   **URL**: [http://localhost/Eventify/frontend/admin/participants.html](http://localhost/Eventify/frontend/admin/participants.html)

---

## 🏗️ Technical Architecture

*   **Backend**: PHP 8.x (RESTful API Design)
*   **Database**: MySQL (PDO for security)
*   **Frontend**: Vanilla JS (ES Modules) & Modern CSS3
*   **Design Language**: Swiss Modern (High contrast, grid-focused, bold typography)

---

## 📁 Directory Structure

```text
Eventify/
├── backend/            # PHP API Logic
│   ├── api/            # API Entry points
│   ├── controllers/    # Request handling
│   ├── models/         # Database models
│   └── config/         # Database configuration
├── frontend/           # HTML/CSS/JS Assets
│   ├── admin/          # Protected admin pages
│   └── assets/         # CSS & Shared JS
├── uploads/            # Banner & Gallery images (Auto-created)
├── database.sql        # Raw SQL Schema
├── setup.php           # Automated setup utility
└── README.md           # You are here!
```

---
*Created for Eventify - Objective Event Management System*
