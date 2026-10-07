# 🚀 FollowersMax - Social Media Marketing (SMM) Panel

A modern, full-featured **Social Media Marketing (SMM) Platform** built with **PHP & MySQL**, featuring automated service synchronization, reseller API integrations, balance management, custom profit margins, manual payment gateway verification, and an administrative control panel.

---

## 🌟 Key Features

### 👤 Customer Experience
- **Interactive Dashboard:** Real-time stats on orders, available account balance, and spent funds.
- **Service Catalog:** Filterable list of services categorized with minimum/maximum limits and dynamic pricing.
- **Instant Order Placement:** Direct integration with upstream SMM provider APIs with validation.
- **Funds Management:** Deposit funds with proof of payment upload (receipt/screenshot) and status tracking.
- **Order Tracking:** Real-time order progress monitoring (Pending, Processing, Completed, Cancelled).
- **Profile & Security:** Account management, profile photo customization, and secure password reset via SMTP.

### 🛡️ Administrative Control Panel
- **Executive Analytics:** Overview of system revenue, total users, pending orders, and active services.
- **Service & Margin Engine:** Sync services automatically from SMM provider APIs and apply custom global profit margins.
- **Deposit Approvals:** Review user payment proof uploads, approve/reject transactions with automatic balance credit.
- **User Management & Impersonation:** View, search, update user balances, or impersonate user accounts for support.
- **Appearance Customization:** Theme font, primary accent colors, and dark mode customization.
- **SMTP & Notification Hub:** Configure mail server with HTML email templates for order notifications and password resets.

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.x (Vanilla / MVC Architecture)
- **Database:** MySQL / MariaDB (PDO with Prepared Statements)
- **Frontend:** TailwindCSS, FontAwesome 6, Modern Glassmorphism UI
- **Integrations:** RESTful SMM Provider API (cURL), SMTP Mailer

---

## 📦 Installation & Setup

### 1. Prerequisites
- PHP 8.0 or higher with `curl` and `pdo_mysql` extensions enabled
- MySQL 5.7+ or MariaDB 10.3+
- Apache or Nginx web server (e.g. XAMPP, Laragon, or VPS)

### 2. Clone the Repository
```bash
git clone https://github.com/ranaumairnawaz/FollowersMaxSMMPlatform.git
cd FollowersMaxSMMPlatform
```

### 3. Database Configuration
1. Open `config.php` (or configure via environment variables):
   ```php
   $db_host = 'localhost';
   $db_name = 'followersmax_db';
   $db_user = 'root';
   $db_pass = '';
   ```
2. Run database migration script in your browser or CLI:
   ```bash
   http://localhost/followersmax/init_db.php
   ```
   *This automatically creates tables and provisions the initial administrator account:*
   - **Default Admin Username:** `admin`
   - **Default Admin Password:** `admin123`

---

## 🔒 Security Best Practices
- Change default admin credentials immediately after running `init_db.php`.
- Keep upstream API credentials secure within the database settings table.
- Transaction receipts uploaded by users are isolated from public repository tracking.

---

## 👨‍💻 Author
**Rana Umair Nawaz**  
- Founder, [ZedX Solutions](https://github.com/ranaumairnawaz)  
- [LinkedIn Profile](https://linkedin.com/in/ranaumairnawaz)  
- [Personal Portfolio](https://umair.zedx-solutions.com)
