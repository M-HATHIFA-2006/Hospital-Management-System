<?php
session_start();
include 'db_connect.php';

// Auto-delete expired medicines
$conn->query("DELETE FROM medicines WHERE expiry_date < CURDATE()");

$message = '';
$medicines = null;

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new medicine
    if (isset($_POST['add_medicine'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $expiry_date = $_POST['expiry_date'];
        $supplier = $_POST['supplier'];
        $batch = $_POST['batch_number'];

        try {
            $stmt = $conn->prepare("INSERT INTO medicines 
                (name, description, quantity, price, expiry_date, supplier, batch_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssidsss", $name, $description, $quantity, $price, $expiry_date, $supplier, $batch);
            
            if ($stmt->execute()) {
                $message = "<div class='success-msg'>✅ Medicine added successfully!</div>";
            } else {
                throw new Exception("Error adding medicine: " . $stmt->error);
            }
        } catch (Exception $e) {
            $message = "<div class='error-msg'>❌ " . $e->getMessage() . "</div>";
        }
    }

    // Handle restocking
    if (isset($_POST['restock'])) {
        $medicine_id = $_POST['medicine_id'];
        $add_quantity = $_POST['add_quantity'];

        try {
            $stmt = $conn->prepare("UPDATE medicines 
                                   SET quantity = quantity + ? 
                                   WHERE id = ?");
            $stmt->bind_param("ii", $add_quantity, $medicine_id);
            
            if ($stmt->execute()) {
                $message = "<div class='success-msg'>✅ Restocked successfully!</div>";
            } else {
                throw new Exception("Restocking error: " . $stmt->error);
            }
        } catch (Exception $e) {
            $message = "<div class='error-msg'>❌ " . $e->getMessage() . "</div>";
        }
    }
}

// Handle deleting medicine
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
        $stmt->bind_param("i", $_GET['delete_id']);
        
        if ($stmt->execute()) {
            $message = "<div class='success-msg'>✅ Medicine deleted successfully!</div>";
        } else {
            throw new Exception("Delete error: " . $stmt->error);
        }
    } catch (Exception $e) {
        $message = "<div class='error-msg'>❌ " . $e->getMessage() . "</div>";
    }
}

// Fetch all medicines
$medicines = $conn->query("SELECT * FROM medicines ORDER BY expiry_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>MedCare - Medicine Inventory</title>
    <style>
        /* Hospital theme styles from previous implementation */
        body { font-family: 'Arial', sans-serif; background: #e8f4f8; margin: 0; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 0 20px rgba(42,92,125,0.1); }
        h2 { color: #2a5c7d; border-bottom: 2px solid #2a5c7d; padding-bottom: 1rem; margin-bottom: 2rem; }
        .medical-icon { font-size: 2rem; margin-right: 10px; }
        .medicine-form { background: #f8f9fa; padding: 2rem; border-radius: 10px; margin-bottom: 3rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #2a5c7d; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        button { background: #2a5c7d; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; transition: background 0.3s ease; }
        button:hover { background: #1a4560; }
        .medicine-table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        .medicine-table th, .medicine-table td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .medicine-table th { background: #2a5c7d; color: white; }
        .low-stock { color: #dc3545; font-weight: bold; }
        .expiring-soon { background-color: #fff3cd; }
        .delete-btn { background: #ff4444; padding: 8px 15px; border-radius: 5px; color: white; text-decoration: none; }
        .success-msg, .error-msg { padding: 15px; border-radius: 8px; margin: 1rem 0; }
        .success-msg { background: #dff0d8; color: #3c763d; }
        .error-msg { background: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <div class="container">
        <h2><span class="medical-icon">💊</span>Medicine Inventory</h2>

        <?php if(!empty($message)) echo $message; ?>

        <!-- Add Medicine Form -->
        <div class="medicine-form">
            <form method="POST">
                <input type="hidden" name="add_medicine" value="1">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Medicine Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Price per Unit</label>
                        <input type="number" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" required>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <input type="text" name="supplier">
                    </div>
                    <div class="form-group">
                        <label>Batch Number</label>
                        <input type="text" name="batch_number">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="2"></textarea>
                    </div>
                </div>
                <button type="submit">Add Medicine</button>
            </form>
        </div>

        <!-- Inventory Table -->
        <table class="medicine-table">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Expiry</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($medicines && $medicines->num_rows > 0): ?>
                    <?php while ($row = $medicines->fetch_assoc()): ?>
                        <?php
                        $expiring = strtotime($row['expiry_date']) < strtotime('+30 days');
                        $lowStock = $row['quantity'] < 20;
                        ?>
                        <tr class="<?= $expiring ? 'expiring-soon' : '' ?>">
                            <td>
                                <strong><?= htmlspecialchars($row['name']) ?></strong><br>
                                <small><?= htmlspecialchars($row['description']) ?></small>
                            </td>
                            <td class="<?= $lowStock ? 'low-stock' : '' ?>">
                                <?= htmlspecialchars($row['quantity']) ?>
                            </td>
                            <td>₹<?= number_format($row['price'], 2) ?></td>
                            <td><?= date('d M Y', strtotime($row['expiry_date'])) ?></td>
                            <td><?= htmlspecialchars($row['supplier']) ?></td>
                            <td>
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="medicine_id" value="<?= $row['id'] ?>">
                                    <input type="number" name="add_quantity" min="1" style="width: 80px;" required>
                                    <button type="submit" name="restock">Restock</button>
                                </form>
                                <a href="medicine_inventory.php?delete_id=<?= $row['id'] ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Delete this medicine?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No medicines in inventory</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>