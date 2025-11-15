<?php
require_once "db_config.php";

function redirect_admin($msg, $type = "success") {
    $msg = urlencode($msg);
    header("Location: admin.php?msg={$msg}&type={$type}");
    exit;
}

$appointment_id = isset($_POST["appointment_id"]) ? (int)$_POST["appointment_id"] : 0;
$phone = isset($_POST["phone"]) ? trim($_POST["phone"]) : "";
$appointment_date = isset($_POST["appointment_date"]) ? trim($_POST["appointment_date"]) : "";
$mechanic_id = isset($_POST["mechanic_id"]) ? (int)$_POST["mechanic_id"] : 0;

if ($appointment_id <= 0 || $phone === "" || $appointment_date === "" || $mechanic_id <= 0) {
    redirect_admin("Invalid data for update.", "error");
}

if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $appointment_date)) {
    redirect_admin("Invalid appointment date.", "error");
}

$sql = "SELECT COUNT(*) AS total
        FROM appointments
        WHERE phone = ? AND appointment_date = ? AND id <> ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $phone, $appointment_date, $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row["total"] > 0) {
    redirect_admin("Client already has another appointment on this date.", "error");
}

$sql = "SELECT COUNT(*) AS total
        FROM appointments
        WHERE mechanic_id = ? AND appointment_date = ? AND id <> ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $mechanic_id, $appointment_date, $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row["total"] >= 4) {
    redirect_admin("Selected mechanic is fully booked on this date.", "error");
}

$sql = "UPDATE appointments
        SET appointment_date = ?, mechanic_id = ?
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $appointment_date, $mechanic_id, $appointment_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    redirect_admin("Appointment updated successfully.");
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    redirect_admin("Error updating appointment: " . $error, "error");
}
?>
