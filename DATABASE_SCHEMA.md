# Database Schema Documentation

This document provides a detailed list of all database tables and their attributes used in the HQ Application.

## Tables

### 1. `users`
Core table for managing application users, including their roles, employment details, and leave credits.

| Attribute | Type | Details |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key, Auto-increment |
| `name` | String | User's full name |
| `email` | String | Unique email address |
| `avatar` | String | Profile picture path (Nullable) |
| `role` | String | User roles (e.g., admin, user) |
| `position` | String | Job position (Nullable, Indexed) |
| `department` | String | Department name (Nullable, Indexed) |
| `id_number` | String | Unique employee ID (e.g., HQI-0001, Indexed) |
| `status` | String | Work status (e.g., Available, Indexed) |
| `employment_status` | String | Probationary or Regular |
| `leave_credits` | Decimal(8,2) | Consolidated leave credits |
| `email_verified_at`| Timestamp | Email verification date (Nullable) |
| `password` | String | Hashed password |
| `remember_token` | String | Remember me token |
| `created_at` | Timestamp | Account creation date |
| `updated_at` | Timestamp | Last update date |

### 2. `leave_requests`
Tracks all leave-related requests from users.

| Attribute | Type | Details |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key, Auto-increment |
| `user_id` | ForeignID | Links to `users.id` (Cascade on Delete) |
| `date_filed` | Date | Date the request was submitted (Nullable) |
| `leave_type` | String | SIL, Maternity, Paternity, etc. (Indexed) |
| `request_type` | String | Leave, Halfday, Undertime, Official Business |
| `from_date` | Date | Start date of leave (Indexed) |
| `to_date` | Date | End date of leave (Nullable) |
| `days_taken` | Decimal(5,2) | Total days (e.g., 0.5, 1.0) |
| `start_time` | Time | For Undertime/Halfday (Nullable) |
| `end_time` | Time | For Undertime/Halfday (Nullable) |
| `reason` | Text | Reason for the request (Nullable) |
| `status` | String | Pending, Approved, Rejected, Cancelled (Indexed) |
| `is_paid` | Boolean | Whether the leave is paid |
| `days_paid` | Decimal(5,2) | Number of paid days |
| `admin_remarks` | Text | Remarks from HR/Admin (Nullable) |
| `created_at` | Timestamp | Request creation date |
| `updated_at` | Timestamp | Last update date |

### 3. `password_reset_tokens`
Standard Laravel table for managing password resets.

| Attribute | Type | Details |
| :--- | :--- | :--- |
| `email` | String | Primary Key, linked to user email |
| `token` | String | Reset token |
| `created_at` | Timestamp | Token creation date (Nullable) |

### 4. `sessions`
Standard Laravel table for session management.

| Attribute | Type | Details |
| :--- | :--- | :--- |
| `id` | String | Primary Key |
| `user_id` | ForeignID | Link to `users.id` (Indexed, Nullable) |
| `ip_address` | String(45) | User's IP address (Nullable) |
| `user_agent` | Text | User's browser agent (Nullable) |
| `payload` | LongText | Session data |
| `last_activity` | Integer | Last activity timestamp (Indexed) |

### 5. `cache` & `cache_locks`
Laravel caching tables.

#### `cache`
- `key` (String, Primary)
- `value` (MediumText)
- `expiration` (Integer)

#### `cache_locks`
- `key` (String, Primary)
- `owner` (String)
- `expiration` (Integer)

### 6. Queue Management
Laravel standard tables for background jobs.

#### `jobs`
- `id` (BigInt, PK)
- `queue` (String, Indexed)
- `payload` (LongText)
- `attempts` (TinyInt)
- `reserved_at` (Integer, Nullable)
- `available_at` (Integer)
- `created_at` (Integer)

#### `job_batches`
- `id` (String, PK)
- `name` (String)
- `total_jobs`, `pending_jobs`, `failed_jobs` (Integer)
- `failed_job_ids` (LongText)
- `options` (MediumText, Nullable)
- `cancelled_at`, `finished_at` (Integer, Nullable)
- `created_at` (Integer)

#### `failed_jobs`
- `id` (BigInt, PK)
- `uuid` (String, Unique)
- `connection`, `queue` (Text)
- `payload`, `exception` (LongText)
- `failed_at` (Timestamp, Now)
