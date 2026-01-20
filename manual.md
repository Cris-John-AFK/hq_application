# HQ-App Developer Manual

This manual guides you through setting up and adding features to the HQ-App, which uses **Laravel 12** (PHP), **Vue.js 3**, and **PostgreSQL**.

## 1. Database Setup (PostgreSQL)

Before running the application, you must configure the database.

1.  **Install PostgreSQL**: Ensure you have PostgreSQL installed and running on your machine.
2.  **Create a Database**: Open your terminal (or pgAdmin) and create a new database.
    ```sql
    CREATE DATABASE hq_app;
    ```
3.  **Configure Environment**:
    *   Duplicate `.env.example` and name it `.env` if you haven't already.
    *   Open `.env` and update the database configuration:
        ```ini
        DB_CONNECTION=pgsql
        DB_HOST=127.0.0.1
        DB_PORT=5432
        DB_DATABASE=hq_app
        DB_USERNAME=your_postgres_username
        DB_PASSWORD=your_postgres_password
        ```
4.  **Run Migrations**:
    ```bash
    php artisan migrate
    ```

## 2. Frontend Development (Vue.js)

The application uses Vite to compile Vue components.

### Creating a New Vue Component

1.  Navigate to `resources/js/components`.
2.  Create a new file, e.g., `MyNewComponent.vue`.
    ```vue
    <template>
        <div class="my-component">
            <h2>{{ title }}</h2>
        </div>
    </template>

    <script setup>
    import { ref } from 'vue';
    const title = ref('My New Component');
    </script>
    ```
3.  Register the component in `resources/js/app.js`:
    ```javascript
    import MyNewComponent from './components/MyNewComponent.vue';
    app.component('my-new-component', MyNewComponent);
    ```

### Using the Component in a View

You can use your registered Vue component in any Blade file that includes the app script.

```html
<!-- Inside a blade file -->
<div id="app">
    <my-new-component></my-new-component>
</div>
```

## 3. Creating a New Page (Laravel Routing)

To add a new page to your application:

1.  **Create a View**: Create a new Blade file in `resources/views`, e.g., `dashboard.blade.php`.
    ```html
    <!DOCTYPE html>
    <html>
    <head>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app">
            <h1>Dashboard</h1>
            <!-- Add Vue components here -->
        </div>
    </body>
    </html>
    ```

2.  **Define a Route**: Open `routes/web.php` and add a route for your page.
    ```php
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
    ```
    Alternatively, use a Controller:
    ```bash
    php artisan make:controller DashboardController
    ```
    Then in `routes/web.php`:
    ```php
    use App\Http\Controllers\DashboardController;

    Route::get('/dashboard', [DashboardController::class, 'index']);
    ```

## 4. Running the Project

1.  **Start the Backend Server**:
    ```bash
    php artisan serve
    ```
2.  **Start the Frontend Dev Server** (Hot Module Replacement):
    ```bash
    npm run dev
    ```

Access the application at `http://localhost:8000`.
Visit `http://localhost:8000/vue` to see the Vue integration demo.
