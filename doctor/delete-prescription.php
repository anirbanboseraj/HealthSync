<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include("../config/database.php");


// =====================================================
// CHECK DOCTOR LOGIN
// =====================================================

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] != 'doctor'
) {
    header("Location: ../login.php");
    exit();
}


$user_id = $_SESSION['user_id'];


// =====================================================
// GET LOGGED-IN DOCTOR
// =====================================================

$doctorQuery = mysqli_query(
    $conn,
    "
    SELECT id
    FROM doctors
    WHERE user_id='$user_id'
    LIMIT 1
    "
);


if (!$doctorQuery) {
    die("Doctor query error: " . mysqli_error($conn));
}


if (mysqli_num_rows($doctorQuery) == 0) {
    die("Doctor profile not found.");
}


$doctor = mysqli_fetch_assoc($doctorQuery);

$doctor_id = $doctor['id'];


// =====================================================
// CHECK PRESCRIPTION ID
// =====================================================

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    die("Invalid prescription ID.");
}


$prescription_id = intval($_GET['id']);


// =====================================================
// DELETE ONLY THIS DOCTOR'S PRESCRIPTION
// =====================================================

$delete = mysqli_query(
    $conn,
    "
    DELETE FROM prescriptions

    WHERE id='$prescription_id'

    AND doctor_id='$doctor_id'
    "
);


if (!$delete) {

    die(
        "Unable to delete prescription: "
        . mysqli_error($conn)
    );

}


// =====================================================
// CHECK WHETHER SOMETHING WAS ACTUALLY DELETED
// =====================================================

if (mysqli_affected_rows($conn) == 0) {

    die(
        "Prescription not found or you do not have "
        . "permission to delete it."
    );

}


// =====================================================
// RETURN TO PRESCRIPTION LIST
// =====================================================

header("Location: prescription.php?deleted=1");

exit();

?>