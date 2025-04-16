<?php
// Database connection settings
$host = "localhost";
$dbname = "dbi78acfkyf4eg";
$username = "uklz9ew3hrop3";
$password = "zyrbspyjlzjb";

// Create connection with error handling
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Function to get username by ID
function getUsernameById($userId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['username'] : 'Unknown';
    } catch(PDOException $e) {
        return 'Unknown';
    }
}

// Function to get featured articles
function getFeaturedArticles($limit = 5) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT a.id, a.title, a.content, a.created_at, a.category, u.username 
            FROM articles a
            JOIN users u ON a.created_by = u.id
            ORDER BY a.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Function to search articles
function searchArticles($query) {
    global $conn;
    try {
        $searchTerm = "%$query%";
        $stmt = $conn->prepare("
            SELECT a.id, a.title, a.content, a.created_at, a.category, u.username 
            FROM articles a
            JOIN users u ON a.created_by = u.id
            WHERE a.title LIKE :query OR a.content LIKE :query OR a.category LIKE :query
            ORDER BY a.created_at DESC
        ");
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Function to get article by ID
function getArticleById($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT a.id, a.title, a.content, a.created_at, a.category, a.created_by, u.username 
            FROM articles a
            JOIN users u ON a.created_by = u.id
            WHERE a.id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

// Function to get article versions
function getArticleVersions($articleId) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT v.id, v.content, v.changed_at, u.username 
            FROM article_versions v
            JOIN users u ON v.changed_by = u.id
            WHERE v.article_id = :article_id
            ORDER BY v.changed_at DESC
        ");
        $stmt->bindParam(':article_id', $articleId);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Function to create new article
function createArticle($title, $content, $category, $userId) {
    global $conn;
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("
            INSERT INTO articles (title, content, category, created_by, created_at)
            VALUES (:title, :content, :category, :created_by, NOW())
        ");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':created_by', $userId);
        $stmt->execute();
        
        $articleId = $conn->lastInsertId();
        
        // Create first version
        $stmt = $conn->prepare("
            INSERT INTO article_versions (article_id, content, changed_by, changed_at)
            VALUES (:article_id, :content, :changed_by, NOW())
        ");
        $stmt->bindParam(':article_id', $articleId);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':changed_by', $userId);
        $stmt->execute();
        
        $conn->commit();
        return $articleId;
    } catch(PDOException $e) {
        $conn->rollBack();
        return false;
    }
}

// Function to update an article
function updateArticle($id, $title, $content, $category, $userId) {
    global $conn;
    try {
        $conn->beginTransaction();
        
        // Update article
        $stmt = $conn->prepare("
            UPDATE articles 
            SET title = :title, content = :content, category = :category
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        
        // Add new version
        $stmt = $conn->prepare("
            INSERT INTO article_versions (article_id, content, changed_by, changed_at)
            VALUES (:article_id, :content, :changed_by, NOW())
        ");
        $stmt->bindParam(':article_id', $id);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':changed_by', $userId);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch(PDOException $e) {
        $conn->rollBack();
        return false;
    }
}

// Get categories
function getCategories() {
    return [
        'Science', 'Technology', 'History', 'Geography',
        'Arts', 'Literature', 'Mathematics', 'Philosophy',
        'Religion', 'Sports', 'Entertainment', 'Politics',
        'Biology', 'Physics', 'Chemistry', 'Astronomy'
    ];
}

// Format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y, g:i a');
}

// Get summary of content (first 200 characters)
function getSummary($content, $length = 200) {
    $content = strip_tags($content);
    if (strlen($content) > $length) {
        return substr($content, 0, $length) . '...';
    }
    return $content;
}
?>
