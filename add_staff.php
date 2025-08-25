<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle adding new staff
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_staff'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $age = intval($_POST['age']);
    $role = $conn->real_escape_string($_POST['role']);
    $staff_id = $conn->real_escape_string($_POST['staff_id']);
    $experience = intval($_POST['years_of_experience']);

    try {
        // Check if Staff ID exists
        $checkQuery = "SELECT id FROM staff WHERE staff_id = '$staff_id'";
        if ($conn->query($checkQuery)->num_rows > 0) {
            throw new Exception("Staff ID already exists!");
        }

        // Insert using prepared statement
        $stmt = $conn->prepare("INSERT INTO staff (name, age, role, staff_id, years_of_experience) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $name, $age, $role, $staff_id, $experience);
        
        if ($stmt->execute()) {
            // Redirect to the same page to avoid form resubmission
            header("Location: add_staff.php?success=1");
            exit();
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        $message = "<div class='error-msg'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

if (isset($_GET['success'])) {
    $message = "<div class='success-msg'>✅ Staff member added successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCare - Add Staff</title>
    <style>
        /* Same as before */
    </style>
</head>
<body>
    <div class="container">
        <h2><span class="medical-icon">👩⚕️</span>Add Staff Member</h2>

        <?php if(!empty($message)) echo $message; ?>

        <!-- Add Staff Form -->
        <div class="staff-form">
            <form method="POST">
                <input type="hidden" name="add_staff" value="1">
                
                <div class="form-group">
                    <label>Staff Name</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" min="18" max="70" required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="Doctor">Doctor</option>
                        <option value="Nurse">Nurse</option>
                        <option value="Admin">Admin</option>
                        <option value="Receptionist">Receptionist</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Staff ID (Unique)</label>
                    <input type="text" name="staff_id" pattern="[A-Za-z0-9]{5,}" title="5+ alphanumeric characters" required>
                </div>

                <div class="form-group">
                    <label>Years of Experience</label>
                    <input type="number" name="years_of_experience" min="0" max="50" required>
                </div>

                <button type="submit">Add Staff Member</button>
            </form>
        </div>
    </div>
</body>
</html>
