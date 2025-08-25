<?php
session_start();
include 'db_connect.php';

$error = ""; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_role'] = $row['role'];

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "❌ User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MedCare Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #e8f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .hospital-header {
            text-align: center;
            color: #2a5c7d;
            margin-bottom: 2rem;
        }

        .form-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(42,92,125,0.1);
            width: 350px;
        }

        .medical-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2a5c7d;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2a5c7d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 1rem;
        }

        button:hover {
            background: #1a4560;
        }

        .error-msg {
            color: #ff4444;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="hospital-header">
            <div class="medical-icon">➕</div>
            <h2>MedCare Patient Portal</h2>
        </div>

        <?php if(!empty($error)): ?>
            <div class='error-msg'><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Access My Account</button>
        </form>

        <p style="text-align: center; margin-top: 1rem;">
            New user? <a href="register.php" style="color: #2a5c7d;">Create Account</a>
        </p>
    </div>
</body>
</html>