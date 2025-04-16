<?php
session_start();
require_once 'db.php';

// Get featured articles
$featuredArticles = getFeaturedArticles(10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WikiClone - The Free Encyclopedia</title>
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
            padding: 1rem;
        }
        
        .welcome-header {
            border-bottom: 1px solid #a2a9b1;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .welcome-header h1 {
            font-family: 'Linux Libertine', 'Georgia', 'Times', serif;
            font-size: 1.8rem;
            font-weight: normal;
        }
        
        .search-container {
            margin-bottom: 1.5rem;
        }
        
        .search-form {
            display: flex;
            max-width: 600px;
        }
        
        .search-input {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.9rem;
            border: 1px solid #a2a9b1;
            border-radius: 2px 0 0 2px;
        }
        
        .search-button {
            background-color: #36c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            cursor: pointer;
            border-radius: 0 2px 2px 0;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: #2a4b8d;
        }
        
        .featured-articles {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .article-card {
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .article-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .article-title {
            color: #36c;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            text-decoration: none;
        }
        
        .article-title:hover {
            text-decoration: underline;
        }
        
        .article-meta {
            font-size: 0.8rem;
            color: #72777d;
            margin-bottom: 0.5rem;
        }
        
        .article-summary {
            font-size: 0.9rem;
            color: #444;
        }
        
        .category-tag {
            display: inline-block;
            background-color: #eaecf0;
            color: #222;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
            border-radius: 2px;
            margin-top: 0.5rem;
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
            
            .featured-articles {
                grid-template-columns: 1fr;
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
                
                <h3>Categories</h3>
                <ul>
                    <?php foreach(getCategories() as $category): ?>
                        <li><a href="search.php?category=<?php echo urlencode($category); ?>"><?php echo $category; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <main class="content">
            <div class="welcome-header">
                <h1>Welcome to WikiClone</h1>
                <p>The free encyclopedia that anyone can edit.</p>
            </div>

            <div class="search-container">
                <form class="search-form" action="search.php" method="GET">
                    <input type="text" name="q" class="search-input" placeholder="Search WikiClone...">
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>

            <h2>Featured Articles</h2>
            <div class="featured-articles">
                <?php if (empty($featuredArticles)): ?>
                    <p>No articles found. <a href="create.php">Create the first article!</a></p>
                <?php else: ?>
                    <?php foreach ($featuredArticles as $article): ?>
                        <div class="article-card">
                            <a href="article.php?id=<?php echo $article['id']; ?>" class="article-title"><?php echo htmlspecialchars($article['title']); ?></a>
                            <div class="article-meta">
                                By <?php echo htmlspecialchars($article['username']); ?> • 
                                <?php echo formatDate($article['created_at']); ?>
                            </div>
                            <div class="article-summary">
                                <?php echo getSummary($article['content']); ?>
                            </div>
                            <span class="category-tag"><?php echo htmlspecialchars($article['category']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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
        // JavaScript for handling redirections and page interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Get all article links
            const articleLinks = document.querySelectorAll('.article-title');
            
            // Add click event listener to each link
            articleLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // We're using the href attribute for navigation, so no need to prevent default
                    // This is just for demonstration of using JS for redirection
                    // In a real application, you might want to use AJAX or add more functionality here
                });
            });
        });
    </script>
</body>
</html>
