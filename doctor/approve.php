<?php

session_start();

require_once("../config/database.php");


/* =========================================================
   CHECK LOGIN
========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}


/* =========================================================
   CHECK APPOINTMENT ID
========================================================= */

if (!isset($_GET['id'])) {

    header("Location: appointments.php");
    exit();

}

$appointment_id = intval($_GET['id']);


/* =========================================================
   GET APPOINTMENT
========================================================= */

$sql = "
    SELECT
        id,
        patient_id,
        doctor_id,
        appointment_date,
        appointment_time,
        problem,
        status
    FROM appointments
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error: " . mysqli_error($conn));

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $appointment_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$appointment = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================================
   CHECK APPOINTMENT
========================================================= */

if (!$appointment) {

    die("Appointment does not exist.");

}


/* =========================================================
   CHECK STATUS
========================================================= */

if (strtolower($appointment['status']) !== "pending") {

    header("Location: appointments.php");
    exit();

}


/* =========================================================
   APPROVE APPOINTMENT
========================================================= */

$update_sql = "
    UPDATE appointments
    SET status = 'Approved'
    WHERE id = ?
    AND status = 'Pending'
";

$update_stmt = mysqli_prepare(
    $conn,
    $update_sql
);

if (!$update_stmt) {

    die("Update error: " . mysqli_error($conn));

}

mysqli_stmt_bind_param(
    $update_stmt,
    "i",
    $appointment_id
);

$updated = mysqli_stmt_execute(
    $update_stmt
);

mysqli_stmt_close($update_stmt);


/* =========================================================
   CREATE NOTIFICATION
========================================================= */

if ($updated) {

    $title = "Appointment Approved";

    $formatted_date = date(
        "d M Y",
        strtotime(
            $appointment['appointment_date']
        )
    );

    $formatted_time = date(
        "h:i A",
        strtotime(
            $appointment['appointment_time']
        )
    );

    $message =
        "Your appointment on " .
        $formatted_date .
        " at " .
        $formatted_time .
        " has been approved by your doctor.";

    $type = "appointment";


    $notification_sql = "
        INSERT INTO notifications
        (
            user_id,
            title,
            message,
            type,
            related_id,
            is_read
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            0
        )
    ";

    $notification_stmt = mysqli_prepare(
        $conn,
        $notification_sql
    );


    if ($notification_stmt) {

        mysqli_stmt_bind_param(
            $notification_stmt,
            "isssi",
            $appointment['patient_id'],
            $title,
            $message,
            $type,
            $appointment_id
        );

        mysqli_stmt_execute(
            $notification_stmt
        );

        mysqli_stmt_close(
            $notification_stmt
        );

    }

}


/* =========================================================
   RETURN
========================================================= */

header("Location: appointments.php");

exit();

?>