<?php
    require 'includes/auth.php';

    $limit = 5; 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $total_stmt = $conn->query("SELECT COUNT(*) FROM posts");
    $total_posts = $total_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $limit);

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

            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </body>
</html>