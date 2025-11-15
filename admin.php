<?php
require_once "db_config.php";

$mechanics = [];
$mSql = "SELECT id, name FROM mechanics ORDER BY id ASC";
$mRes = $conn->query($mSql);
while ($row = $mRes->fetch_assoc()) {
    $mechanics[] = $row;
}

$sql = "SELECT a.id, a.client_name, a.phone, a.car_license, a.appointment_date,
               m.name AS mechanic_name, a.mechanic_id
        FROM appointments a
        JOIN mechanics m ON a.mechanic_id = m.id
        ORDER BY a.appointment_date ASC, a.id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Appointment List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            padding: 20px 25px 30px;
            border-radius: 6px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            color: #333;
        }
        .small {
            font-size: 13px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
        .btn {
            padding: 5px 10px;
            background: #007bff;
            color: #fff;
            border-radius: 4px;
            border: none;
            font-size: 13px;
            cursor: pointer;
        }
        .msg {
            margin-top: 10px;
            padding: 8px;
            border-radius: 4px;
            font-size: 14px;
        }
        .msg-success {
            background: #e6ffea;
            border: 1px solid #8fd19e;
            color: #1f6f2e;
        }
        .msg-error {
            background: #ffe5e5;
            border: 1px solid #ff7b7b;
            color: #b30000;
        }
        a {
            color: #007bff;
            text-decoration: none;
            font-size: 13px;
        }
        a:hover {
            text-decoration: underline;
        }
        input[type="date"], select {
            font-size: 13px;
            padding: 3px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Admin - Appointment List</h1>
    <p class="small">
        You can view all appointments here and change the appointment date or assigned mechanic
        (system will check availability and client duplicate rules).
        <br>
        <a href="index.php">&larr; Back to User Page</a>
    </p>

    <?php if (isset($_GET["msg"]) && $_GET["msg"] !== ""): ?>
        <div class="msg <?php echo (isset($_GET["type"]) && $_GET["type"] === "error") ? 'msg-error' : 'msg-success'; ?>">
            <?php echo htmlspecialchars($_GET["msg"]); ?>
        </div>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Client Name</th>
            <th>Phone</th>
            <th>Car License</th>
            <th>Appointment Date</th>
            <th>Mechanic</th>
            <th>Actions</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row["id"]; ?></td>
                    <td><?php echo htmlspecialchars($row["client_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["phone"]); ?></td>
                    <td><?php echo htmlspecialchars($row["car_license"]); ?></td>
                    <td>
                        <form method="post" action="update_appointment.php">
                            <input type="hidden" name="appointment_id" value="<?php echo $row["id"]; ?>">
                            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($row["phone"]); ?>">
                            <input type="date" name="appointment_date"
                                   value="<?php echo htmlspecialchars($row["appointment_date"]); ?>" required>
                    </td>
                    <td>
                            <select name="mechanic_id" required>
                                <?php foreach ($mechanics as $m): ?>
                                    <option value="<?php echo $m["id"]; ?>"
                                            <?php echo ($m["id"] == $row["mechanic_id"]) ? "selected" : ""; ?>>
                                        <?php echo htmlspecialchars($m["name"]); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                    </td>
                    <td>
                            <button type="submit" class="btn">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No appointments found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
<?php
$conn->close();
?>
