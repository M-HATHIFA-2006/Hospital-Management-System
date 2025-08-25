<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$staffResult = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_staff'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $age = intval($_POST['age']);
    $role = $conn->real_escape_string($_POST['role']);
    $staff_id = $conn->real_escape_string($_POST['staff_id']);
    $experience = intval($_POST['years_of_experience']);
    $specialization = $role === "Doctor" ? $conn->real_escape_string($_POST['specialization']) : null;

    try {
        $checkQuery = "SELECT id FROM staff WHERE staff_id = '$staff_id'";
        if ($conn->query($checkQuery)->num_rows > 0) {
            throw new Exception("Staff ID already exists!");
        }

        $stmt = $conn->prepare("INSERT INTO staff (name, age, role, staff_id, years_of_experience, specialization) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissis", $name, $age, $role, $staff_id, $experience, $specialization);
        
        if ($stmt->execute()) {
            $message = "<div class='success-msg'>✅ Staff member added successfully!</div>";
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        $message = "<div class='error-msg'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $deleteQuery = "DELETE FROM staff WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $message = "<div class='success-msg'>✅ Staff member deleted successfully!</div>";
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        $message = "<div class='error-msg'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

$staffResult = $conn->query("SELECT * FROM staff ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef6f8;
            padding: 2rem;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        h2 { color: #2a5c7d; }
        .form-group { margin-bottom: 1.5rem; }
        label { font-weight: bold; }
        input, select {
            width: 100%; padding: 10px;
            border: 1px solid #ccc; border-radius: 6px;
        }
        button {
            background: #2a5c7d; color: #fff;
            padding: 10px 20px;
            border: none; border-radius: 6px;
            cursor: pointer;
        }
        button:hover { background: #1e3f5e; }
        table {
            width: 100%; border-collapse: collapse;
            margin-top: 2rem;
        }
        th, td {
            border: 1px solid #ccc; padding: 10px;
            text-align: left;
        }
        th {
            background: #2a5c7d; color: white;
        }
        .delete-btn {
            background: #e74c3c; color: white;
            padding: 6px 12px; border-radius: 5px;
            text-decoration: none;
        }
        .success-msg, .error-msg {
            padding: 10px; border-radius: 6px;
            margin: 10px 0;
        }
        .success-msg { background: #d4edda; color: #155724; }
        .error-msg { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>👩‍⚕️ Staff Management</h2>
        <?php if(!empty($message)) echo $message; ?>

        <form method="POST">
            <input type="hidden" name="add_staff" value="1">

            <div class="form-group">
                <label>Staff Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Age</label>
                <input type="number" name="age" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" id="role-select" required onchange="toggleSpecialization(this.value)">
                    <option value="">--Select Role--</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Nurse">Nurse</option>
                    <option value="Admin">Admin</option>
                    <option value="Receptionist">Receptionist</option>
                </select>
            </div>

            <div class="form-group" id="specialization-group" style="display:none;">
                <label>Specialization</label>
                <input type="text" name="specialization" placeholder="e.g. Cardiologist, Dermatologist">
            </div>

            <div class="form-group">
                <label>Staff ID (Unique)</label>
                <input type="text" name="staff_id" required>
            </div>

            <div class="form-group">
                <label>Years of Experience</label>
                <input type="number" name="years_of_experience" required>
            </div>

            <button type="submit">Add Staff</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Age</th><th>Role</th>
                    <th>Staff ID</th><th>Experience</th><th>Specialization</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($staffResult && $staffResult->num_rows > 0): ?>
                    <?php while ($row = $staffResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['age'] ?></td>
                            <td><?= $row['role'] ?></td>
                            <td><?= $row['staff_id'] ?></td>
                            <td><?= $row['years_of_experience'] ?> years</td>
                            <td><?= $row['specialization'] ?? '—' ?></td>
                            <td>
                                <a class="delete-btn" href="manage_staff.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Delete this staff?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No staff records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleSpecialization(role) {
            const specGroup = document.getElementById('specialization-group');
            specGroup.style.display = (role === 'Doctor') ? 'block' : 'none';
        }
    </script>
</body>
</html>
