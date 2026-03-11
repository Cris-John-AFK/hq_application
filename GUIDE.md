# 📘 HQ-App: Local Setup & Maintenance Guide

This guide provides everything you need to set up, run, and maintain the **HatQ HQ Leave Management System** on a fresh Windows environment.

---
## 📌 Table of Contents 
1.  [**🛠️ Required Software**](#required-software)
2.  [**🏴‍☠️ Cloning a Private Repo**](#cloning-private)
3.  [**🚀 Setting Up (First Time)**](#setting-up)
4.  [**🏁 How to Run (Daily Use)**](#how-to-run)
5.  [**⚡ One-Click Startup (Recommended)**](#one-click-startup)
6.  [**🌐 Sharing on Wi-Fi**](#sharing-on-wifi)
7.  [**🏗️ Scalability (Billion Data)**](#scalability)
8.  [**🛠️ Maintenance Checklist**](#maintenance)
9.  [**🔄 Remote Updates**](#remote-updates)
10. [**🔐 Code Security & Privacy**](#security--privacy)
11. [**🏗️ Building for Protection**](#building-for-protection)
12. [**📂 Master Excel Data**](#master-excel-data)

---

## <a name="required-software"></a>🛠️ 1. Required Software (The "Shopping List")

To run this system locally, you MUST download and install these specific versions of software. Install them in this exact order:

1.  **PHP 8.2 (via XAMPP)**
    *   **Download**: [apachefriends.org](https://www.apachefriends.org/index.html) (Select the version with **PHP 8.2.x**)
    *   **Purpose**: The "engine" that runs the Laravel backend code.
2.  **Node.js (LTS Version)**
    *   **Download**: [nodejs.org](https://nodejs.org/) (Select **20.x or 22.x LTS**)
    *   **Purpose**: Runs the Vite frontend and manages design assets.
3.  **PostgreSQL 15+**
    *   **Download**: [enterprisedb.com/downloads/postgres-postgresql-downloads](https://www.enterprisedb.com/downloads/postgres-postgresql-downloads)
    *   **Purpose**: The enterprise database that stores all employee and attendance data.
4.  **pgAdmin 4**
    *   **Download**: Included with PostgreSQL (or separate at [pgadmin.org](https://www.pgadmin.org/download/))
    *   **Purpose**: The visual "Control Panel" where you can see your database tables.
5.  **Composer**
    *   **Download**: [getcomposer.org](https://getcomposer.org/download/)
    *   **Purpose**: Downloads the "Brain" (libraries) of the Laravel framework.
6.  **Git (Optional but Recommended)**
    *   **Download**: [git-scm.com](https://git-scm.com/)
    *   **Purpose**: For tracking changes and version control.

### 🧪 Step 0: Check if you have them already
Run these in your terminal to verify installations and versions:
```bash
php -v            # Should show 8.2.x
node -v           # Should show v20 or v22
composer -v       # Should show 2.x
psql --version    # Should show 15.x or 16.x
```

---

## <a name="cloning-private"></a>🏴‍☠️ 2. Cloning a Private Repository

Since your project is **Private**, a regular `git clone` will fail. You have two options to download it on the central PC:

### Option A: Using a Personal Access Token (**RECOMMENDED for Updates**)
1.  On your GitHub account (at home), go to **Settings** -> **Developer Settings** -> **Personal Access Tokens** -> **Tokens (classic)**.
2.  Generate a new token with **`repo`** permissions. **Copy it!**
3.  On the company PC, run:
    ```bash
    # Replace <TOKEN> with your token and <USER> with your username
    git clone https://<USER>:<TOKEN>@github.com/Cris-John-AFK/hq_application.git
    ```
> [!IMPORTANT]
> **Why use Git?** If you use Git Clone, you can run **`REMOTE HQ-APP.bat`** later to update the code in 1 second. 

### Option B: Manual ZIP (One-Time Only)
1.  Go to GitHub on your browser.
2.  Click **Code** -> **Download ZIP**.
3.  Extract the ZIP folder on the company PC.
> [!WARNING]
> If you use the ZIP method, you **cannot** use the automatic update scripts. You would have to download a new ZIP every time you change something!

---

## <a name="setting-up"></a>🚀 3. Setting Up the Project (First Time)

Once the software is installed, follow these commands in your **Terminal / PowerShell** inside the project folder:

### Step 1: Install Dependencies
```bash
# Install PHP backend libraries
composer install

# Install Frontend design libraries
npm install
```

### <a name="env-config"></a>⚙️ Step 2: Configure Environment (.env)
When you first clone/download the project, you won't have a `.env` file. You must create one:

1.  **Copy the template**:
    ```bash
    cp .env.example .env
    ```
2.  **Open `.env` in Notepad** and check these 3 vital lines:
    *   `DB_DATABASE=hq_db` (Must match the name you create in pgAdmin)
    *   `DB_USERNAME=postgres`
    *   `DB_PASSWORD=your_postgres_password_here` (Fill this in!)
3.  **Generate Security Key**:
    ```bash
    php artisan key:generate
    ```

### Step 3: Link Storage
Allows the system to display uploaded avatars and documents:
```bash
php artisan storage:link
```

### Step 4: Prepare the Database
Make sure PostgreSQL is running, then create a database named `hq_db` and run:
```bash
php artisan migrate --seed
```

---

## <a name="how-to-run"></a>🏁 4. How to Run the System (Daily Use)

> [!TIP]
> **No VS Code Needed!** You do NOT need to install VS Code on the central PC. A regular user only needs the **Requirements** (PHP/Node/Postgres) and they can just double-click the `.bat` files to start the app.

To use the app, you MUST open **TWO terminal windows** at the same time:

**Terminal 1 (Backend)**
```bash
php artisan serve
```
*Wait for: `Server running on http://127.0.0.1:8000`*

**Terminal 2 (Frontend - ONLY for development)**
```bash
npm run dev
```
*Wait for: `Vite running on http://localhost:5173`*

> [!TIP]
> **Always use Port 8000 for Central PC setup!** As the Central PC, the system serves the "Built" version automatically for maximum security and performance.

---

## <a name="one-click-startup"></a>⚡ 4. One-Click Startup & Server Recovery

This is the **safest and easiest** way for the company staff to start the system, especially after a power outage.

### How to use `START_SYSTEM.bat`:
In the project folder, there is a file I created for you called **`START_SYSTEM.bat`**. 
1.  **Staff Instruction**: Tell the staff to just **Double-Click** this file. 
2.  **What it does automatically**:
    *   **Auto-Detect IP**: It finds the current Wi-Fi/Network IP address of the PC.
    *   **Auto-Update `.env`**: It automatically updates the `APP_URL` in your configuration so you don't have to do it manually.
    *   **Auto-Start Servers**: It launches both the Backend and Frontend in separate screens.
    *   **Network Ready**: It instantly makes the app accessible to the whole office Wi-Fi.

> [!TIP]
> You can create a **Desktop Shortcut** of this `.bat` file for the staff. If the power goes out, they just reboot the PC and double-click the shortcut!

---

## <a name="sharing-on-wifi"></a>🌐 5. Sharing on Wi-Fi (Network Access)

To let other computers or phones on the same Wi-Fi access the app:

### Step 1: Find your IP Address
Run this in your terminal:
```powershell
ipconfig
```
Look for **`IPv4 Address`** (Example: `192.168.1.15`).

### Step 2: Run Servers for the Network
Close your normal servers and run these instead:
```bash
# Terminal 1: Laravel Backend
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Vite Frontend
npm run dev -- --host
```

### Step 3: Open on other devices
On a different phone or laptop on the same Wi-Fi, type your IP and port **8000**:
`http://192.168.1.15:8000`

> [!NOTE]
> By visiting port 8000, you are accessing the **Compiled Build**. This is secure and much faster for multiple users on a Wi-Fi network.

### 🛠️ Step 4: Updating the IP Address (.env Sync)
If your Wi-Fi router reboots and gives your PC a **new IP Address**, you MUST update your environment file to match.

1.  Open the **`.env`** file in your project folder.
2.  Find the line **`APP_URL`**.
3.  Change it to your NEW IP:
    ```env
    APP_URL=http://192.168.1.20:8000
    ```
4.  **Frontend Note**: You do NOT need to hardcode the IP anywhere in the Vue/Javascript code. The system uses "Relative API Calls," meaning it will automatically follow whatever IP the browser is currently visiting!

> [!IMPORTANT]
> **Firewall Block?** If it doesn't load, run this in **Administrator PowerShell** to allow the port 8000 (Central PC):
> ```powershell
> netsh advfirewall firewall add rule name="HQ-App Production" dir=in action=allow protocol=TCP localport=8000
> ```

---

## <a name="scalability"></a>🏗️ 6. Scalability & Performance "Billion Data" Reality

You asked if the system can handle **1 Million rows per month** or **Billion rows a year**. Here is the technical assessment:

### **The Good News (Database Integrity)**
*   **PostgreSQL Prowess**: We are using **PostgreSQL**, which is an industry titan. It can easily store billions of rows of data. Many of the world's largest banks use PostgreSQL for trillions of transactions.
*   **Indexing**: Every attendance record is "Indexed" by `employee_id` and `date`. This means even if you have a billion rows, searching for one employee's data takes **milliseconds**, not minutes.

### **The Challenges (Network & Memory)**
*   **Browser Memory**: Your browser (Chrome/Edge) cannot display 1 million rows at once—it will crash. This is why we use **Pagination** (e.g., 20 rows per page).
*   **Excel Imports**: Importing 1 million rows from a single Excel file is very heavy. At that scale, we recommend splitting Excel files into **50,000 rows each** for maximum stability.
*   **Monthly Reports**: Aggregating a billion rows for a "Monthly Total" is the most intense part. To handle this, the system is designed to "Snapshot" and calculate totals only when requested, ensuring the rest of the app stays fast.

### **Recommendation for "Big Data"**
If you truly reach **Billions of rows**, we recommend:
1.  **Increase RAM**: Ensure your local server has at least **16GB-32GB of RAM**.
2.  **Date Partitioning**: (Advanced) We can split the database table into "Years" so each year is its own separate storage unit, keeping individual queries ultra-fast.

---

## <a name="maintenance"></a>🛠️ 7. Maintenance Checklist
*   **Check Audit Trail**: Regularly view the "Audit Trail" in the app to see who changed what.
*   **Backup Database**: Every week, run a backup in **pgAdmin 4** (Right-click Database -> Backup).
*   **Clear Logs**: If the app feels slow, run `php artisan logs:clear`.

---

## <a name="remote-updates"></a>🔄 8. Remote Updates & Auto-Deployment
To update the company PC from home without visiting:
1.  **Push from Home**: Use `git push origin main`.
2.  **Run REMOTE HQ-APP.bat**: On the company PC, just double-click that file. It will pull the new code, migrate the DB, and rebuild the security frontend for you!

> [!CAUTION]
> This requires you to have installed the app using **Git Clone** (Option A in Section 2). It will not work with a ZIP download.

---

## <a name="security--privacy"></a>🔐 9. Code Security & Privacy
To protect your source code from staff:
1.  **Hidden Folder**: Move the `HQ-App` folder to a hidden directory (e.g., `C:\ProgramData\HQ-App`).
2.  **Shortcut Only**: Only provide the **START HQ-APP** shortcut on the staff desktop.
3.  **Permissions**: Right-click folder > Properties > Security. Restrict "Read" rights for the "Everyone" group but allow "Execute" for the specific script.
4.  **Production Build**: Always run `npm run build`. This "minifies" your frontend code into unreadable text, making it impossible to copy your logic.

---

## <a name="building-for-protection"></a>🏢 10. Building for Protection
To ensure that staff cannot edit or easily read your frontend logic:
1.  **Build your assets!** Run this command once after any design change:
    ```bash
    npm run build
    ```
2.  **What this does**: It takes all your `.vue` and `.js` files and mashes them into a few "unreadable" minified files inside `public/build`.
3.  **The Result**: When you run the `START HQ-APP.bat` script, it only starts the Laravel server (Port 8000). Laravel will then serve these scrambled, compiled files. Anyone trying to "Inspect Element" will only see code like `a(b,c){return d}`—meaning your secrets are safe.

---

## <a name="master-excel-data"></a>📂 12. Master Excel Data
All the standard Excel templates and sample data used for importing into the system are stored in the root folder:

### Location: `MASTER_EXCEL_DATA/`
*   **`inventory_details.xlsx`**: The master template for your equipment inventory.
*   **`biometric_attendance_sample.xls`**: A sample of the biometric log format.
*   **`Attendance_TimeLogs/`**: A folder containing multiple historical biometric data samples (`TimeLogs (12).xlsx`, etc.).

> [!TIP]
> Use these files as a reference when preparing your own Excel data for upload. Make sure to keep the column headers exactly as they are in these samples!
