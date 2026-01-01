# Task Management System

A robust Task Management API built with Laravel, featuring task dependencies, user assignment, and status management.

## Features

- **User Authentication**: Secure authentication using Laravel Sanctum.
- **Task Management**: Full CRUD operations for tasks.
- **Task Dependencies**:
    - Tasks can depend on other tasks.
    - Prevents completion of tasks until all dependencies are completed.
    - Circular dependency detection.
    - Task can't depend on itself.
- **Task Assignment**: Assign tasks to a specific user.
- **Status Management**: Track tasks as Pending, Completed, or Canceled.
- **Filtering**: Filtering based on status, due
date range, or assigned user.

## Prerequisites

- PHP 8.1
- Laravel 10
- Composer
- MySQL

## Installation & Setup

1. **Clone the repository**

   git clone <https://github.com/Nouran-Ebrahim/Task_management_system.git>
   cd Task_management_system

2. **Install Dependencies**

   composer install

3. **Environment Setup**
 Configure database settings in `.env` file:
 set database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

4. **Generate Application Key**
   php artisan key:generate
5. **Run Migrations**
   Create the database tables:

   php artisan migrate --seed


6. **Serve the Application**
   php artisan serve
 
   The API will be accessible at `http://localhost:8000`.

## API Documentation

### Authentication
- `POST /api/login`: Login and receive an API token.
- `POST /api/logout`: Logout and revoke tokens (Requires Auth).

### Tasks
All task routes require authentication (Bearer Token).

- `GET /api/tasks`: List all tasks (supports filtering).
- `POST /api/tasks/store`: Create a new task.
- `GET /api/tasks/show/{id}`: View task details.
- `PUT /api/tasks/update/{id}`: Update task details.
- `DELETE /api/tasks/delete/{id}`: Delete a task.
- `POST /api/tasks/statusUpdate/{id}`: Update task status (Pending/Completed/Canceled).
- `POST /api/tasks/assign/{id}`: Assign a task to a user.

### Dependencies
- `POST /api/tasks/addDependencies/{id}`: Add dependencies to a task.
- `POST /api/tasks/removeDependency/{id}`: Remove dependency from a task.

## Authorization & Permissions

The application uses **Policy-based authorization** to control access to tasks based on user roles and assignment.

### Roles
1. **Manager**
   - Has full access to all tasks.
   - Can **Create**, **Update**,**Update Status** **Delete**, **Assign**,and **View** tasks.
   - Can manage **Dependencies** (Add/Remove).
   - Can **Retrieve** all tasks.

2. **User (Assignee)**
   - Can only **Retrieve** tasks assigned to them.
   - Can only **View** tasks assigned to them.
   - Can **Update Status** of their assigned tasks (e.g., mark as Completed).
   - *Cannot* create, delete, update or reassign tasks.
   - *Cannot* Add/Remove task dependencies.
