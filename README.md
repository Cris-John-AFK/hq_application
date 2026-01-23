# HatQ Inc. - HQ Management System

A professional, high-performance web application built with **Laravel 12**, **PostgreSQL**, and **Vue.js 3** for streamlined employee leave management and attendance tracking.

## 🌟 Key Features

### 🏢 Corporate Identity & UX
- **Rebranded Interface**: Updated to **HatQ Inc. Management** branding across all layouts.
- **Premium Sidebar**: Neumorphism design with active state indicators, sleek transitions, and profile integration.
- **Universal Layout**: Shared `MainLayout.vue` ensures a consistent, responsive experience across all screen sizes.
- **Real-time Synchronization**: Live 12-hour clock and dynamic breadcrumbs in the topbar.

### 📊 Advanced Admin Dashboard
- **5-Column Stats Grid**: Real-time tracking of **Total Employees**, **Present**, **Absent**, **Late**, and **On Leave** metrics.
- **Activity Pagination**: "Recent Attendance" and "Recent Leaves" feeds support clean pagination (5 per page).
- **Pulse Notifications**: Real-time red pulse badges on the dashboard tabs alert admins to new pending leave requests.
- **Pending Highlights**: "New" animated badges on list items catch immediate attention for pending tasks.
- **Visual Analytics**: Professional grouped bar charts for clear, comparison-ready attendance visualization (Present, Absent, Late, Leave).

### 📅 Smart Calendar & Scheduling
- **Manage Calendar Activities**: A dedicated full-screen module (`/schedules`) for high-level organizational planning.
- **Dynamic Event Management**: Admins can **Create**, **View**, and **Delete** custom company events, meetings, and local holidays.
- **Interactive Daily Overview**: Click any date to see a detailed popup containing:
    -   **Staff On Leave**: List of employees away with their avatars and leave types.
    -   **Event Details**: Full titles and descriptions for company activities and holidays.
- **Hybrid Data Layer**: Seamlessly merges Philippine Public Holidays (static) with User-Created Events (dynamic) and Approved Leaves (real-time).
- **Color-Coded Intelligence**: Instant visual distinction between Leaves (Purple), Regular Holidays (Red), Special Holidays (Orange), and Events (Blue).

### 📁 Unified Leave Management (HR Module)
- **Personnel Leave Authorization Form**: A pixel-perfect digital replica of the physical authorization form with strict validation.
- **Inclusive Day Counting**: Automatically calculates duration including weekends for accurate reporting.
- **Smart Date Blocking**: Prevents backdating of leave requests; enforces current/future dates only.
- **Comprehensive Metrics**: 5-column dashboard summary (Pending, Approved, Rejected, Cancelled, Total All Time).
- **Employee Insight Hero**: High-impact header displaying an employee's avatar, position, status, and live SIL balance when filtered.
- **Search & Filter**: Debounced search by **Name** or **Employee ID** (e.g. HQI-0001) for instant retrieval.
- **Overlap Prevention**: Intelligent validation system that prevents filing multiple leave requests for the same date, ensuring no "redundancy" in employee schedules.

### 💳 SIL Credit Tracking & Precision
- **Unified Credit System**: Consolidated SIL balance replacing fragmented credit types for simpler auditing.
- **precision Numeric Logic**: Strict numeric parsing ensures balance deductions and restorations are 100% accurate.
- **Visual Math Breakdowns**: Admins see the exact calculation (Original Bal - Deduction = New Bal) before approving requests.

### 📤 Reporting & Auditing
- **CSV Export Engine**: One-click generation of general or employee-specific leave reports.
- **Audit-Ready Data**: Exported files include Department, Position, Leave Type, Payment Status, and **Latest SIL Balance**.

### 🧠 Enterprise Decision Support (New)
- **Pattern Detection**: Automatically flags "Frequent Friday Leavers" and "Long Weekend Seekers" to alert HR of potential abuse.
- **Compliance Rules Engine**: Validates leaves against **Solo Parent Act**, **VAWS**, and **Maternity/Paternity** laws based on user demographics.
- **Department Impact Analysis**: Visualizes absenteeism impact (Critical/High/Low) if a request is approved, showing who else is away.
- **Credit Forecasting**: Projects year-end balances based on current burn rate and scheduled leaves.
- **Audit Trail**: Mandatory justification for all Admin actions (Approvals/Rejections), recorded in a tamper-proof `leave_action_logs` table.
- **File Attachments**: Optional document attachment (Medical Certificates, etc.) support for leave requests.

## ⚡ Performance & Scalability
- **Database Optimization**: Global indexing across `users` and `leave_requests` tables:
  - `users`: Indexed on `name`, `id_number`, `department`, `status`, `role`, and `position`.
  - `leave_requests`: Indexed on `created_at`, `from_date`, `to_date`, `request_type`, `status`, and `is_paid`.
- **Infrastructure**: Optimized for <50ms query times on frequently filtered data.
- **Network Efficiency**: Implemented **debouncing** on search inputs and **paginated API responses** to minimize server load.

## 🛠️ Tech Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3, Vite, TailwindCSS v4
- **State Management**: Pinia
- **Database**: PostgreSQL
- **Data Vis**: ApexCharts & Chart.js

### 📑 Integrated Reporting Center
- **Annual Company Report**: Detailed breakdown including Headcount, Attendance Rate, Absenteeism, Tardiness, **Undertime / Half Day**, and **Total Undertime (mins)**.
- **Departmental Analytics**: Monthly deep-dive into each department's Scheduled vs Actual Hours, **Regular Hours**, Overtime, Excess Hours, and **Excess Overtime Employee Count**.
- **Excel Export**: One-click download of all report data for offline analysis.
- **Mock Data Engine**: Reports are powered by a realistic simulation engine for instant demonstration.

### 🏢 Dynamic Department Management
- **Centralized Management**: Dedicated `departments` database table for scalable organizational structure.
- **Dynamic Assignment**: Employees can be assigned to departments dynamically via the "Add/Edit Employee" modals.

## 🚀 Installation & Setup

### 1. Prerequisites
- PHP 8.2+ & Composer
- Node.js & NPM
- PostgreSQL 15+

### 2. Quick Setup
```bash
# Clone and Install
git clone https://github.com/Cris-John-AFK/hq_application.git
cd hq_application
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database (Update .env with PGSQL credentials first)
php artisan migrate --seed
```

### 3. Development
```bash
# Start Backend
php artisan serve

# Start Frontend
npm run dev
```

## 🔐 Default Admin Credentials
| Email | Password |
| :--- | :--- |
| `admin@hq.app` | `password` |

---
*Created by the HatQ Inc. Engineering Team*
