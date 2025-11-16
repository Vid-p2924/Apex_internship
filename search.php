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