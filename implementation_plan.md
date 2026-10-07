# SMM Panel Implementation Plan

This plan outlines the creation of a simple yet powerful SMM (Social Media Marketing) panel utilizing a PHP and MySQL stack.

## User Review Required
> [!IMPORTANT]
> The application will run on **Vanilla PHP with MySQL**, utilizing PDO for database access, as it smoothly runs on your XAMPP environment without complex setup. Modern CSS (Glassmorphism, animations) will be written from scratch without heavy UI frameworks to ensure a "WOW" premium design without React.
> **Database Authentication:** Do you already have a MySQL database named `followersmaxv2` with a specific password, or should I create a setup script that creates the schema under the default XAMPP `root` user (no password)?

## Proposed Changes

We will build the system in a structured format:

### Core Files
#### [NEW] config.php
Database connection (PDO) and global constants.
#### [NEW] setup.sql
Database schema for `users`, `orders`, `services`, `transactions` (fund additions), and `settings`.

### Admin Panel (Backend Control)
#### [NEW] admin/index.php
Admin dashboard showing total statistics and the current balance of the API provider.
#### [NEW] admin/settings.php
Form to configure the API credentials (URL & Key), the unified **Profit Margin %**, and the **Manual Bank Transfer Details** (Account Name, Number, Bank).
#### [NEW] admin/services.php
UI to fetch/sync services from the SMM API Provider. This applies the profit margin % configured in settings to the incoming API base prices.
#### [NEW] admin/funds.php
Page to review (Approve/Cancel) user submissions for Bank Transfers. Allows admins to view uploaded payment screenshots.
#### [NEW] admin/reports.php
Reports page showing general revenues and analytics.

### User Panel
#### [NEW] index.php
Sign in / Registration page.
#### [NEW] dashboard.php
User's main interface featuring a minimal "New Order" form and summary stats of their account.
#### [NEW] add_funds.php
A page where users can view your Bank Details, upload a screenshot of their transaction, and log the amount they sent as a pending support ticket.
#### [NEW] orders.php
History of placed orders, showing the live status updated from the API.

### API Integrations
#### [NEW] api_sync.php
A secure endpoint for the admin to trigger fetching services from the remote API and syncing them to the local `services` table.
#### [NEW] cron_status.php
A script (can be hit manually or by a cronjob) to update the status of existing local orders by checking the API provider `action=status`.

## Styling Strategy
#### [NEW] assets/css/style.css
A premium, dark-mode focused, glassmorphic UI aesthetic inspired by modern web design. Color scheme will be vibrant but professional to ensure it looks "high-end" out of the box.

## Verification Plan

### Automated Tests
- There are no automated tests proposed for this simple PHP structure. Verification will be manual.

### Manual Verification
1. Open the project in the browser via XAMPP (`localhost/followersmaxv2`).
2. Run database setup setup script `localhost/followersmaxv2/init_db.php`.
3. Login as the predefined admin account.
4. Go to **Admin -> Settings**, add API dummy details and set a 150% Profit Margin, plus test Bank Details.
5. Go to **Admin -> Services** and hit "Sync Services". Verify in the DB that prices are 1.5x the origin.
6. Login as a User, navigate to **Add Funds**, upload a mock image, submit.
7. Login as Admin, approve the funds, and verify User balance goes up.
8. As a User, place a "New Order" and see it deduct balance and appear in the "Orders" page.
