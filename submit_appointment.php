<?php
// submit_appointment.php
require_once "db_config.php";

function redirect_with_message($msg, $type = "success") {
    $msg = urlencode($msg);
    header("Location: index.php?msg={$msg}&type={$type}");
    exit;
}

$client_name = isset($_POST["client_name"]) ? trim($_POST["client_name"]) : "";
$address = isset($_POST["address"]) ? trim($_POST["address"]) : "";
$phone = isset($_POST["phone"]) ? trim($_POST["phone"]) : "";
$car_license = isset($_POST["car_license"]) ? trim($_POST["car_license"]) : "";
$engine_no = isset($_POST["engine_no"]) ? trim($_POST["engine_no"]) : "";
$appointment_date = isset($_POST["appointment_date"]) ? trim($_POST["appointment_date"]) : "";
$mechanic_id = isset($_POST["mechanic_id"]) ? (int)$_POST["mechanic_id"] : 0;

if (
    $client_name === "" || $address === "" || $phone === "" ||
    $car_license === "" || $engine_no === "" || $appointment_date === "" ||
    $mechanic_id <= 0
) {
    redirect_with_message("Please fill in all required fields.", "error");
}

if (!preg_match("/^[0-9]+$/", $phone)) {
    redirect_with_message("Phone must contain only numbers.", "error");
}
if (!preg_match("/^[0-9]+$/", $engine_no)) {
    redirect_with_message("Car engine number must contain only numbers.", "error");
}

if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $appointment_date)) {
    redirect_with_message("Invalid appointment date.", "error");
}

$sql = "SELECT COUNT(*) AS total
        FROM appointments
        WHERE phone = ? AND appointment_date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $phone, $appointment_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row["total"] > 0) {
    redirect_with_message("You already have an appointment on this date.", "error");
}

$sql = "SELECT COUNT(*) AS total
        FROM appointments
        WHERE mechanic_id = ? AND appointment_date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $mechanic_id, $appointment_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row["total"] >= 4) {
    redirect_with_message("Selected mechanic is fully booked on that date. Please choose another mechanic or date.", "error");
}

$sql = "INSERT INTO appointments
        (client_name, address, phone, car_license, engine_no, appointment_date, mechanic_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssi",
    $client_name,
    $address,
    $phone,
    $car_license,
    $engine_no,
    $appointment_date,
    $mechanic_id
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    redirect_with_message("Appointment booked successfully!");
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    redirect_with_message("Error while booking appointment: " . $error, "error");
}
?>
