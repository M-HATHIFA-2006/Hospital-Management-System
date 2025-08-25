<?php
include 'db_connect.php';

$message = ""; // For both success and error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
    if ($conn->query($sql)) {
        $message = "✅ User registered successfully!";
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MedCare Registration</title>
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

        input, select {
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

        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            text-align: center;
        }

        .success {
            background: #dff0d8;
            color: #3c763d;
        }

        .error {
            background: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="hospital-header">
            <div class="medical-icon">🏥</div>
            <h2>Staff Registration</h2>
        </div>

        <?php if(!empty($message)): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Hospital Email" required>
            <input type="password" name="password" placeholder="Create Password" required>
            <select name="role">
                <option value="Admin">Hospital Admin</option>
                <option value="Doctor">Medical Doctor</option>
                <option value="Nurse">Nursing Staff</option>
                <option value="Receptionist">Front Desk</option>
            </select>
            <button type="submit">Register Now</button>
        </form>

        <p style="text-align: center; margin-top: 1rem;">
            Existing user? <a href="login.php" style="color: #2a5c7d;">Login Here</a>
        </p>
    </div>
</body>
</html>