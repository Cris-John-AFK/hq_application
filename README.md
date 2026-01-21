# HQ-App

A modern web application built with **Laravel 12**, **PostgreSQL**, and **Vue.js 3**.

## 🌟 Features
- **Full-Stack Integration**: Laravel (Backend) + Vue.js (Frontend) via Vite.
- **Database**: PostgreSQL configured efficiently.
- **Styling**: TailwindCSS v4 for modern, responsive designs.
- **Hybrid Support**: Supports both Blade templates and Vue components.
- **Role-Based Dashboards**: Distinct views for Admin and User roles managed by `Dashboard.vue`.
- **Universal Layout**: Shared `MainLayout.vue` with responsive Sidebar and Topbar for consistent UX.
- **Professional Sidebar**: Neumorphism design with image logo support, active state indicators, and smooth transitions.
- **Real-time Clock**: 12-hour format clock in the topbar with live updates.
- **Admin Modules**: 
  - Employee List with search, filter, pagination, and **Add Employee** functionality (Manual ID `HQI-XXXX`)
  - Enhanced Action Menu: View Leaves, Change Password, Mark On Leave
  - Leave request management with custom radio button UI
  - Attendance tracking and reporting
  - Modern stacked area chart with ApexCharts showing attendance trends
  - Dynamic calendar with month navigation and event tooltips
- **User Modules**:
  - Personal dashboard with quick stats
  - Profile page with **Photo Upload**, **Edit Profile**, and **Password Change** details with **Upload Confirmation**
  - Attendance history
  - Leave request submission
- **Reusable Components**:
  - `LeaveRequestModal.vue` - Google Forms-style modal for leave requests
  - `EventCalendar.vue` - Compact, interactive calendar with highlighted events
  - `EmployeeList.vue` - Advanced employee management table
  - `AttendanceChart.vue` - Interactive line chart for attendance trends (Present, Absent, Late, Leave) with date filtering
- **Routing**: **Vue Router 4** for seamless SPA navigation.
- **Data Visualization**: 
  - **Chart.js** for beautiful, responsive line charts
  - Interactive tooltips and custom legends
  - Dynamic time-period filtering (Daily, Weekly, Monthly) with date pickers
  - Smooth animations and gradient styling
- **State Management**: **Pinia** for centralized auth and app state.



## 🚀 Getting Started

Follow these steps to set up the project locally.

### 1. Prerequisites
- Docker (optional, if using Sail) or Local PHP/Composer/PostgreSQL setup.
- Node.js & NPM.

### 2. Installation
```bash
# Clone the repository
git clone https://github.com/Cris-John-AFK/hq_application.git
cd hq_application

# Install PHP dependencies
composer install

# Install JS dependencies
npm install
```

### 3. Configuration
1. Copy the example env file:
   ```bash
   cp .env.example .env
   ```
2. Update `.env` with your PostgreSQL credentials:
   ```ini
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=hq_app
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Generate the key and seed the database:
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
   > **Note**: The `--seed` command creates default Admin and User accounts.

### 4. Default Credentials
Use these accounts to log in at `http://localhost:8000/login`:

| Role  | Email           | Password |
| :---  | :---            | :---     |
| **Admin** | `admin@hq.app`  | `password` |
| **User**  | `user@hq.app`   | `password` |

### 5. Running the App
Start the development servers:
```bash
# Terminal 1: Laravel Backend
php artisan serve

# Terminal 2: Vite Frontend (Hot Output)
npm run dev
```

## 🔐 Authentication & Roles
The app uses a hybrid authentication system:
- **Backend**: Laravel Session Auth (Stateful).
- **Frontend**: Vue.js + **Pinia** for state management.
- **Roles**: Users are assigned roles (`admin` or `user`) which determine their access level.

### Logic Flow
1. User submits login form (Vue).
2. **Axios** sends credentials to Laravel.
3. Laravel validates and starts a session.
4. **Pinia** updates the user state and redirects to `/dashboard`.
5. **Vue Router Guards** ensure protected routes cannot be accessed without valid authentication, redirecting unauthenticated users to `/login`.

Visit `http://localhost:8000` to see the app.
Visit `http://localhost:8000/vue` for a Vue.js demonstration.

## 📁 Component Structure

The Vue.js components are organized as follows:

```
resources/js/components/
├── admin/              # Admin-specific components
│   └── LeaveRequests.vue
├── user/               # User-specific components
│   ├── Dashboard.vue
│   ├── Profile.vue
│   ├── Attendance.vue
│   └── LeaveRequests.vue
├── common/             # Shared/reusable components
│   ├── LeaveRequestModal.vue  # Reusable leave request form
│   ├── EventCalendar.vue      # Interactive calendar
│   └── EmployeeList.vue       # Employee management table
├── login/              # Authentication components
│   └── LoginForm.vue
└── Dashboard.vue       # Main dashboard router
```

### Key Components

- **LeaveRequestModal.vue**: A reusable Google Forms-style modal for submitting leave requests. Used by both admin (for employees) and users (for themselves).
- **EventCalendar.vue**: Dynamic calendar with month navigation, event tooltips, and today's date highlighting.
- **EmployeeList.vue**: Advanced table with search, filtering by department/status, pagination, and inline status management.

## 🛣️ Routing

The application uses **Vue Router 4** with separate routes for admin and user roles:

### Admin Routes
- `/dashboard` - Admin dashboard
- `/employees` - Employee management
- `/attendance` - Attendance tracking (admin view)
- `/schedules` - Schedule management
- `/reports` - Reports and analytics
- `/settings` - Application settings

### User Routes
- `/dashboard` - User dashboard
- `/profile` - Personal profile
- `/my-attendance` - Personal attendance history
- `/leave-requests` - Submit and view leave requests

All routes are defined in `resources/js/router/index.js` and automatically set page titles via navigation guards.

## 📚 Documentation
Please refer to the [Developer Manual](manual.md) for detailed instructions on:
- Creating new Vue components.
- Adding new Routes and Pages.
- Detailed architectural decisions.

## Tech Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3, Vite, TailwindCSS v4
- **Charts**: Chart.js (via chart.js package)
- **Database**: PostgreSQL

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
