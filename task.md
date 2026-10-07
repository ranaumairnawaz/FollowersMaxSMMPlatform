# SMM Panel Development Task List

## 1. Planning and Setup
- [x] Analyze requirements and API documentation
- [x] Create project structure
- [x] Define database schema (MySQL)
- [x] Create [init_db.php](file:///d:/xampp/htdocs/followersmaxv2/init_db.php) script for database installation

## 2. Backend & Core Utilities
- [x] Create [config.php](file:///d:/xampp/htdocs/followersmaxv2/config.php) for DB connection and settings
- [ ] Implement authentication (Login/Register for users, Admin login)
- [ ] Create helper functions for API requests
- [ ] Create helpers for setting management (Admin settings)

## 3. Admin Panel
- [ ] Implement Admin Dashboard (Stats, API Balance)
- [ ] Implement Settings Page (API URL, API Key, Profit Margin %, Bank Details)
- [ ] Implement Services Management (Fetch from API with Margin applied)
- [ ] Implement Fund Approvals (View user transfer screenshots, approve/reject)
- [ ] Implement Orders Page (View all user orders)
- [ ] Implement Reports Page (Revenue, Orders summary)

## 4. User Panel
- [ ] Implement User Dashboard (New Order form, Stats)
- [ ] Implement Add Funds Page (Show bank details, upload screenshot, enter amount)
- [ ] Implement User Orders Page (View history and status via API)

## 5. API Integration & Cron Jobs
- [ ] Script to submit order to provider when user places order
- [ ] Script (`sync_services.php`) to fetch services and update local pricing based on margin
- [ ] Script (`sync_orders.php`) to update status of pending orders

## 6. Frontend UI/UX
- [x] Design beautiful, premium CSS (glassmorphism, animations, responsive)
- [x] Apply styling to User Dashboard
- [ ] Apply styling to Admin Dashboard
- [x] Ensure mobile responsiveness

## 7. Verification & Testing
- [ ] Test User Registration & Login
- [ ] Test Admin settings update
- [ ] Test fetching services with margin calculation
- [ ] Test placing a sample order
- [ ] Test adding funds flow (User upload -> Admin approve -> Balance update)
