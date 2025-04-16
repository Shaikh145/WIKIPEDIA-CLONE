<?php
session_start();
require_once 'db.php';

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    // Redirect to home page
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

$error = '';
$success = '';

// Process registration form
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
        $error = "All fields are required";
    } else {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        }
        // Check password length
        elseif(strlen($password) < 6) {
            $error = "Password must be at least 6 characters long";
        }
        // Check if passwords match
        elseif($password !== $confirmPassword) {
            $error = "Passwords do not match";
        } else {
            try {
                // Check if username already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                
                if($stmt->rowCount() > 0) {
                    $error = "Username already exists";
                } else {
                    // Check if email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                    
                    if($stmt->rowCount() > 0) {
                        $error = "Email already exists";
                    } else {
                        // Hash password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new user
                        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':password', $hashedPassword);
                        $stmt->execute();
                        
                        $success = "Registration successful! You can now login.";
                    }
                }
            } catch(PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WikiClone</title>
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
        
        .register-form {
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
        
        .success-message {
            color: #14866d;
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
                    <li><a href="login.php">Login</a></li>
                    <li><a href="index.php">Back to Home</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main-container">
        <div class="register-form">
            <div class="form-header">
                <h1>Create a WikiClone Account</h1>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Register</button>
                </div>
                
                <div class="form-footer">
                    Already have an account? <a href="login.php">Login here</a>
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
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value.trim();
                const confirmPassword = document.getElementById('confirm_password').value.trim();
                
                // Basic validation
                if(username === '' || email === '' || password === '' || confirmPassword === '') {
                    e.preventDefault();
                    alert('Please fill in all fields');
                    return;
                }
                
                // Email validation
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!emailPattern.test(email)) {
                    e.preventDefault();
                    alert('Please enter a valid email address');
                    return;
                }
                
                // Password length check
                if(password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long');
                    return;
                }
                
                // Password match check
                if(password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match');
                    return;
                }
            });
            
            // Auto redirect to login page after successful registration
            <?php if(!empty($success)): ?>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
