# Project Plan: PHP CRUD Application

This document outlines the plan for developing a simple, secure, and feature-rich web application using PHP and MySQL. The project is divided into four main tasks.

## Task 1: Basic CRUD and Authentication

### 1. Database Setup

- **Database:** `blog`
- **Tables:**
  - `posts`
    - `id` INT PRIMARY KEY AUTO_INCREMENT
    - `title` VARCHAR(255) NOT NULL
    - `content` TEXT NOT NULL
    - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  - `users`
    - `id` INT PRIMARY KEY AUTO_INCREMENT
    - `username` VARCHAR(255) NOT NULL UNIQUE
    - `password` VARCHAR(255) NOT NULL

**SQL Schema:**
```sql
CREATE DATABASE blog;

USE blog;

CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
```

### 2. CRUD Operations

- **`db.php`**: Database connection file.
- **`create.php`**:
  - HTML form with fields for title and content.
  - PHP code to handle form submission, validate data, and insert into the `posts` table.
- **`index.php`**:
  - PHP code to fetch all posts from the `posts` table.
  - HTML to display the posts in a list.
- **`edit.php`**:
  - PHP code to fetch a specific post by its ID.
  - HTML form pre-filled with the post's data.
  - PHP code to handle form submission, validate data, and update the post in the `posts` table.
- **`delete.php`**:
  - PHP code to delete a post by its ID.

### 3. User Authentication

- **`register.php`**:
  - HTML form for username and password.
  - PHP code to handle form submission, hash the password using `password_hash()`, and insert the new user into the `users` table.
- **`login.php`**:
  - HTML form for username and password.
  - PHP code to handle form submission, verify credentials using `password_verify()`, and start a session.
- **`logout.php`**:
  - PHP code to destroy the session and log the user out.
- **`auth.php`**:
  - A file to be included in protected pages to check if a user is logged in.

## Task 2: Advanced Features and UI

### 1. Search Functionality

- **`search.php`**:
  - HTML form with a search input.
  - PHP code to handle the search query, search the `posts` table for matching titles or content, and display the results.

### 2. Pagination

- Modify **`index.php`**:
  - Calculate the total number of posts.
  - Determine the number of posts per page.
  - Use `LIMIT` and `OFFSET` in the SQL query to fetch the correct posts for the current page.
  - Generate pagination links.

### 3. User Interface Improvements

- **`style.css`**:
  - Create a CSS file to style the application.
  - Link the stylesheet in all PHP files.
- **Bootstrap Integration (Optional)**:
  - Include the Bootstrap CDN links in the header of the PHP files.
  - Use Bootstrap classes to style forms, tables, buttons, and other elements.

## Task 3: Security and User Roles

### 1. Prepared Statements

- Refactor all database queries to use PDO or MySQLi prepared statements to prevent SQL injection.

### 2. Form Validation

- **Server-Side Validation**:
  - In `create.php`, `edit.php`, and `register.php`, add PHP code to validate all user input (e.g., check for empty fields, validate email format, etc.).
- **Client-Side Validation**:
  - Use HTML5 form validation attributes (e.g., `required`) for immediate feedback to the user.

### 3. User Roles and Permissions

- **Database Schema Update**:
  - Add a `role` column to the `users` table (e.g., `ENUM('admin', 'editor')`).
- **Role-Based Access Control (RBAC)**:
  - After a user logs in, store their role in the session.
  - On pages like `create.php`, `edit.php`, and `delete.php`, check the user's role and grant access only to authorized users (e.g., only admins and editors can create/edit/delete posts).

## Task 4: Integration and Testing

### 1. Integration

- Ensure all features work together seamlessly.
- Create a consistent navigation menu.
- Protect administrative areas.

### 2. Testing and Debugging

- **Functional Testing**:
  - Test all CRUD operations.
  - Test user registration, login, and logout.
  - Test search and pagination.
  - Test role-based access.
- **Usability Testing**:
  - Ensure the application is easy to use and navigate.
- **Security Testing**:
  - Attempt to perform SQL injection.
  - Test form validation with invalid data.

## File Structure

```
/
├── css/
│   └── style.css
├── includes/
│   ├── db.php
│   └── auth.php
├── index.php
├── login.php
├── logout.php
├── register.php
├── create.php
├── edit.php
├── delete.php
├── search.php
└── plan.md
```
