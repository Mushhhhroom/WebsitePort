# 🚀 Personal Portfolio & Content Management System
**Developer:** Jairus John Valdez  
**Major:** Computer Science  
**Technology Stack:** PHP 8, MySQL (PDO), HTML5, Modern CSS3, JavaScript (ES6+)

---

## 🌟 Overview
A production-grade, secure, dynamic personal portfolio website engineered specifically for a Computer Science student. It showcases your resume, academic background, projects, and contact inquiries with full administrative control.

### Key Highlights
- **Dynamic Resume System:** Education, leadership, technical skills, and certifications pulled straight from MySQL and editable via the dashboard.
- **Project Showcase:** Live filterable project catalog with category buttons, real-time search, tech stack badges, and GitHub/Demo links.
- **Defensive Security Architecture:**
  - **CSRF Protection:** Cryptographic tokens verified for every form submission using `hash_equals()`.
  - **SQL Injection Prevention:** 100% prepared statements with PDO and disabled emulation (`PDO::ATTR_EMULATE_PREPARES => false`).
  - **Cross-Site Scripting (XSS) Defense:** Automated context-aware escaping with `e()` (`htmlspecialchars`).
  - **Authentication & Password Security:** Modern `password_hash()` with bcrypt (`PASSWORD_DEFAULT`), `password_verify()`, session regeneration upon login, and session cookie protections (`HttpOnly`, `SameSite=Lax`).
  - **Anti-Spam Honeypot:** Invisible trap field on the contact form to catch automated bots without annoying CAPTCHAs.
  - **Brute-Force Throttle:** Temporary rate-limiting on consecutive failed admin login attempts.
- **Full Admin Control Panel (`dashboard.php`):**
  - Project management (Add, Edit, Delete, Toggle Featured).
  - Resume management (Add/Delete entries across all 4 resume sections).
  - Contact inbox (Read inquiries, mark read/unread, delete, or direct email reply).
  - Profile customization & secure password change.

---

## 📂 File Architecture
```
portfolio/
├── config/
│   └── database.php         # Database configuration constants & site metadata
├── includes/
│   ├── db.php               # PDO database connection instance
│   ├── security.php         # CSRF, XSS escaping, auth guards & session security
│   ├── header.php           # Global navigation and responsive HTML header
│   └── footer.php           # Global footer, links, and script loader
├── assets/
│   ├── css/
│   │   └── style.css        # Responsive cyber/dark developer stylesheet
│   └── js/
│       └── main.js          # Project search/filter, tabs, and mobile menu
├── index.php                # Homepage with hero, featured projects, and skill cards
├── about.php                # Dynamic resume page with print-to-PDF support
├── projects.php             # Full filterable projects showcase
├── contact.php              # Secure contact form with CSRF and anti-bot honeypot
├── login.php                # Admin login with rate limiting & security tokens
├── logout.php               # Secure session destruction and cookie clearance
├── dashboard.php            # Administrative control center (full CRUD operations)
├── setup.php                # Web & CLI automated database installer and seeder
└── database.sql             # Full MySQL schema dump and initial seed data
```

---

## ⚡ Quick Start & Setup

### Method 1: Using the Built-in PHP Server (Recommended for Quick Testing)
Open **PowerShell** or **Command Prompt** inside the `portfolio` directory:
```powershell
# 1. Run the database setup script (creates database and seeds initial records)
C:\xampp\php\php.exe setup.php

# 2. Start the built-in development server
C:\xampp\php\php.exe -S localhost:8000
```
Open your browser and navigate to:
👉 **`http://localhost:8000`**

---

### Method 2: Using XAMPP Apache
1. Copy or link this `portfolio` folder to `C:\xampp\htdocs\portfolio`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open your browser and navigate to:
👉 **`http://localhost/portfolio/setup.php`** (to initialize the database).  
👉 **`http://localhost/portfolio/index.php`** (to view the site).

---

## 🔐 Default Admin Credentials
- **Login URL:** `http://localhost:8000/login.php`
- **Username:** `admin`
- **Password:** `password123`

*(You can update your username, full name, and password anytime under the **Settings** tab in `dashboard.php`)*.
