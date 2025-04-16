<?php
session_start();
require_once 'db.php';

// Check if user is already logged in
if(isset($_SESSION['user_id']) && !isset($_GET['logout'])) {
    // Redirect to home page
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

// Handle logout
if(isset($_GET['logout'])) {
    // Destroy session and redirect to login
    session_unset();
    session_destroy();
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$error = '';

// Process login form
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Please enter both username and password";
    } else {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        
        try {
            // Check if user exists
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                
                // Verify password
                if(password_verify($password, $user['password'])) {
                    // Password is correct, set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Redirect to home page
                    echo "<script>window.location.href = 'index.php';</script>";
                    exit;
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "User not found";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WikiClone</title>
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
            max-width: 500px;
            margin: 3rem auto;
            padding: 0 1rem;
        }
        
        .login-form {
            background-color: #fff;
            border: 1px solid #a2a9b1;
            border-radius: 2px;
            padding: 2rem;
        }
        
        .form-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .form-header h1 {
            font-size: 1.8rem;
            font-weight: normal;
            color: #000;
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
        
        .error-message {
            color: #d33;
            margin-bottom: 1rem;
            font-size: 0.9rem;
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
        
        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .form-footer a {
            color: #36c;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
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
                    <li><a href="register.php">Register</a></li>
                    <li><a href="index.php">Back to Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-container">
        <div class="login-form">
            <div class="form-header">
                <h1>Login to WikiClone</h1>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Login</button>
                </div>
                
                <div class="form-footer">
                    Don't have an account? <a href="register.php">Register here</a>
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
        // JavaScript for form validation and page redirection
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();
                
                if(username === '' || password === '') {
                    e.preventDefault();
                    alert('Please enter both username and password');
                }
            });
        });
    </script>
</body>
</html>
