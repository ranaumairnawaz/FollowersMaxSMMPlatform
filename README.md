# 🚀 FollowersMax — Social Media Marketing (SMM) Platform

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php" alt="PHP 8">
  <img src="https://img.shields.io/badge/TailwindCSS-Modern%20UI-06B6D4?style=for-the-badge&logo=tailwindcss" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/MySQL-PDO-4479A1?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/SMM%20Reseller-API%20Integrated-FF6C37?style=for-the-badge" alt="SMM Reseller API">
</p>

---

## 📖 What is FollowersMax?

**FollowersMax** is a high-performance **Social Media Marketing (SMM) reseller portal and service dispatch engine** built with **PHP, MySQL, and Tailwind CSS**. 

It serves as an automated bridge between end-users wishing to purchase social media marketing growth services and upstream wholesale provider APIs. It automates order routing, currency balance deduction, custom profit margin calculations, and real-time order status tracking.

---

## 🎯 Why FollowersMax? Problem & Solution

| The Challenge | How FollowersMax Solves It |
| :--- | :--- |
| **Manual Order Forwarding:** Reselling SMM services manually requires copy-pasting customer links and quantities to upstream providers 24/7. | Implements **Direct Upstream API Dispatching** that submits validated orders to provider APIs automatically in real-time. |
| **Price & Profit Volatility:** Wholesale providers constantly adjust prices; updating hundreds of services manually leads to negative margins. | Features an **Automated Margin Engine** that automatically applies global or category percentage profit margins to all synced services. |
| **Manual Payment Reconciliation:** Verifying bank transfers and receipts manually often causes delayed balance top-ups. | Provides a **Dedicated Deposit Verification Workflow** where users upload payment proof receipts, and admins approve funds with automatic balance updates. |

---

## 🌟 Key Features

### 👤 Customer Experience
- **Interactive Dashboard:** Live visibility over available account balance, active orders, and total spent funds.
- **Categorized Service Catalog:** Filterable list of services with dynamic pricing, minimum/maximum order limits, and description notes.
- **Instant Order Submission:** Client-side validation ensuring links, quantities, and balances meet criteria before order placement.
- **Order Lifecycle Tracking:** Real-time status indicators (Pending, Processing, In Progress, Completed, Cancelled).
- **Payment & Funds Management:** Upload deposit receipts with automated status updates.
- **Profile & Notification Suite:** Profile customizer and secure password reset workflows via SMTP.

### 🛡️ Administrative Command Suite
- **Executive Analytics:** Monitor total revenue, pending orders, user balance liabilities, and active API provider balances.
- **Service & Margin Engine:** Sync services from wholesale APIs and configure markup percentages.
- **Deposit Review Pipeline:** Review uploaded transaction proofs, approve deposits with automatic ledger crediting, or reject invalid attempts.
- **User Management & Impersonation:** Search users, adjust balances, or securely impersonate accounts to resolve customer inquiries.
- **Theme & Appearance Customizer:** Configure brand font, accent colors, and dark mode presets directly from the admin panel.
- **SMTP Notification Hub:** Configure mail server credentials and dispatch responsive HTML emails for orders and resets.

---

## 🛠️ Technical Stack

- **Backend:** PHP 8.x (Clean MVC-style architecture with PDO prepared statements)
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Frontend:** Tailwind CSS, FontAwesome 6, Modern Glassmorphism Styling
- **Integrations:** RESTful Provider API (cURL), SMTP Mailer

---

## 📦 Local Installation & Setup

### 1. Prerequisites
- PHP 8.0 or higher with `curl` and `pdo_mysql` extensions enabled
- MySQL / MariaDB
- Web server (Apache via XAMPP, Laragon, or Nginx)

### 2. Clone & Configure
```bash
git clone https://github.com/ranaumairnawaz/FollowersMaxSMMPlatform.git
cd FollowersMaxSMMPlatform
```

Configure database connection in `config.php`:
```php
$db_host = 'localhost';
$db_name = 'followersmax_db';
$db_user = 'root';
$db_pass = '';
```

### 3. Initialize Database
Visit `http://localhost/followersmax/init_db.php` in your browser to automatically create all required tables and provision the default administrator:
- **Default Username:** `admin`
- **Default Password:** `admin123`

---

## 👨‍💻 Author
**Rana Umair Nawaz**  
- [GitHub Profile](https://github.com/ranaumairnawaz)  
- [LinkedIn Profile](https://linkedin.com/in/ranaumairnawaz)
