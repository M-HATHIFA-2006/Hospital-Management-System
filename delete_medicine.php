<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $medicine_id = $_GET['id'];
    $sql = "DELETE FROM medicines WHERE id = '$medicine_id'";
    if ($conn->query($sql)) {
        echo "<script>alert('✅ Medicine deleted successfully!'); window.location.href='medicine_inventory.php';</script>";
    } else {
        echo "<script>alert('❌ Error deleting medicine!'); window.location.href='medicine_inventory.php';</script>";
    }
}
?>
