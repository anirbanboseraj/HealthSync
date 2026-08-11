<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../config/database.php");

$patient_id = $_SESSION['user_id'];


// ==============================
// GET FORM DATA
// ==============================

$doctor_id = isset($_POST['doctor_id'])
    ? intval($_POST['doctor_id'])
    : 0;

$appointment_date = $_POST['appointment_date'] ?? '';
$appointment_time = $_POST['appointment_time'] ?? '';
$problem = trim($_POST['reason'] ?? '');


// ==============================
// VALIDATION
// ==============================

if (
    $doctor_id <= 0 ||
    empty($appointment_date) ||
    empty($appointment_time) ||
    empty($problem)
) {
    die("Invalid appointment information.");
}


// ==============================
// CHECK DOCTOR
// ==============================

$doctor_query = mysqli_query(
    $conn,
    "SELECT id, fullname
     FROM doctors
     WHERE id = '$doctor_id'
     LIMIT 1"
);

if (!$doctor_query || mysqli_num_rows($doctor_query) == 0) {
    die("Selected doctor was not found.");
}

$doctor = mysqli_fetch_assoc($doctor_query);


// ==============================
// INSERT APPOINTMENT
// ==============================

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO appointments
    (
        patient_id,
        doctor_id,
        appointment_date,
        appointment_time,
        problem,
        status
    )
    VALUES (?, ?, ?, ?, ?, 'Pending')"
);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $stmt,
    "iisss",
    $patient_id,
    $doctor_id,
    $appointment_date,
    $appointment_time,
    $problem
);


if (mysqli_stmt_execute($stmt)) {

    $appointment_id = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);


    // ==============================
    // SUCCESS
    // ==============================

    header(
        "Location: my-appointments.php?success=1"
    );

    exit();

} else {

    $error = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    die("Unable to book appointment: " . $error);
}
?>