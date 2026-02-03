# HatQ Inc. - HQ Management System

A professional, enterprise-grade human capital management application built with **Laravel 12**, **PostgreSQL**, and **Vue.js 3**. Designed for high-performance employee leave tracking, attendance analytics, and organizational transparency.

## 🌟 Key Features

### 🏢 Corporate Identity & Premium UX
- **HatQ Branding**: Fully custom-branded interface with a focus on modern corporate aesthetics.
- **Glassmorphic Navigation**: A sleek, dark-themed sidebar with interactive states and smooth transitions.
- **Global Smart Scroll**: An intelligent, pulsing "Scroll Down" indicator that ensures users never miss deep content on dashboards or schedules.
- **Universal Topbar**: Features a live 12-hour synchronized clock and dynamic breadcrumb navigation.

### 📊 Enterprise Analytics Dashboard
- **Executive Stats Grid**: Real-time monitoring of **Total Employees**, **Daily Presence**, **Absences**, **Lateness**, and **On-Leave** staff.
- **Corporate Performance Ranking**: A data-driven leaderboard ranking departments by attendance excellence, fostering healthy motivation.
- **Live Activity Feeds**: Paginated "Recent Attendance" and "Recent Leaves" streams with real-time status notifications.
- **Rich Data Vis**: Interactive grouped bar charts for multi-dimensional attendance analysis.

### 📅 Advanced Scheduling & Calendar
- **Continuous Multi-Day Visualization**: Sophisticated logic that renders multi-day events as single, unbroken visual bars for clarity.
- **Intelligent Decluttering**: Dynamic labeling ensures event titles only appear on the first day or weekly boundaries.
- **Philippine Compliance**: Pre-loaded with Philippine Public Holidays integrated into the dynamic organizational calendar.
- **One-Click Planning**: Full-screen schedule management module for organizational planning.

### � Advanced Resource Auditing (Inventory)
- **High-Fidelity Product Showcase**: Assets are presented in a premium, 3D interactive gallery featuring curated studio-quality imagery and "Apple-style" product presentation.
- **Dynamic Resource Registry**: Comprehensive lifecycle management for IT hardware (Laptops, Desktops, Network), Furniture, and specialized equipment.
- **Integrated Audit Trail**: Every asset modification (Create, Update, Delete) is automatically captured in the global security journal for financial transparency.
- **Advanced Audit Toolbar**: Instant real-time searching by **Serial Number**, **Asset Nomenclature**, or **Technical Description** with multi-criteria category filters.
- **Robust Fail-Safe Architecture**: Integrated image loading system with premium fallback icons ensures a consistently polished UI during external network latency.

### �📁 Advanced Leave & Masterlist Management
- **Unified Masterlist Support**: Seamlessly manage leave for both system users and "Masterlist Employees" (staff without digital accounts) with 100% data parity.
- **Admin-Filing with Auto-Approval**: Administrators can bypass approval workflows by filing leaves directly on behalf of employees, triggering instant credit deductions.
- **Disciplinary Metadata Tracking**: New **Attendance Category** system allows administrators to classify leaves (UA, WMC, WD, UH) for advanced absenteeism reporting.
- **Digital Form Fidelity**: Pixel-perfect digital authorization forms that match physical compliance standards.
- **Conflict Prevention**: Intelligent overlap validation prevents filing duplicate requests for the same date.
- **Precision SIL Tracking**: Consolidated Service Incentive Leave (SIL) system with 100% accurate numeric deductions and restorations.

### 🛡️ Security & Action Audit Trail
- **Comprehensive Action Logging**: Every administrative move—including **Excel Exports**, **Bulk Imports**, **Setting Changes**, and **Bulletin Updates**—is recorded in the Audit Trail.
- **Granular Change Tracking**: Profile updates log "Old vs New" data snapshots for technical review and security monitoring.
- **Native Automated Backups**: Integrated `php artisan backup:daily` system with 7-day automated rotation.
- **Granular Authorization**: Strict Laravel **Policies** control every action, ensuring users only see what they own.
- **Global API Protection**: Built-in **Rate Limiting** (`throttle:120,1`) to prevent brute-force attacks and abuse.
- **Safe Transactions**: Multi-table updates (Credits + Status) are wrapped in atomic database transactions.

### 🧠 Enterprise Decision Support
- **AI-Driven Pattern Detection**: Automatically flags "Frequent Friday Leavers" or "Long Weekend Seekers" to alert HR of potential leave abuse.
- **Compliance Rules Engine**: Validates every request against **Solo Parent Act**, **VAWS**, and **Maternity/Paternity** laws.
- **Department Impact Analysis**: Visualizes absenteeism impact (Critical/High/Low) before approval, showing who else is away.
- **Credit Forecasting**: Projects year-end balances based on current burn rates and scheduled future leaves.
- **Attachment Support**: Seamlessly attach Medical Certificates or justification documents to any request.

## ⚡ Performance Architecture
- **Optimized Data Layer**: Refactored frontend using centralized **Pinia Stores** with **Request Deduplication**, reducing server load by ~60%.
- **Request Merging**: Intelligent `fetchingPromise` logic prevents parallel redundant API calls.
- **Enterprise Indexing**: Advanced **Compound Database Indexes** on `[status, from_date]` and `[user_id, status]` for instant reports.
- **Production Caching**: Pre-configured for **Redis** and **Laravel Config Caching**.

## 🛠️ Tech Stack
- **Languages**: PHP 8.2 (Laravel 12), JavaScript (Vue.js 3)
- **Styling**: TailwindCSS v4 + Vanilla CSS
- **Database**: PostgreSQL 15+
- **Reporting**: Chart.js, ApexCharts, CSV Export Engine
- **Tooling**: Vite, Composer, NPM

## 🚀 Getting Started

### 1. Prerequisites
- PHP 8.2+
- Node.js & NPM
- PostgreSQL 15+

### 2. Installation
```bash
# Clone the repository
git clone https://github.com/Cris-John-AFK/hq_application.git
cd hq_application

# Install dependencies
composer install
npm install

# Setup Environment
cp .env.example .env
php artisan key:generate

# Build Assets
npm run build
```

### 3. Database & Deployment
```bash
# Run Migrations & Seeds
php artisan migrate --seed

# Start the Application
php artisan serve
```

### 4. Production (NEED TO DO DAILY / WEEKLY FOR BACKUP!)
```bash
# Build Optimized Assets
npm run build

# Clear & Cache Configurations
php artisan config:cache
php artisan route:cache

# Run Daily Backup (Critical)
php artisan backup:daily
```

## 🔐 Default Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Administrator** | `admin@hq.app` | `password` |
| **Employee** | `user@hq.app` | `password` |

---
*Created by Cris John M. Cañales & Jessica Roque*
*Support: crisjohn.canales@gmail.com | jessicaroque12@gmail.com*
