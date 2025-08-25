<?php
// assign_bed.php

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $patient_id = (int)$_POST['patient_id'];

    // 1) Verify patient exists & is ICU or pregnant
    $pQ = "SELECT situation FROM patients WHERE id = $patient_id";
    $pR = $conn->query($pQ);
    if (!$pR || $pR->num_rows === 0) {
        echo "<div class='error-msg'>❌ Invalid patient ID.</div>";
        exit;
    }
    $info = $pR->fetch_assoc();
    if (!in_array($info['situation'], ['ICU','pregnant'])) {
        echo "<div class='error-msg'>
                ❌ Only ICU or Pregnant patients can receive a bed.
              </div>";
        exit;
    }

    // 2) Allocate the first available bed
    $bedQ = "SELECT * FROM beds WHERE status='Available' LIMIT 1";
    $bedR = $conn->query($bedQ);

    if ($bedR && $bedR->num_rows) {
        $bed       = $bedR->fetch_assoc();
        $bed_id    = $bed['id'];
        $bed_room  = $bed['room_number'];
        $conn->query("
          UPDATE beds
             SET status='Occupied', patient_id=$patient_id
           WHERE id=$bed_id
        ");
        echo "<div class='success-msg'>
                ✅ Assigned Room $bed_room.
              </div>";
    } else {
        echo "<div class='error-msg'>❌ No available beds!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Bed</title>
  <style>
    body { font-family: Arial, sans-serif; background:#eef7fb; padding:2rem; }
    .success-msg { background:#dff0d8; color:#3c763d; padding:.8rem; border-radius:4px; margin-bottom:1rem; }
    .error-msg   { background:#f2dede; color:#a94442; padding:.8rem; border-radius:4px; margin-bottom:1rem; }
    form { max-width:400px; background:#fff; padding:1.2rem; border-radius:6px; box-shadow:0 0 10px rgba(0,0,0,.1); }
    label { display:block; margin-bottom:.4rem; font-weight:bold; }
    input { width:100%; padding:.6rem; margin-bottom:1rem; border:1px solid #ccc; border-radius:4px; }
    button { background:#2a5c7d; color:#fff; padding:.6rem 1.2rem; border:none; border-radius:4px; cursor:pointer; }
    button:hover { background:#1a4560; }
  </style>
</head>
<body>
  <h2>Assign Bed to Patient</h2>
  <form method="POST">
    <label for="patient_id">Patient ID</label>
    <input type="number" name="patient_id" id="patient_id" required>
    <button type="submit">Assign Bed</button>
  </form>
</body>
</html>