# HatQ Inc. - HQ Leave Management System

A professional, enterprise-grade human capital management application built with **Laravel 12**, **PostgreSQL**, and **Vue.js 3**. Designed for high-performance employee leave tracking, attendance analytics, and organizational transparency.

## 🌟 Key Features

### 🏢 Corporate Identity & Premium UX
- **HatQ Branding**: Fully custom-branded interface with a focus on modern corporate aesthetics.
- **Glassmorphic Navigation**: A sleek, dark-themed sidebar with interactive states and smooth transitions.
- **Global Smart Scroll**: An intelligent, pulsing "Scroll Down" indicator that ensures users never miss deep content on dashboards or schedules.
- **Universal Topbar**: Features a live 12-hour synchronized clock and dynamic breadcrumb navigation.
- **Production-Ready Stability**: All previously "experimental" features (Attendance, Reports) have been promoted to core modules, and the temporary Settings panel has been deprecated for a cleaner, focused administrative experience.
- **Mistake-Proof Employee Records**: Clicking an employee opens a beautiful, read-only "Details Modal" to prevent accidental data overwrites. Explicit "Edit" modes ensure purposeful updates.

### 📊 Enterprise Analytics & Reporting
- **Executive Stats Grid**: Real-time monitoring of **Total Employees**, **Daily Presence**, **Absences**, **Lateness**, and **On-Leave** staff.
- **Robust Attendance Rates**: Realistic absence and tardiness calculation factoring in explicitly logged records and individualized dynamic working shifts, eliminating inflated figures.
- **Attendance Trend Graph**: A high-fidelity, interactive **ApexCharts** visualization with toggleable **Daily**, **Weekly**, and **Monthly** perspectives for long-term presence tracking.
- **Corporate Performance Ranking**: A data-driven leaderboard ranking departments by attendance excellence, fostering healthy motivation.
- **Live Activity Feeds**: Paginated "Recent Attendance" and "Recent Leaves" streams with real-time status notifications and high-density employee metadata.
- **Unified HR Sync**: Attendance records are automatically cross-referenced with the centralized **Employee Masterlist**, resolving Department, Full Name, and Avatar data even for staff without digital accounts.

### 🩸 Comprehensive Leave Workflow
- **Multi-Leave Credits System**: Dedicated tracking for Vacation (VL), Paternity (PL), Solo Parent (SP), Bereavement, and VAWC leaves with dynamic balance checking during submission.
- **Multi-Stage Approval Trail**: High-fidelity chronological timeline tracking every step of a request: **Employee Filing** → **Dept Head Review** → **HR/Admin Final Decision**.
- **Role-Specific Queues**: HR/Admin view now defaults to **"Forwarded to HR"** (Dept Approved), ensuring a streamlined workflow focused on final-stage actions.
- **Dept Head Empowerment**: Department heads have a specialized dashboard to view, review, and add remarks to staff requests within their own department scope.
- **Frictionless Employee Portal**: A standalone, high-security portal for field staff to file leaves and track their detailed multi-leave balances without requiring a full system account.
- **Hybrid Data Fetching**: User dashboards dynamically fetch both web-submitted and portal-submitted records by cross-referencing **ID Number** and **Employee ID**.

### 🧪 Biometric Attendance Engine & Masterlist Import
- **Multi-Sheet Universal Excel Parser**: High-performance client-side engine capable of reading 3-sheet formats to import Masterlists, schedule working hours, and initial leave balances.
- **Dual-Log Reconciliation**: Advanced logic that automatically groups separate **IN** and **OUT** rows, identifies the earliest/latest timestamps, and calculates exact work hours.
- **Live Progress UI**: Real-time visual feedback during mass imports, showing parsing stages and calculation status with a synchronized progress bar.
- **Dynamic Lateness Classification**: Automatically determines **Present**, **Late**, **Half-Day**, or **Absent** statuses based on the individualized working hours assigned to the employee rather than a single hardcoded cutoff.

### 📅 Advanced Scheduling & Calendar
- **Continuous Multi-Day Visualization**: Sophisticated logic that renders multi-day events as single, unbroken visual bars for clarity.
- **Philippine Compliance**: Pre-loaded with Philippine Public Holidays integrated into the dynamic organizational calendar.
- **One-Click Planning**: Full-screen schedule management module for organizational planning.

### 📦 Advanced Resource Auditing (Inventory)
- **High-Density Data Grid**: Resources are presented in a clean, professional spreadsheet-like data table structure to maximize visibility of critical organization assets.
- **Bulk Excel Importer**: Seamlessly migrate hundreds of historical assets from legacy `<inventory_details.xlsx>` files directly into the modernized HatQ SQL registry.
- **Integrated Audit Trail**: Every asset modification (Create, Update, Delete, Import) is automatically captured in the global security journal.

### 📁 Archive & Data Maintenance
- **Advanced Archive Registry**: Featuring a Windows 11-inspired Explorer navigation system. Records are categorized into secure "Folders" for Leaves and Personnel.
- **Soft-Delete Maintenance**: High-performance administrative tool to mass-archive legacy records using smart date thresholds (30, 60, or 90 days ago).
- **Precision SIL Tracking**: Automated Service Incentive Leave (SIL) system with 100% accurate numeric deductions managed through dedicated leave modules.

---

## 🏎️ Performance & Development Speed

The development environment is optimized for maximum "snappiness" by leveraging a hybrid server architecture:

### **Why is `localhost:5173` faster?**
- **Vite Engine**: Port 5173 is powered by **Vite**, providing near-instant asset delivery and **Hot Module Replacement (HMR)**.
- **The Hybrid Workflow**: UI & Assets are served by Vite (5173), while API & Data are automatically proxied to Laravel (8000) in the background.

---

## 🛡️ Security & Action Audit Trail
- **Comprehensive Action Logging**: Every administrative move—including **Excel Exports**, **Bulk Imports**, and **Setting Changes**—is recorded in the Audit Trail.
- **Native Automated Backups**: Integrated `php artisan backup:daily` system with 7-day automated rotation.
- **Global API Protection**: Built-in **Rate Limiting** (`throttle:120,1`) to prevent abuse.
- **Safe Transactions**: Multi-table updates are wrapped in atomic database transactions.

---

## 🛠️ Tech Stack
- **Languages**: PHP 8.2 (Laravel 12), JavaScript (Vue.js 3)
- **Styling**: TailwindCSS v4 + Vanilla CSS
- **Database**: PostgreSQL 15+
- **Reporting**: Chart.js, ApexCharts, CSV Export Engine
- **Tooling**: Vite, Composer, NPM

## 🚀 Getting Started

### 1. Prerequisites
- PHP 8.2+ | Node.js & NPM | PostgreSQL 15+

### 2. Installation
```bash
git clone https://github.com/Cris-John-AFK/hq_application.git
cd hq_application
composer install && npm install
cp .env.example .env && php artisan key:generate
npm run build
```

### 3. Database & Deployment
```bash
php artisan migrate --seed
php artisan serve
```

---
*Created by Cris John M. Cañales & Jessica Roque*
*Support: crisjohn.canales@gmail.com | jessicaroque12@gmail.com*
