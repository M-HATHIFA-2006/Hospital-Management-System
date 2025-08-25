<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1) Pull every room (one bed each) + assigned patient name
$bedQuery  = "
  SELECT b.id,
         b.room_number,
         b.status,
         p.name AS patient_name
    FROM beds AS b
    LEFT JOIN patients AS p
      ON b.patient_id = p.id
    ORDER BY b.room_number
";
$bedResult = $conn->query($bedQuery);

// 2) Figure out halfway point
$totalRooms = $bedResult ? $bedResult->num_rows : 0;
$halfway    = (int) ceil($totalRooms / 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bed & Room Management</title>
  <style>
    body { font-family: Arial, sans-serif; background: #eef7fb; padding: 2rem; }
    .container { max-width: 800px; margin: auto; background: #fff; padding: 1.5rem;
                 border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
    h2 { color: #2a5c7d; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 0.75rem; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #2a5c7d; color: #fff; }
    .status-Available { color: #27ae60; font-weight: bold; }
    .status-Occupied  { color: #c0392b; font-weight: bold; }
  </style>
</head>
<body>
  <div class="container">
    <h2>🏥 Bed & Room Management</h2>
    <table>
      <thead>
        <tr>
          <th>Room Number</th>
          <th>Floor</th>
          <th>Status</th>
          <th>Assigned Patient</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($bedResult && $bedResult->num_rows):
          $index = 0;
          while ($row = $bedResult->fetch_assoc()):
            $index++;
            // first half → floor 1, rest → floor 2
            $floor = ($index <= $halfway) ? 1 : 2;
        ?>
          <tr>
            <td><?= htmlspecialchars($row['room_number']) ?></td>
            <td><?= $floor ?></td>
            <td class="status-<?= htmlspecialchars($row['status']) ?>">
              <?= htmlspecialchars($row['status']) ?>
            </td>
            <td>
              <?= $row['patient_name']
                    ? htmlspecialchars($row['patient_name'])
                    : 'None' ?>
            </td>
          </tr>
        <?php
          endwhile;
        else:
        ?>
          <tr>
            <td colspan="4" style="text-align:center;">No rooms defined</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>