# Implementation Guide: PHP CRUD Application

This guide provides a complete, step-by-step process for building the PHP CRUD application outlined in `plan.md`. It is designed for you to create the files and write the code yourself.

## Part 1: Server Setup (XAMPP on Kali Linux)

*(This is the setup you have already completed.)*

1.  **Download XAMPP for Linux** from [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html).
2.  Open a terminal and make the installer executable:
    ```bash
    cd Downloads
    chmod +x xampp-linux-x64-*.run
    ```
3.  Run the installer with `sudo`:
    ```bash
    sudo ./xampp-linux-x64-*.run
    ```
4.  Follow the setup wizard.
5.  Start the Apache and MySQL services from the XAMPP Control Panel.
    ```bash
    sudo /opt/lampp/manager-linux-x64.run
    ```
6.  Verify the installation by visiting `http://localhost` and `http://localhost/phpmyadmin`.
7.  Using phpMyAdmin, create the `blog` database and the `posts` and `users` tables using the SQL schema from `plan.md`.

## Part 2: Project Structure

Before you start coding, you need to set up the correct folder structure for your project.

1.  Navigate to the XAMPP webroot directory in your terminal. This is where all your web projects live.
    ```bash
    cd /opt/lampp/htdocs/
    ```
2.  Create a new directory for your project. Let's call it `apex-blog`.
    ```bash
    sudo mkdir apex-blog
    ```
3.  Navigate into your new project directory.
    ```bash
    cd apex-blog
    ```
4.  Create the `css` and `includes` subdirectories.
    ```bash
    sudo mkdir css includes
    ```
5.  You will need to set the correct permissions so you can create and edit files without using `sudo` every time. Change the ownership of the project folder to your user.
    ```bash
    sudo chown -R $USER:$USER .
    ```

Now, your project directory is `/opt/lampp/htdocs/apex-blog`, and you can access it at `http://localhost/apex-blog/`. All file paths in this guide will be relative to this directory.

---

## Part 3: Task 1 - Basic CRUD and Authentication

### 1. Database Connection (`includes/db.php`)

**Purpose:** This file will handle the connection to your MySQL database. Including it in other files will allow you to reuse the same connection code.

**Instructions:** Create a new file named `db.php` inside the `includes` folder.

**Path:** `includes/db.php`

**Code:**
```php
<?php
// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

$servername = "localhost";
$username = "root";
// Your XAMPP MySQL password might be empty by default
$password = ""; 
$dbname = "blog";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    // Stop the script if the connection fails
    die(); 
}
?>
```

### 2. User Registration (`register.php`)

**Purpose:** Provides a form for new users to register. It hashes the password for security and saves the user to the database.

**Instructions:** Create a new file named `register.php` in the root of your project directory (`apex-blog`).

**Path:** `register.php`

**Code:**
```php
<?php
require 'includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $username = $_POST['username'];
        // Hash the password for security
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        try {
            $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);

            if ($stmt->execute()) {
                $message = 'Successfully created new user';
            } else {
                $message = 'Sorry, there must have been an issue creating your account';
            }
        } catch (PDOException $e) {
            // Check for duplicate entry
            if ($e->errorInfo[1] == 1062) {
                $message = 'That username is already taken.';
            } else {
                $message = 'An error occurred: ' . $e->getMessage();
            }
        }
    } else {
        $message = 'Please fill out all fields.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if(!empty($message)): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <input name="username" type="text" placeholder="Enter your username" required>
            <input name="password" type="password" placeholder="and password" required>
            <input type="submit" value="Submit">
        </form>
        <p>Already a member? <a href="login.php">Log in here</a>.</p>
    </div>
</body>
</html>
```

### 3. User Login (`login.php`)

**Purpose:** Provides a form for users to log in. It verifies their credentials and starts a session to keep them logged in.

**Instructions:** Create a new file named `login.php` in your project root.

**Path:** `login.php`

**Code:**
```php
<?php
require 'includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $sql = "SELECT id, username, password FROM users WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $_POST['username']);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            // Redirect to the main page after successful login
            header("Location: index.php");
            exit;
        } else {
            $message = 'Sorry, those credentials do not match.';
        }
    } else {
        $message = 'Please enter username and password.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if(!empty($message)): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input name="username" type="text" placeholder="Enter your username" required>
            <input name="password" type="password" placeholder="and password" required>
            <input type="submit" value="Login">
        </form>
        <p>Not a member? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
```

### 4. Authentication Check (`includes/auth.php`)

**Purpose:** This script checks if a user is logged in. You will include this at the top of pages that should be protected.

**Instructions:** Create a new file named `auth.php` inside the `includes` folder.

**Path:** `includes/auth.php`

**Code:**
```php
<?php
// This file is included in protected pages.
// It checks if the user is logged in.

// We need to access the session, so we require the db.php file which starts it.
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page.
    header("Location: login.php");
    exit;
}
?>
```

### 5. Logout (`logout.php`)

**Purpose:** Destroys the user's session, effectively logging them out.

**Instructions:** Create a new file named `logout.php` in your project root.

**Path:** `logout.php`

**Code:**
```php
<?php
require 'includes/db.php';

// Unset all of the session variables.
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: login.php");
exit;
?>
```

### 6. Create Post (`create.php`)

**Purpose:** A form to create a new blog post. Only logged-in users should access this.

**Instructions:** Create a new file named `create.php` in your project root.

**Path:** `create.php`

**Code:**
```php
<?php
require 'includes/auth.php'; // Protect this page

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['title']) && !empty($_POST['content'])) {
        $sql = "INSERT INTO posts (title, content) VALUES (:title, :content)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $_POST['title']);
        $stmt->bindParam(':content', $_POST['content']);

        if ($stmt->execute()) {
            header("Location: index.php"); // Redirect after successful creation
            exit;
        } else {
            $message = 'Error creating post.';
        }
    } else {
        $message = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Post</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">Back to Posts</a>
        <h1>Create New Post</h1>
        <?php if(!empty($message)): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>
        <form action="create.php" method="POST">
            <input type="text" name="title" placeholder="Post Title" required>
            <textarea name="content" placeholder="Post Content" required></textarea>
            <input type="submit" value="Create Post">
        </form>
    </div>
</body>
</html>
```

### 7. List Posts (`index.php`)

**Purpose:** The main page of the blog. It fetches and displays all posts from the database.

**Instructions:** Create a new file named `index.php` in your project root.

**Path:** `index.php`

**Code:**1

```php
<?php
require 'includes/auth.php'; // Protect this page

// Fetch all posts
$stmt = $conn->prepare("SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Blog</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Blog Posts</h1>
            <div class="user-actions">
                <a href="create.php" class="btn">New Post</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>

        <div class="posts-list">
            <?php if (empty($posts)): ?>
                <p>No posts yet. <a href="create.php">Create one!</a></p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-item">
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</p>
                        <small>Posted on <?= date('F j, Y', strtotime($post['created_at'])) ?></small>
                        <div class="post-actions">
                            <a href="edit.php?id=<?= $post['id'] ?>">Edit</a>
                            <a href="delete.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
```

### 8. Edit Post (`edit.php`)

**Purpose:** Fetches a specific post and displays its data in a form, allowing the user to update it.

**Instructions:** Create a new file named `edit.php` in your project root.

**Path:** `edit.php`

**Code:**
```php
<?php
require 'includes/auth.php';

$message = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch the post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "Post not found.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['title']) && !empty($_POST['content'])) {
        $sql = "UPDATE posts SET title = :title, content = :content WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $_POST['title']);
        $stmt->bindParam(':content', $_POST['content']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $message = 'Error updating post.';
        }
    } else {
        $message = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">Back to Posts</a>
        <h1>Edit Post</h1>
        <?php if(!empty($message)): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>
        <form action="edit.php?id=<?= $id ?>" method="POST">
            <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
            <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
            <input type="submit" value="Update Post">
        </form>
    </div>
</body>
</html>
```

### 9. Delete Post (`delete.php`)

**Purpose:** Deletes a post from the database based on its ID.

**Instructions:** Create a new file named `delete.php` in your project root.

**Path:** `delete.php`

**Code:**
```php
<?php
require 'includes/auth.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $sql = "DELETE FROM posts WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        die();
    }
}

header("Location: index.php");
exit;
?>
```

---

## Part 4: Task 2 - Advanced Features and UI

### 1. Basic Styling (`css/style.css`)

**Purpose:** To add some basic styling to make the application look clean and modern.

**Instructions:** Create a new file named `style.css` inside the `css` folder.

**Path:** `css/style.css`

**Code:**
```css
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    background-color: #f4f7f6;
    color: #333;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

h1, h2 {
    color: #2c3e50;
}

a {
    color: #3498db;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ecf0f1;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.user-actions .btn {
    margin-left: 10px;
}

.btn {
    background-color: #3498db;
    color: #fff;
    padding: 10px 15px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-size: 16px;
}
.btn:hover {
    background-color: #2980b9;
    text-decoration: none;
}
.btn-secondary {
    background-color: #e74c3c;
}
.btn-secondary:hover {
    background-color: #c0392b;
}

form {
    display: flex;
    flex-direction: column;
}

input[type="text"],
input[type="password"],
textarea {
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

textarea {
    min-height: 150px;
    resize: vertical;
}

input[type="submit"] {
    background-color: #2ecc71;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    align-self: flex-start;
}
input[type="submit"]:hover {
    background-color: #27ae60;
}

.message {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    background-color: #e74c3c;
    color: white;
}

.posts-list .post-item {
    border-bottom: 1px solid #ecf0f1;
    padding: 20px 0;
}
.posts-list .post-item:last-child {
    border-bottom: none;
}

.post-item h2 {
    margin-top: 0;
}

.post-item small {
    color: #95a5a6;
    display: block;
    margin-top: 10px;
}

.post-actions {
    margin-top: 15px;
}
.post-actions a {
    margin-right: 15px;
}

/* Pagination and Search */
.pagination {
    margin-top: 30px;
}
.pagination a {
    margin: 0 5px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.pagination a.active {
    background-color: #3498db;
    color: #fff;
    border-color: #3498db;
}

.search-form {
    margin-bottom: 20px;
}
```

### 2. Search Functionality (`search.php`)

**Purpose:** Allows users to search for posts by title or content.

**Instructions:** Create a new file named `search.php` in your project root.

**Path:** `search.php`

**Code:**
```php
<?php
require 'includes/auth.php';

$search_term = $_GET['query'] ?? '';
$posts = [];

if (!empty($search_term)) {
    $sql = "SELECT * FROM posts WHERE title LIKE :search OR content LIKE :search ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':search', '%' . $search_term . '%');
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">Back to Posts</a>
        <h1>Search Results for "<?= htmlspecialchars($search_term) ?>"</h1>

        <div class="posts-list">
            <?php if (empty($posts)): ?>
                <p>No posts found matching your search.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-item">
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</p>
                        <small>Posted on <?= date('F j, Y', strtotime($post['created_at'])) ?></small>
                        <div class="post-actions">
                            <a href="edit.php?id=<?= $post['id'] ?>">Edit</a>
                            <a href="delete.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
```

### 3. Update `index.php` for Search and Pagination

**Purpose:** Add a search bar and pagination links to the main blog page.

**Instructions:** **Replace** the entire content of your existing `index.php` with the following code.

**Path:** `index.php`

**Code:**
```php
<?php
require 'includes/auth.php';

// Pagination logic
$limit = 5; // Number of posts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of posts
$total_stmt = $conn->query("SELECT COUNT(*) FROM posts");
$total_posts = $total_stmt->fetchColumn();
$total_pages = ceil($total_posts / $limit);

// Fetch posts for the current page
$stmt = $conn->prepare("SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Blog</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Blog Posts</h1>
            <div class="user-actions">
                <a href="create.php" class="btn">New Post</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>

        <!-- Search Form -->
        <form action="search.php" method="GET" class="search-form">
            <input type="text" name="query" placeholder="Search for posts...">
            <input type="submit" value="Search">
        </form>

        <div class="posts-list">
            <?php if (empty($posts)): ?>
                <p>No posts yet. <a href="create.php">Create one!</a></p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-item">
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</p>
                        <small>Posted on <?= date('F j, Y', strtotime($post['created_at'])) ?></small>
                        <div class="post-actions">
                            <a href="edit.php?id=<?= $post['id'] ?>">Edit</a>
                            <a href="delete.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination Links -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>
```

---

## Part 5: Task 3 - Security and User Roles

The code provided in Part 3 and 4 already includes key security features like **prepared statements** (using PDO `prepare` and `bindParam`) to prevent SQL injection and `htmlspecialchars()` to prevent XSS attacks. Now, let's add server-side validation and user roles.

### 1. Add Server-Side Validation

**Purpose:** To ensure that data submitted via forms is valid and complete before it's processed.

**Instructions:** Update the PHP code block at the top of `register.php`, `create.php`, and `edit.php` to include more robust checks.

**Example Update for `create.php`:**
```php
// ... (at the top of create.php)
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $message = 'Please fill in all fields.';
    } elseif (strlen($title) > 255) {
        $message = 'Title cannot be longer than 255 characters.';
    } else {
        $sql = "INSERT INTO posts (title, content) VALUES (:title, :content)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $message = 'Error creating post.';
        }
    }
}
// ... (rest of the file)
```
*Apply similar logic to `register.php` and `edit.php`.*

### 2. Implement User Roles

**Purpose:** To restrict certain actions (like creating or deleting posts) to specific user roles (e.g., 'admin').

**Step A: Update Database Schema**

1.  Go to `http://localhost/phpmyadmin`.
2.  Select your `blog` database.
3.  Select the `users` table and go to the "Structure" tab.
4.  Click "Add" 1 column "After `password`" and click "Go".
5.  Name the column `role`, set the type to `ENUM`, and enter the values `'admin', 'editor'` in the length/values box. Set the default to `'editor'`.
6.  Save the changes.

**Step B: Update Registration to Assign a Role**

In `register.php`, you can assign a default role. For simplicity, we'll make the first user an admin and everyone else an editor.

**Update `register.php`:**
```php
// ... inside the try block in register.php
$sql_count = "SELECT COUNT(*) FROM users";
$count_stmt = $conn->query($sql_count);
$user_count = $count_stmt->fetchColumn();

// First user is admin, others are editors
$role = ($user_count == 0) ? 'admin' : 'editor';

$sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $password);
$stmt->bindParam(':role', $role);

if ($stmt->execute()) {
// ...
```

**Step C: Store Role in Session**

In `login.php`, fetch the user's role and store it in the session.

**Update `login.php`:**
```php
// ... inside the if ($user && password_verify(...)) block
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role']; // Add this line
header("Location: index.php");
// ...
```
*You'll need to add `role` to the SELECT query: `SELECT id, username, password, role FROM users...`*

**Step D: Protect Actions Based on Role**

Now, in files like `create.php`, `edit.php`, and `delete.php`, you can check the user's role.

**Example for `delete.php`:**
```php
<?php
require 'includes/auth.php';

// Role check: Only admins can delete
if ($_SESSION['role'] !== 'admin') {
    // You can redirect or show an error
    echo "Access Denied. You do not have permission to perform this action.";
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    // ... (rest of the delete logic)
}

header("Location: index.php");
exit;
?>
```
*Apply this role-checking logic to `create.php` and `edit.php` as well. You might allow both 'admin' and 'editor' to create/edit, but only 'admin' to delete.*

---

## Part 6: Task 4 - Integration and Testing

**Purpose:** To ensure all parts of your application work together correctly and are free of bugs.

**Instructions:** Follow this checklist to test your application thoroughly.

### Functional Testing Checklist
-   [ ] **Registration:** Can you create a new user? Does it prevent duplicate usernames?
-   [ ] **Login/Logout:** Can you log in with a registered user? Does `logout.php` successfully end the session?
-   [ ] **Authentication:** Can you access `index.php` only when logged in? Are you redirected to `login.php` if you try to access it directly?
-   [ ] **Create Post:** Can you create a new post? Does it appear on the `index.php` page?
-   [ ] **Edit Post:** Can you edit an existing post? Are the changes saved correctly?
-   [ ] **Delete Post:** Can you delete a post? Does the confirmation prompt appear?
-   [ ] **Search:** Does the search form on `index.php` correctly filter posts?
-   [ ] **Pagination:** Do the pagination links on `index.php` work correctly?
-   [ ] **Role Permissions:** If you are logged in as an 'editor', are you blocked from deleting posts?

### Usability Testing
-   Is the navigation intuitive?
-   Are forms easy to understand and use?
-   Are messages (success, error) clear and helpful?

### Security Testing
-   Try entering text with special characters like `<script>alert('test')</script>` into the post title and content. Does the site execute the script (bad) or display the text safely (good)?
-   Check that you cannot access pages like `create.php` or `edit.php` by typing the URL directly into the browser without being logged in.

This comprehensive guide should provide you with everything you need to build and test your PHP application. Good luck!