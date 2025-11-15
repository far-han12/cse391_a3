<?php
// index.php - User Panel
require_once "db_config.php";

date_default_timezone_set("Asia/Dhaka");

$today = date("Y-m-d");

$selectedDate = isset($_GET["appointment_date"]) && $_GET["appointment_date"] !== ""
    ? $_GET["appointment_date"]   
    : $today;                    


$mechanics = [];
$sql = "SELECT m.id, m.name, m.max_per_day,
               (m.max_per_day - COUNT(a.id)) AS free_slots
        FROM mechanics m
        LEFT JOIN appointments a
          ON a.mechanic_id = m.id
         AND a.appointment_date = ?
        GROUP BY m.id, m.name, m.max_per_day
        ORDER BY m.id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row["free_slots"] === null) {
        $row["free_slots"] = $row["max_per_day"]; 
    }
    $mechanics[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Workshop Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 20px 25px 30px;
            border-radius: 6px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        h1, h2 {
            margin-top: 0;
            color: #333;
        }
        .help-box {
            background: #eef8ff;
            border: 1px solid #b5d5ff;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        form input, form select, form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .col-half {
            flex: 1 1 48%;
        }
        .btn {
            margin-top: 15px;
            padding: 10px 18px;
            background: #007bff;
            color: #fff;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 15px;
        }
        .btn-secondary {
            background: #28a745;
        }
        .small {
            font-size: 12px;
            color: #555;
        }
        .mechanic-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
        }
        .mechanic-table th, .mechanic-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .mechanic-table th {
            background: #f0f0f0;
        }
        .badge-full {
            color: #fff;
            background: #dc3545;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }
        .badge-available {
            color: #fff;
            background: #28a745;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar a {
            font-size: 13px;
            color: #007bff;
            text-decoration: none;
        }
        .top-bar a:hover {
            text-decoration: underline;
        }
        .error-msg, .success-msg {
            margin-top: 10px;
            padding: 8px;
            border-radius: 4px;
            font-size: 14px;
        }
        .error-msg {
            background: #ffe5e5;
            border: 1px solid #ff7b7b;
            color: #b30000;
        }
        .success-msg {
            background: #e6ffea;
            border: 1px solid #8fd19e;
            color: #1f6f2e;
        }
.date-check-row {
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 420px;
    margin-top: 6px;
}

.date-check-row input[type="date"] {
    flex: 1;           
}

.date-check-row .btn {
    margin-top: 0;      
    padding: 8px 14px;
    white-space: nowrap;
}

@media (max-width: 600px) {
    .date-check-row {
        flex-direction: column;
        align-items: stretch;
    }

    .date-check-row .btn {
        width: 100%;
        text-align: center;
    }
}

    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>Car Workshop Online Appointment</h1>
        <a href="admin.php" target="_blank">Go to Admin Panel</a>
    </div>

    <div class="help-box">
        <strong>Help:</strong> Fill in your details, choose the appointment date, and select your desired mechanic.
        Each mechanic can handle maximum <strong>4 cars per day</strong>. If you already have an appointment
        on the same date, the system will not allow a duplicate booking.
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] !== ""): ?>
        <div class="<?php echo isset($_GET['type']) && $_GET['type'] === 'error' ? 'error-msg' : 'success-msg'; ?>">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>
<form method="get" action="index.php" style="margin-bottom: 10px;">
    <label for="appointment_date">
        Select appointment date to check mechanic availability:
    </label>

    <div class="date-check-row">
        <input
            type="date"
            id="appointment_date"
            name="appointment_date"
            value="<?php echo htmlspecialchars($selectedDate); ?>"
                min="<?php echo htmlspecialchars($today); ?>"
            required
        >

        <button type="submit" class="btn btn-secondary">
            Check availability
        </button>
    </div>

    <span class="small">
        This date will also be used in the appointment form below.
    </span>
</form>



    <h2>Mechanic availability on <?php echo htmlspecialchars($selectedDate); ?></h2>
    <table class="mechanic-table">
        <tr>
            <th>#</th>
            <th>Mechanic Name</th>
            <th>Free places (out of 4)</th>
            <th>Status</th>
        </tr>
        <?php foreach ($mechanics as $m): ?>
            <tr>
                <td><?php echo $m["id"]; ?></td>
                <td><?php echo htmlspecialchars($m["name"]); ?></td>
                <td><?php echo (int)$m["free_slots"]; ?></td>
                <td>
                    <?php if ($m["free_slots"] <= 0): ?>
                        <span class="badge-full">Full</span>
                    <?php else: ?>
                        <span class="badge-available">Available</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <hr style="margin:20px 0;">

    <h2>Take an Appointment</h2>
    <form method="post" action="submit_appointment.php" onsubmit="return validateForm();">
        <div class="row">
            <div class="col-half">
                <label for="client_name">Name*</label>
                <input type="text" id="client_name" name="client_name" required>
            </div>
            <div class="col-half">
                <label for="phone">Phone*</label>
                <input type="text" id="phone" name="phone" required>
            </div>
        </div>

        <label for="address">Address*</label>
        <textarea id="address" name="address" rows="2" required></textarea>

        <div class="row">
            <div class="col-half">
                <label for="car_license">Car License Number*</label>
                <input type="text" id="car_license" name="car_license" required>
            </div>
            <div class="col-half">
                <label for="engine_no">Car Engine Number*</label>
                <input type="text" id="engine_no" name="engine_no" required>
            </div>
        </div>

        <div class="row">
            <div class="col-half">
                <label for="appointment_date2">Appointment Date*</label>
                <input
                    type="date"
                    id="appointment_date2"
                    name="appointment_date"
                    value="<?php echo htmlspecialchars($selectedDate); ?>"
                        min="<?php echo htmlspecialchars($today); ?>"
                    required
                >
            </div>
            <div class="col-half">
                <label for="mechanic_id">Select Mechanic*</label>
                <select id="mechanic_id" name="mechanic_id" required>
                    <option value="">-- Choose mechanic --</option>
                    <?php foreach ($mechanics as $m): ?>
                        <option value="<?php echo $m["id"]; ?>"
                            <?php echo ($m["free_slots"] <= 0 ? 'disabled' : ''); ?>>
                            <?php
                            echo htmlspecialchars($m["name"]) . " ("
                                . (int)$m["free_slots"] . " free)";
                            if ($m["free_slots"] <= 0) {
                                echo " - FULL";
                            }
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn">Submit Appointment</button>
    </form>
</div>

<script>
    function validateForm() {
        const name = document.getElementById("client_name").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const carLicense = document.getElementById("car_license").value.trim();
        const engineNo = document.getElementById("engine_no").value.trim();
        const date = document.getElementById("appointment_date2").value.trim();
        const mechanic = document.getElementById("mechanic_id").value;

        if (name === "" || phone === "" || carLicense === "" || engineNo === "" || date === "" || mechanic === "") {
            alert("Please fill in all required fields.");
            return false;
        }

        const phoneDigitsOnly = /^[0-9]+$/;
        if (!phoneDigitsOnly.test(phone)) {
            alert("Phone must contain only numbers.");
            return false;
        }

        const engineDigitsOnly = /^[0-9]+$/;
        if (!engineDigitsOnly.test(engineNo)) {
            alert("Car engine number must contain only numbers.");
            return false;
        }

        if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
            alert("Appointment date must be a valid date.");
            return false;
        }

        return true;
    }
</script>
</body>
</html>
<?php
$conn->close();
?>
