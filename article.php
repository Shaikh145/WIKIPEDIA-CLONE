<?php
session_start();
require_once 'db.php';

// Get article ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to homepage
if($id === 0) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

// Get article data
$article = getArticleById($id);

// If article not found, show error
if(!$article) {
    $errorMessage = "Article not found";
}

// Get article versions
$versions = getArticleVersions($id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article ? htmlspecialchars($article['title']) : 'Article Not Found'; ?> - WikiClone</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Linux Libertine', 'Georgia', 'Times', serif;
            line-height: 1.6;
            color: #222;
            background-color: #f6f6f6;
            margin: 0;
        }
        
        /* Header and Navigation */
        .header {
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.5rem 1rem;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #000;
            text-decoration: none;
        }
        
        .logo span {
            color: #36c;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 1.5rem;
        }
        
        .nav-links a {
            color: #36c;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #2a4b8d;
            text-decoration: underline;
        }
        
        /* Main Content */
        .main-container {
            display: flex;
            max-width: 1200px;
            margin: 1rem auto;
            padding: 0 1rem;
        }
        
        .sidebar {
            width: 200px;
            padding-right: 1rem;
        }
        
        .sidebar-menu {
            background-color: #f8f9fa;
            border: 1px solid #a2a9b1;
            border-radius: 2px;
            padding: 0.7rem;
            margin-bottom: 1rem;
        }
        
        .sidebar-menu h3 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #54595d;
            font-weight: 500;
        }
        
        .sidebar-menu ul {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
        }
        
        .sidebar-menu a {
            color: #36c;
            text-decoration: none;
        }
        
        .sidebar-menu a:hover {
            text-decoration: underline;
        }
        
        .content {
            flex: 1;
            background-color: #fff;
            border: 1px solid #a2a9b1;
            border-radius: 2px;
            padding: 1.5rem;
        }
        
        .article-header {
            border-bottom: 1px solid #a2a9b1;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .article-title {
            font-family: 'Linux Libertine', 'Georgia', 'Times', serif;
            font-size: 2.2rem;
            font-weight: normal;
            margin-bottom: 0.5rem;
        }
        
        .article-meta {
            color: #72777d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .article-category {
            display: inline-block;
            background-color: #eaecf0;
            color: #222;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
            border-radius: 2px;
        }
        
        .article-content {
            font-size: 0.95rem;
            line-height: 1.7;
        }
        
        .article-content p {
            margin-bottom: 1rem;
        }
        
        .article-content h2 {
            font-size: 1.5rem;
            font-weight: normal;
            color: #000;
            margin: 1.5rem 0 1rem;
            padding-bottom: 0.2rem;
            border-bottom: 1px solid #eaecf0;
        }
        
        .article-content h3 {
            font-size: 1.3rem;
            font-weight: normal;
            color: #000;
            margin: 1.2rem 0 0.8rem;
        }
        
        .article-content ul, .article-content ol {
            margin: 0.5rem 0 1rem 1.5rem;
        }
        
        .article-content a {
            color: #36c;
            text-decoration: none;
        }
        
        .article-content a:hover {
            text-decoration: underline;
        }
        
        .article-actions {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eaecf0;
        }
        
        .btn {
            display: inline-block;
            background-color: #36c;
            color: #fff;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border: none;
            border-radius: 2px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2a4b8d;
        }
        
        .article-versions {
            margin-top: 2rem;
        }
        
        .article-versions h3 {
            font-size: 1.2rem;
            font-weight: normal;
            margin-bottom: 1rem;
        }
        
        .version-list {
            list-style: none;
            border: 1px solid #eaecf0;
            border-radius: 2px;
        }
        
        .version-item {
            padding: 0.7rem;
            border-bottom: 1px solid #eaecf0;
            font-size: 0.9rem;
        }
        
        .version-item:last-child {
            border-bottom: none;
        }
        
        .error-container {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .error-message {
            font-size: 1.2rem;
            color: #d33;
            margin-bottom: 1.5rem;
        }
        
        /* Footer */
        .footer {
            background-color: #f8f9fa;
            border-top: 1px solid #a2a9b1;
            padding: 1rem 0;
            margin-top: 2rem;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            text-align: center;
            font-size: 0.8rem;
            color: #72777d;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            list-style: none;
            margin-bottom: 0.5rem;
        }
        
        .footer-links li {
            margin: 0 0.5rem;
        }
        
        .footer-links a {
            color: #36c;
            text-decoration: none;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                margin-bottom: 1rem;
                padding-right: 0;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">Wiki<span>Clone</span></a>
            <nav>
                <ul class="nav-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="create.php">Create Article</a></li>
                        <li><a href="#"><?php echo $_SESSION['username']; ?></a></li>
                        <li><a href="login.php?logout=1">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-container">
        <aside class="sidebar">
            <div class="sidebar-menu">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="index.php">Main Page</a></li>
                    <li><a href="search.php">Search</a></li>
                    <li><a href="index.php">Recent Changes</a></li>
                    <li><a href="index.php">Random Article</a></li>
                </ul>
            </div>
            
            <div class="sidebar-menu">
                <h3>Categories</h3>
                <ul>
                    <?php foreach(getCategories() as $category): ?>
                        <li><a href="search.php?category=<?php echo urlencode($category); ?>"><?php echo $category; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <?php if($article): ?>
            <div class="sidebar-menu">
                <h3>Article Tools</h3>
                <ul>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="edit.php?id=<?php echo $article['id']; ?>">Edit this page</a></li>
                    <?php endif; ?>
                    <li><a href="#history">View history</a></li>
                </ul>
            </div>
            <?php endif; ?>
        </aside>

        <main class="content">
            <?php if(isset($errorMessage)): ?>
                <div class="error-container">
                    <div class="error-message"><?php echo $errorMessage; ?></div>
                    <a href="index.php" class="btn">Return to Homepage</a>
                </div>
            <?php elseif($article): ?>
                <div class="article-header">
                    <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                    <div class="article-meta">
                        By <?php echo htmlspecialchars($article['username']); ?> • 
                        Last updated: <?php echo formatDate($article['created_at']); ?>
                    </div>
                    <span class="article-category"><?php echo htmlspecialchars($article['category']); ?></span>
                </div>
                
                <div class="article-content">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>
                
                <div class="article-actions">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn">Edit Article</a>
                    <?php else: ?>
                        <a href="login.php" class="btn">Login to Edit</a>
                    <?php endif; ?>
                </div>
                
                <?php if(!empty($versions)): ?>
                <div class="article-versions" id="history">
                    <h3>Article History</h3>
                    <ul class="version-list">
                        <?php foreach($versions as $version): ?>
                            <li class="version-item">
                                Edited by <?php echo htmlspecialchars($version['username']); ?> on 
                                <?php echo formatDate($version['changed_at']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <ul class="footer-links">
                <li><a href="#">About WikiClone</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Use</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
            <p>All content is available under the Creative Commons Attribution-ShareAlike License.</p>
            <p>&copy; <?php echo date('Y'); ?> WikiClone</p>
        </div>
    </footer>

    <script>
        // JavaScript for handling page interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event listeners to internal links for smooth scrolling
            const internalLinks = document.querySelectorAll('a[href^="#"]');
            
            internalLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 70,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
