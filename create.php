<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$error = '';
$success = '';

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($_POST['title']) || empty($_POST['content']) || empty($_POST['category'])) {
        $error = "All fields are required";
    } else {
        $title = sanitizeInput($_POST['title']);
        $content = $_POST['content']; // We'll sanitize when displaying
        $category = sanitizeInput($_POST['category']);
        $userId = $_SESSION['user_id'];
        
        // Create article
        $articleId = createArticle($title, $content, $category, $userId);
        
        if($articleId) {
            $success = "Article created successfully!";
            // Redirect to the new article after a brief delay
            header("Refresh: 2; URL=article.php?id=$articleId");
        } else {
            $error = "Error creating article";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Article - WikiClone</title>
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .create-form {
            background-color: #fff;
            border: 1px solid #a2a9b1;
            border-radius: 2px;
            padding: 1.5rem;
        }
        
        .form-header {
            margin-bottom: 1.5rem;
        }
        
        .form-header h1 {
            font-size: 1.8rem;
            font-weight: normal;
            color: #000;
        }
        
        .error-message {
            color: #d33;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            padding: 0.5rem;
            background-color: #fee7e6;
            border: 1px solid #f8d7da;
            border-radius: 2px;
        }
        
        .success-message {
            color: #14866d;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            padding: 0.5rem;
            background-color: #e6fffa;
            border: 1px solid #b2f5ea;
            border-radius: 2px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.7rem;
            font-size: 1rem;
            border: 1px solid #a2a9b1;
            border-radius: 2px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #36c;
            box-shadow: 0 0 0 2px rgba(51,102,204,0.2);
        }
        
        textarea.form-control {
            min-height: 300px;
            font-family: 'Courier New', monospace;
            line-height: 1.5;
        }
        
        select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23333' viewBox='0 0 12 12'%3E%3Cpath d='M3 5l3 3 3-3'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.7rem center;
            background-size: 0.65rem;
            padding-right: 2rem;
        }
        
        .btn {
            display: inline-block;
            background-color: #36c;
            color: #fff;
            font-size: 1rem;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 2px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2a4b8d;
        }
        
        .btn-secondary {
            background-color: #f8f9fa;
            color: #222;
            border: 1px solid #a2a9b1;
            margin-right: 0.5rem;
        }
        
        .btn-secondary:hover {
            background-color: #eaecf0;
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-help {
            margin-bottom: 1.5rem;
            padding: 0.7rem;
            background-color: #f8f9fa;
            border: 1px solid #eaecf0;
            border-radius: 2px;
            font-size: 0.9rem;
        }
        
        .form-help h3 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .form-help ul {
            margin-left: 1.5rem;
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
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">Wiki<span>Clone</span></a>
            <nav>
                <ul class="nav-links">
                    <li><a href="#"><?php echo $_SESSION['username']; ?></a></li>
                    <li><a href="login.php?logout=1">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-container">
        <div class="create-form">
            <div class="form-header">
                <h1>Create New Article</h1>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="form-help">
                <h3>Article Guidelines</h3>
                <ul>
                    <li>Choose a clear and descriptive title</li>
                    <li>Write in a neutral, encyclopedic tone</li>
                    <li>Organize content into logical sections</li>
                    <li>Include reliable sources when possible</li>
                    <li>Select the most appropriate category for your article</li>
                </ul>
            </div>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="title">Article Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach(getCategories() as $category): ?>
                            <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" class="form-control" required></textarea>
                </div>
                
                <div class="form-footer">
                    <div>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn">Create Article</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> WikiClone</p>
        </div>
    </footer>

    <script>
        // JavaScript for form handling and validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(e) {
                const title = document.getElementById('title').value.trim();
                const content = document.getElementById('content').value.trim();
                const category = document.getElementById('category').value;
                
                if(title === '' || content === '' || category === '') {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
            
            // Warn user before leaving with unsaved changes
            let formChanged = false;
            
            const inputs = document.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    formChanged = true;
                });
            });
            
            window.addEventListener('beforeunload', function(e) {
                if (formChanged && !document.querySelector('.success-message')) {
                    // Cancel the event
                    e.preventDefault();
                    // Chrome requires returnValue to be set
                    e.returnValue = '';
                    // Message to display (though most browsers now show their own generic message)
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
        });
    </script>
</body>
</html>
