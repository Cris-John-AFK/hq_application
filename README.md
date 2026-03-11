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
- **Cyberpunk Admin Experience**: The "Review Leave" terminal features a **Sticky Neural Sidebar** optimized for high-performance scrolling.
- **Bulk Action Command Center**: A sleek, floating "Glassmorphism" toolbar in the Leave Management grid allows administrators to select dozens of requests and Approve/Reject them in a single click with automated progress tracking.
- **Omni-Search Hub**: A high-performance global search bar in the topbar (accessible with a "Command Palette" feel) to instantly teleport to any Employee Profile or Leave RID from anywhere in the app.
- **Surgical Deep-Linking**: The system now supports `rid` and `user_id` URL parameters, allowing search results to instantly open the targeted administrative context automatically.
- **Reactive Visual Effects**: Integrated neon "Neural Trails" that animate based on scroll direction (Up/Down) and an ambient "Neural Pulse" aura to signify sticky focus.
- **GPU-Accelerated Silk-Scroll**: Leverages `requestAnimationFrame` and hardware acceleration (`will-change: transform`) to ensure a smooth, lag-free experience even with complex visual effects.

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
- **Clean UI Logic**: The employee filing form intelligently hides redundant leave balances (already visible in the portal sidebar) while retaining them for HR filing-on-behalf.
- **Hybrid Data Fetching**: User dashboards dynamically fetch both web-submitted and portal-submitted records by cross-referencing **ID Number** and **Employee ID**.
- **Service-Oriented Processing**: Critical leave logic (deductions, notifications, audits) is centralized in the **LeaveService** layer, ensuring 100% processing consistency between individual and bulk actions.

### 🧪 Biometric Attendance Engine & Historical Snapshots
- **Shift Integrity Snapshots**: The system now implements a "Snapshot" pattern for working hours. When biometric data is imported, the employee's **current** shift times (e.g., 07:00 AM - 03:00 PM) are permanently stamped onto the attendance record. 
    - **Previous Data Safety**: If you change an employee's shift to 06:00 AM next month, their past attendance from this month will **not** turn "Late." The system remembers they were originally on a 07:00 AM shift for those specific dates.
- **Multi-Sheet Universal Excel Parser**: High-performance client-side engine capable of reading 3-sheet formats to import Masterlists, schedule working hours, and initial leave balances.
- **Robust Transactional Imports**: All bulk imports (Attendance & Masterlist) are wrapped in **Database Transactions**. If a single row is malformed or has an error, the system skips it safely or rolls back to prevent partial data corruption.
- **Dynamic Lateness Classification**: Automatically determines **Present**, **Late**, **Half-Day**, or **Absent** statuses based on the snapshot working hours stored at the time of import.

### 📅 Advanced Scheduling & Shift Management
- **Dynamic Shift Registry**: Administrators can now manage the organization's shift library via the **"⚙️ Manage Shifts"** command center in the Masterlist.
    - **Custom Codes**: Create codes like `SHIFT_A`, `GY_SHIFT`, or `FLEX_1`.
    - **Global Sync**: Once a shift code is added, it becomes available in all dropdowns (Add/Edit Employee) and is automatically recognized by the Excel Import engine.
- **Editable Working Hours**: Employee working hours are no longer read-only. You can manually tweak an individual's schedule directly from their "Edit Profile" modal.
- **Philippine Compliance**: Pre-loaded with Philippine Public Holidays integrated into the dynamic organizational calendar.

### 👤 System Account Management & Promotion
- **One-Click Promotion**: Easily "Promote" any employee in the Masterlist to a System User (Admin, Dept Head, or User) using the **"Create Account"** action.
- **Department Overrides (Managers Layer)**: Solves the "Manager Managing Managers" conflict.
    - **The Problem**: Top-level managers often belong to a "Managers" department in the masterlist.
    - **The Solution**: When promoting a Manager to a `Dept Head`, the system now provides a **"Managed Department"** dropdown. You can select "Accounting" or "HR" as their managed scope, even if their personal profile stays in the "Managers" list.
- **Automatic Name Sync**: Importing a Masterlist update for an employee will automatically sync their corrected name to their linked System Account.

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
- **Birthdate Transparency**: The `birthdate` field is now visible and editable in employee profiles to ensure HR can verify age-based compliance and records.
- **Advanced Action Forensic Index**: A fully searchable Audit Trail featuring a **Visual Diff Analyzer** that highlights precisely which data fields were modified (e.g., "Status: Pending → Approved").
- **Real-Time Log Search**: Debounced search bar to instantly find activities by **User Name**, **IP Address**, **Description**, or **Device Type**.
- **Kiosk Session Protection**: The Employee Portal now uses **Session-Only Storage** to prevent credentials from persisting between browser sessions.
- **History Snarfing Prevention**: Proactive "Login Guard" wipes all session data upon landing on the login terminal, preventing session recovery via the "Back/Forward" browser buttons.
- **RBAC Hardening**: Unified `AdminMiddleware` protects all HR and Administrative routes, ensuring strict Role-Based Access Control.
- **Privacy First**: Sensitive employee metadata (e.g., Birthdate PINs) is strictly hidden from API JSON responses and never exposed to the frontend.
- **Tiered Rate Limiting**: Deployed granular security throttles:
    - **Admin Critical**: 15-30 requests/min for approvals and bulk updates.
    - **Global Search**: Throttled to prevent automated data enumeration.
    - **Portal API**: Strict 10 requests/min to halt brute-force attempts.
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
```

### 3. Database Setup
```bash
php artisan migrate --seed
```

---

## 💻 Running the Development Server

### Option A — Local Only (your machine only)
Open **two separate terminals** and run:

```bash
# Terminal 1 – Laravel API backend
php artisan serve

# Terminal 2 – Vite frontend (HMR)
npm run dev
```

Access via: `http://localhost:5173`

---

### Option B — Local Network Access (share with other PCs on the same Wi-Fi/LAN) ⭐

> Use this when you want other computers or phones on the same network to open the app.

**Step 1 — Find your machine's local IP address**
```powershell
# Run in PowerShell or CMD
ipconfig
# Look for: IPv4 Address → e.g. 10.10.10.8
```

**Step 2 — Start both servers bound to all interfaces**
```bash
# Terminal 1 – Laravel backend (bound to all network interfaces)
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 – Vite frontend (exposed to network)
npm run dev -- --host
```

**Step 3 — Access from other devices**

| Device | URL to open |
| :--- | :--- |
| Your machine | `http://localhost:5173` |
| Other PC / Phone on same Wi-Fi | `http://10.10.10.8:5173` *(replace with your actual IP)* |

> [!IMPORTANT]
> **Firewall:** Windows Firewall may block incoming connections. If other devices can't connect, run this in an **Administrator PowerShell** to open the ports:
> ```powershell
> netsh advfirewall firewall add rule name="HQ-App Vite" dir=in action=allow protocol=TCP localport=5173
> netsh advfirewall firewall add rule name="HQ-App Laravel" dir=in action=allow protocol=TCP localport=8000
> ```

> [!NOTE]
> **How it works internally:** Vite (`npm run dev -- --host`) acts as the UI server and automatically proxies all `/api/*` requests to Laravel on port 8000. Remote devices only need to open the Vite port (5173). Laravel on port 8000 handles all API calls transparently in the background.

---

## 📖 Operational Procedures & Calculation Logic

This section defines the strict workflows and processing rules used by the HatQ HQ-App.

### ⚙️ 1. Managing Working Hours & Shifts
To ensure flawless attendance tracking, follow these steps:
1. **Define your Shifts**: Go to **Employee Masterlist** → click the **"⚙️ Manage Shifts"** button. Add your standard shift codes (e.g., "A", "7-3", "B") and their exact times.
2. **Assign to Employees**: Open an employee's profile and select the shift from the dropdown. This is the **Source of Truth** for their lateness.
3. **Importing Attendance**: When you import the Biometric Excel, the system looks at this assigned shift and "snapshots" it into the attendance record. Even if you change their shift later, the system remembers what they were assigned *at the time* of the record.

### 🛡️ 2. Transferring Dept Heads
When a Dept Head is replaced or promoted:
1. **Demote Previous**: Edit the old user's account and change their role back to `User`.
2. **Promote New**: Find the new employee in the Masterlist → Click **"Create Account"** → Select **Role: Dept Head**.
3. **Set the Scope**: In the **"Managed Department"** dropdown, select the actual department they will lead (e.g., "Production").
4. **Data Retention**: All previous leave approvals and remarks from the *old* head are permanently stored. The *new* head will immediately inherit access to all current pending requests for that specific department's scope.

### 📊 3. Masterlist & Headcount Rules
The "Total Employees" (Headcount) in any report is not a static number. It is a **historically accurate** calculation that "time travels" based on your data:
- **Hiring Gatekeeper**: An employee only appears in a report if their `date_hired` is on or before the last day of that reporting month.
- **Archive Memory**: If you archive an employee on March 1st, they **will still appear** in the February headcount (because they were active in February), but they will vanish from the March headcount.
- **Dynamic Sync**: Headcount updates automatically every time you view a report—no manual refresh needed.

### ⏱️ 4. Attendance & Absence Logic
The system follows a "Cold Fact" rule for every workday (dates with at least one record captured):
- **Present**: Recorded when an employee has BOTH a valid `Time-In` and `Time-Out`.
- **Strict Absence Rule**: An employee is automatically flagged as **Absent** if:
    1. They have **zero** attendance records for a scheduled working day.
    2. They have a `Time-In` but **NO `Time-Out`** (or vice versa). Missing half the log equates to a failed shift.
- **Half-Day Status**: Triggered if the `Hours Worked` is greater than 0 but less than 5.0 hours. This is automatically capped at **240 minutes** of lost time (Undertime).

### 🚨 5. Tardiness (Lateness) Process
Lately, the system treats every minute as a fact based on **Individualized Working Hours**:
- **The Comparison**: `Actual Time-In` vs. `Expected Start Time` (from the Masterlist).
- **Grace Period**: There is **0 minutes** of grace. 07:01 AM is 1 minute late for a 07:00 AM shift.
- **Minute Summation**: Every single minute is summed into the "Total Tardiness (mins)" column.
- **Anomaly Protection (8-Hour Cap)**: To prevent night-shift workers or data errors from creating 700+ minute spikes, any single instance of lateness is **capped at 480 minutes (8 hours)**.
- **Formula**: `(Actual Time - Scheduled Start) = Tardiness Mins`.

### 📉 6. Report Formula Definitions
You can hover over any header in the web reports to see these, but here is the technical breakdown:
- **Attendance Rate (%)**: `(Present Days + Undertimes) ÷ (Headcount × Working Days)`.
- **Absenteeism Rate (%)**: `(Total Absent Days) ÷ (Headcount × Working Days)`.
- **Tardiness Frequency (%)**: `(Number of Late Instances) ÷ (Headcount × Working Days)`.
- **Undertime Frequency (%)**: `(Number of Undertime/Half-Day Instances) ÷ (Headcount × Working Days)`.
- **Total Scheduled Hrs**: `Σ (Employee Shift Length × Working Days)`. (Example: A 9-hour shift employee scheduled for 20 days = 180 scheduled hours).

### 🏢 7. Departmental Performance
The **Monthly Department Report** compares the performance of specific teams:
- **Actual Working Days**: Counts the unique dates where **that specific department** had at least one employee on site.
- **Actual vs. Scheduled**: Tracks how many hours were rendered vs. how many were expected based on the unique shifts of that team's personnel.

---

*Updated March 2026*
*Created by Cris John M. Cañales & Jessica Roque*
*Support: crisjohn.canales@gmail.com | jessicaroque12@gmail.com*
