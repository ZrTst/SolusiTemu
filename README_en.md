# 🛠️ SolusiTemu Company - Appointment System
[English] | [Bahasa Melayu](README.md)  
This is a Form 4 - Form 5 Computer Science Coursework Project.  
The project is developed using PHP, HTML, CSS, and JS.  

---

## 📌 1. Roles 
The system categorizes users into 3 main roles based on the prefix of their User ID:

*   **A (Admin)** -> `role: admin` (Manages the entire system: Users, Services, Data Import/Export, and Reports).
*   **J (Technician)** -> `role: technician` (Manages work tasks and updates operational statuses).
*   **P (Customer)** -> `role: customer` (Searches for services/technicians, books appointments, and checks their own appointments).

---

## 🗺️ 2. Table of Contents
All business pages are called dynamically using `include` inside `dashboard.php` via the `?page=xxx` parameter without changing the base URL.

```text
.
├── README.md
├── README_en.md
├── .htaccess          
└── pages/   
    ├── errors/         # Error response pages (400, 401, 403, 404)
    ├── sub_pages/      # Business UI fragment files
    └── api/            # Backend data processing scripts
├── private_core/       # Database configuration & global security functions
├── public/             # Static asset files
├── images/             # System image files
```

---

## 🔄 3. Operational Data Flow 

Every time a user submits form data, the data flow is processed through 3 phases:

1.  **UI (View)**: The user fills out the form in `pages/sub_pages/janjitemu.php`.
2.  **API (Process)**: Data is sent to `pages/api/add_janjitemu.php` via the `POST` method. The backend performs Session security checks, validates user roles, and inserts data using PDO Prepared Statements.
3.  **Redirection (Redirect)**: After successful insertion into the database, the backend redirects the user back to `dashboard.php?page=user_janjitemu&msg=success`.

---

## 🗃️ 4. Development Checklist

### 🔓 Phase 1: Login & Session 
- [x] Interface design for `login.php` (Using ID / Using Name).
- [x] Secure password verification (`db_verifyPassword` inside the `/private_core/functions.php` file).
- [x] Automatic Session management based on ID prefixes (P/A/J).

### 📐 Phase 2: Dashboard Layout & Router 
- [ ] Implementation of Flexbox layout (Fixed Left Sidebar, Flexible Right Main Content).
- [ ] Nested `if-else` + `switch-case` structures inside `dashboard.php`.
- [ ] Setting up `pages/sub_pages/` and `pages/api/` folders.

### 💾 Phase 3: Core Business Logic (CRUD Operations)
- [ ] Add New Appointment Form (Customer).
- [ ] Search & Display active Services and Technicians (Customer).
- [ ] Appointment History Table display with isolated data access rights control (Customer, Technician, Admin).
- [ ] Appointment Approval & Allocation features (Technician, Admin).
- [ ] Global administrative operations: `delete`, `update`, `add` for Appointment/User/Service entities (Admin only).
- [ ] Bulk Data Import feature from external files (Admin).
- [ ] Formatted Report Generation Output feature (Admin).

### ❗ Phase 4: Vulnerability Testing and Error Recovery
- [ ] Cyber error testing and code vulnerability analysis (Vulnerability testing: SQL Injection, XSS).
- [ ] Runtime error handling and database logic recovery to ensure a bug-free system.

---

© 2026-2027 ZrTst. Built with Vanilla Web Technologies & Pure Logic.
