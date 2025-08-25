<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare Dashboard</title>
    <style>
        :root {
            --main-blue: #2a5c7d;
            --hover-blue: #1a4560;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #e8f4f8;
            min-height: 100vh;
            margin: 0;
            padding: 2rem;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .hospital-header {
            text-align: center;
            color: var(--main-blue);
            margin-bottom: 3rem;
        }

        .medical-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .welcome-message {
            text-align: center;
            color: var(--main-blue);
            margin-bottom: 3rem;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1rem;
        }

        .dashboard-btn {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            color: var(--main-blue);
            box-shadow: 0 2px 15px rgba(42,92,125,0.1);
            transition: transform 0.3s ease;
            border: 2px solid var(--main-blue);
        }

        .dashboard-btn:hover {
            transform: translateY(-5px);
            background: var(--main-blue);
            color: white;
        }

        .dashboard-btn-icon {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 1rem;
        }

        .logout-btn {
            background: #ff4444;
            color: white;
            border: none;
            margin-top: 3rem;
        }

        .logout-btn:hover {
            background: #cc0000;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="hospital-header">
            <div class="medical-icon">🏥</div>
            <h1>MedCare Hospital Management System</h1>
        </div>

        <div class="welcome-message">
            <h2>Welcome back, <?php echo $user_name; ?>!</h2>
            <p>Logged in as <strong><?php echo $user_role; ?></strong></p>
        </div>

        <div class="grid-container">
            <?php if ($user_role == 'Admin' || $user_role == 'Receptionist'): ?>
                <a href="patients.php" class="dashboard-btn">
                    <span class="dashboard-btn-icon">📁</span>
                    Manage Patients
                </a>
                <a href="manage_staff.php" class="dashboard-btn">
                    <span class="dashboard-btn-icon">👩⚕️</span>
                    Manage Staff
                </a>
            <?php endif; ?>

            <?php if ($user_role == 'Admin' || $user_role == 'Doctor' || $user_role == 'Nurse'): ?>
                <a href="bed_management.php" class="dashboard-btn">
                    <span class="dashboard-btn-icon">🛏️</span>
                    Bed & Room Management
                </a>
            <?php endif; ?>

            <?php if ($user_role == 'Admin' || $user_role == 'Pharmacist'): ?>
                <a href="medicine_inventory.php" class="dashboard-btn">
                    <span class="dashboard-btn-icon">💊</span>
                    Medicine Inventory
                </a>
            <?php endif; ?>

            <a href="logout.php" class="dashboard-btn logout-btn">
                <span class="dashboard-btn-icon">🚪</span>
                Logout
            </a>
        </div>
    </div>
</body>
</html>