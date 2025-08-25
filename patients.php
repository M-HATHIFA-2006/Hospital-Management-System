<?php
// patients.php

session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// 1) Handle new patient form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_patient'])) {
    $name            = $conn->real_escape_string($_POST['name']);
    $disease         = $conn->real_escape_string($_POST['disease']);
    $age             = (int)$_POST['age'];
    $dob             = $conn->real_escape_string($_POST['dob']);
    $blood_group     = $conn->real_escape_string($_POST['blood_group']);
    $medical_history = $conn->real_escape_string($_POST['medical_history']);
    $pressure_level  = $conn->real_escape_string($_POST['pressure_level']);
    $sugar_level     = $conn->real_escape_string($_POST['sugar_level']);
    $situation       = $conn->real_escape_string($_POST['situation']);

    // ICU & Pregnant get a bed, everyone else (i.e. “normal”) does not
    if (in_array($situation, ['ICU','pregnant'])) {
        $bedQ  = "SELECT * FROM beds WHERE status='Available' LIMIT 1";
        $bedR  = $conn->query($bedQ);

        if ($bedR && $bedR->num_rows) {
            $bed         = $bedR->fetch_assoc();
            $room_number = $bed['room_number'];

            $insertSQL = "
              INSERT INTO patients
                (name,disease,room_number,age,dob,blood_group,
                 medical_history,pressure_level,sugar_level,situation)
              VALUES
                ('$name','$disease','$room_number',$age,'$dob','$blood_group',
                 '$medical_history','$pressure_level','$sugar_level','$situation')";
            if ($conn->query($insertSQL)) {
                $pid = $conn->insert_id;
                $conn->query("
                  UPDATE beds
                     SET status='Occupied', patient_id=$pid
                   WHERE id={$bed['id']}");
                $message = "<div class='success-msg'>
                              ✅ $situation patient admitted to Room $room_number.
                            </div>";
            } else {
                $message = "<div class='error-msg'>
                              ❌ Could not save patient:<br>".htmlspecialchars($conn->error)."
                            </div>";
            }
        } else {
            $message = "<div class='error-msg'>
                          ❌ No beds available to assign.
                        </div>";
        }

    } else {
        // normal case: just record, no room_number column
        $insertSQL = "
          INSERT INTO patients
            (name,disease,age,dob,blood_group,
             medical_history,pressure_level,sugar_level,situation)
          VALUES
            ('$name','$disease',$age,'$dob','$blood_group',
             '$medical_history','$pressure_level','$sugar_level','$situation')";
        if ($conn->query($insertSQL)) {
            $message = "<div class='success-msg'>
                          ✅ Normal patient added (no bed allocated).
                        </div>";
        } else {
            $message = "<div class='error-msg'>
                          ❌ Could not save patient:<br>".htmlspecialchars($conn->error)."
                        </div>";
        }
    }
}

// 2) Discharge logic (unchanged)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['discharge_id'])) {
    $id = (int)$_POST['discharge_id'];
    $conn->query("UPDATE beds SET status='Available', patient_id=NULL WHERE patient_id=$id");
    $conn->query("DELETE FROM patients WHERE id=$id");
    $message = "<div class='success-msg'>✅ Patient discharged.</div>";
}

// 3) Fetch all patients for listing
$patientsResult = $conn->query("SELECT * FROM patients ORDER BY admitted_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Management</title>
  <style>
    body { font-family: Arial,sans-serif; background:#eef7fb; padding:2rem; }
    .container { max-width:900px; margin:auto; background:#fff; padding:1.5rem;
                 border-radius:8px; box-shadow:0 0 15px rgba(0,0,0,.1); }
    h2 { color:#2a5c7d; margin-top:0; }
    .patient-form { background:#f9f9f9; padding:1rem; border-radius:6px; }
    .form-group { margin-bottom:1rem; }
    label { display:block; margin-bottom:.4rem; font-weight:bold; }
    input, select, textarea { width:100%; padding:.5rem; border:1px solid #ccc; border-radius:4px; }
    button { background:#2a5c7d; color:#fff; padding:.6rem 1.2rem; border:none; border-radius:4px; cursor:pointer; }
    button:hover { background:#1a4560; }
    .success-msg { background:#dff0d8; color:#3c763d; padding:.8rem; border-radius:4px; margin-bottom:1rem; }
    .error-msg   { background:#f2dede; color:#a94442; padding:.8rem; border-radius:4px; margin-bottom:1rem; }
    table { width:100%; border-collapse:collapse; margin-top:1.5rem; }
    th, td { padding:.6rem; border-bottom:1px solid #ddd; text-align:left; }
    th { background:#2a5c7d; color:#fff; }
    .delete-btn { background:#c0392b; color:#fff; padding:.4rem .8rem; border:none; border-radius:4px; cursor:pointer; }
    .delete-btn:hover { background:#922b21; }
  </style>
</head>
<body>
  <div class="container">
    <h2>🩺 Patient Management</h2>
    <?= $message ?>

    <div class="patient-form">
      <form method="POST">
        <input type="hidden" name="add_patient" value="1">
        <div class="form-group">
          <label>Name</label>
          <input type="text" name="name" required>
        </div>
        <div class="form-group">
          <label>Situation</label>
          <select name="situation" required>
            <option value="normal">Normal</option>
            <option value="pregnant">Pregnant</option>
            <option value="ICU">ICU</option>
          </select>
        </div>
        <!-- other fields unchanged -->
        <div class="form-group">
          <label>Age</label>
          <input type="number" name="age" min="0">
        </div>
        <div class="form-group">
          <label>DOB</label>
          <input type="date" name="dob">
        </div>
        <div class="form-group">
          <label>Blood Group</label>
          <input type="text" name="blood_group">
        </div>
        <div class="form-group">
          <label>Medical History</label>
          <textarea name="medical_history" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label>Pressure Level</label>
          <input type="text" name="pressure_level">
        </div>
        <div class="form-group">
          <label>Sugar Level</label>
          <input type="text" name="sugar_level">
        </div>
        <div class="form-group">
          <label>Symptoms</label>
          <input type="text" name="disease" required>
        </div>

        <button>Add Patient</button>
      </form>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Symptoms</th><th>Situation</th>
          <th>Room</th><th>Admitted At</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($patientsResult && $patientsResult->num_rows): ?>
          <?php while ($p = $patientsResult->fetch_assoc()): ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars($p['disease']) ?></td>
              <td><?= htmlspecialchars($p['situation']) ?></td>
              <td><?= $p['room_number'] ?? '—' ?></td>
              <td><?= $p['admitted_at'] ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('Discharge?')">
                  <input type="hidden" name="discharge_id" value="<?= $p['id'] ?>">
                  <button class="delete-btn">Discharge</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" style="text-align:center;">No patients admitted</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>