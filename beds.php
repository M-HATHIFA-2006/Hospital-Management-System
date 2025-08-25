<?php 
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_bed'])) {
    $room_number = $_POST['room_number'];
    $block_number = $_POST['block_number'];

    $checkDuplicate = "SELECT * FROM beds WHERE room_number = '$room_number' AND block_number = '$block_number'";
    $result = $conn->query($checkDuplicate);

    if ($result && $result->num_rows > 0) {
        $message = "<div class='error-msg'>❌ Bed already exists for this block and room!</div>";
    } else {
        $insert = "INSERT INTO beds (room_number, block_number, status) VALUES ('$room_number', '$block_number', 'Available')";
        if ($conn->query($insert)) {
            $message = "<div class='success-msg'>✅ Bed added successfully!</div>";
        } else {
            $message = "<div class='error-msg'>❌ Failed to add bed.</div>";
        }
    }
}

$bedsResult = $conn->query("SELECT * FROM beds ORDER BY block_number, room_number");
?>

<!DOCTYPE html>
<html>
<head>
    <title>MedCare - Bed Management</title>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f0f6fa; margin: 0; padding: 2rem; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.05); }
        h2 { color: #2a5c7d; margin-bottom: 2rem; }
        .bed-form { background: #f9f9f9; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #2a5c7d; font-weight: bold; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; }
        button { background: #2a5c7d; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        button:hover { background: #1a3f5a; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #2a5c7d; color: white; }
        tr:hover { background-color: #f2f2f2; }
        .occupied { color: red; font-weight: bold; }
        .available { color: green; font-weight: bold; }
        .success-msg { background: #dff0d8; color: #3c763d; padding: 15px; border-radius: 8px; margin: 1rem 0; }
        .error-msg { background: #f2dede; color: #a94442; padding: 15px; border-radius: 8px; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🛏️ Bed Management</h2>

        <?php if (!empty($message)) echo $message; ?>

        <div class="bed-form">
            <form method="POST">
                <input type="hidden" name="add_bed" value="1">
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" name="room_number" required>
                </div>

                <div class="form-group">
                    <label>Block Number (1 to 4)</label>
                    <select name="block_number" required>
                        <option value="">Select Block</option>
                        <option value="1">Block 1 (Emergency/ICU/Pregnant)</option>
                        <option value="2">Block 2</option>
                        <option value="3">Block 3</option>
                        <option value="4">Block 4</option>
                    </select>
                </div>

                <button type="submit">Add Bed</button>
            </form>
        </div>

        <h3>All Beds</h3>
        <table>
            <thead>
                <tr>
                    <th>Bed ID</th>
                    <th>Room Number</th>
                    <th>Block Number</th>
                    <th>Status</th>
                    <th>Assigned Patient</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bedsResult && $bedsResult->num_rows > 0): ?>
                    <?php while($bed = $bedsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($bed['id']) ?></td>
                            <td><?= htmlspecialchars($bed['room_number']) ?></td>
                            <td><?= htmlspecialchars($bed['block_number']) ?></td>
                            <td class="<?= strtolower($bed['status']) ?>">
                                <?= $bed['status'] ?>
                            </td>
                            <td>
                                <?= $bed['patient_id'] ? 'Patient ID: ' . $bed['patient_id'] : '-' ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No beds available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
