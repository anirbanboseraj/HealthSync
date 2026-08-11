<?php

session_start();

/* =========================================================
   ADMIN SECURITY
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   DATABASE
========================================================= */

require_once("../config/database.php");


/* =========================================================
   UPDATE APPOINTMENT STATUS
========================================================= */

if (
    isset($_POST['update_status']) &&
    isset($_POST['appointment_id']) &&
    isset($_POST['new_status'])
) {

    $appointment_id = intval($_POST['appointment_id']);

    $new_status = $_POST['new_status'];

    /*
       Only allow valid appointment statuses
    */

    $allowed_statuses = [
        'Pending',
        'Approved',
        'Completed',
        'Cancelled'
    ];

    if (in_array($new_status, $allowed_statuses)) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE appointments
             SET status = ?
             WHERE id = ?"
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $new_status,
                $appointment_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }
    }

    /*
       Refresh page after update
    */

    header("Location: appointments.php");
    exit();
}


/* =========================================================
   GET APPOINTMENTS
========================================================= */

$sql = "

    SELECT

        a.id,

        a.patient_id,

        a.doctor_id,

        a.appointment_date,

        a.appointment_time,

        a.problem,

        a.status,

        a.created_at,

        u.fullname AS patient_name,

        u.email AS patient_email,

        d.fullname AS doctor_name

    FROM appointments a

    LEFT JOIN users u
        ON a.patient_id = u.id

    LEFT JOIN doctors d
        ON a.doctor_id = d.id

    ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC

";


$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Database Error: " .
        mysqli_error($conn)
    );

}


$appointment_count = mysqli_num_rows($result);


/* =========================================================
   COUNT STATUS
========================================================= */

$pending_count = 0;
$approved_count = 0;
$completed_count = 0;
$cancelled_count = 0;


/*
   We can calculate counts while displaying.
   Store rows first so we can reuse them.
*/

$appointments = [];

while ($row = mysqli_fetch_assoc($result)) {

    $appointments[] = $row;

    switch ($row['status']) {

        case 'Pending':
            $pending_count++;
            break;

        case 'Approved':
            $approved_count++;
            break;

        case 'Completed':
            $completed_count++;
            break;

        case 'Cancelled':
            $cancelled_count++;
            break;
    }
}

?>


<!DOCTYPE html>

<html lang="en">


<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Appointments | HealthSync
</title>


<!-- FONT AWESOME -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<style>

/* =========================================================
   RESET
========================================================= */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* =========================================================
   BODY
========================================================= */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #020617;

    color: #e2e8f0;

}


/* =========================================================
   LAYOUT
========================================================= */

.dashboard {

    min-height: 100vh;

    display: flex;

}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    width: 250px;

    position: fixed;

    top: 0;

    left: 0;

    bottom: 0;

    background: #0f172a;

    border-right: 1px solid #1e293b;

    padding: 25px 15px;

}


.logo {

    text-align: center;

    margin-bottom: 35px;

}


.logo h2 {

    color: white;

    font-size: 25px;

}


.logo span {

    color: #3b82f6;

}


.logo p {

    color: #64748b;

    font-size: 11px;

    margin-top: 5px;

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    margin-bottom: 6px;

    border-radius: 8px;

    color: #94a3b8;

    text-decoration: none;

    font-size: 14px;

    transition: 0.2s;

}


.sidebar a:hover {

    background: #172554;

    color: #60a5fa;

}


.sidebar a.active {

    background: #1e3a8a;

    color: white;

}


.sidebar a i {

    width: 20px;

    text-align: center;

}


.logout {

    margin-top: 30px;

    color: #f87171 !important;

}


.logout:hover {

    background: #450a0a !important;

}


/* =========================================================
   MAIN
========================================================= */

.main {

    margin-left: 250px;

    width: calc(100% - 250px);

    padding: 30px;

}


/* =========================================================
   TOPBAR
========================================================= */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 30px;

}


.page-title h1 {

    color: white;

    font-size: 28px;

}


.page-title p {

    color: #64748b;

    font-size: 13px;

    margin-top: 7px;

}


.admin-profile {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 10px 15px;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 10px;

}


.admin-icon {

    width: 38px;

    height: 38px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #1e3a8a;

    color: #60a5fa;

}


.admin-profile strong {

    display: block;

    color: white;

    font-size: 13px;

}


.admin-profile small {

    color: #64748b;

    font-size: 10px;

}


/* =========================================================
   STAT CARDS
========================================================= */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 28px;

}


.stat-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 14px;

    padding: 20px;

}


.stat-top {

    display: flex;

    align-items: center;

    justify-content: space-between;

}


.stat-icon {

    width: 43px;

    height: 43px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

    font-size: 18px;

}


.stat-number {

    color: white;

    font-size: 25px;

    font-weight: bold;

    margin-top: 15px;

}


.stat-label {

    color: #64748b;

    font-size: 11px;

    margin-top: 5px;

}


/* =========================================================
   APPOINTMENT CARD
========================================================= */

.table-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    overflow: hidden;

}


.table-header {

    padding: 25px;

    border-bottom: 1px solid #1e293b;

}


.table-header h2 {

    color: white;

    font-size: 19px;

}


.table-header p {

    color: #64748b;

    font-size: 12px;

    margin-top: 6px;

}


/* =========================================================
   TABLE
========================================================= */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


.appointment-table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1100px;

}


.appointment-table th {

    padding: 15px 18px;

    background: #111827;

    color: #64748b;

    font-size: 10px;

    text-align: left;

    border-bottom: 1px solid #1e293b;

    white-space: nowrap;

}


.appointment-table td {

    padding: 17px 18px;

    color: #cbd5e1;

    font-size: 12px;

    border-bottom: 1px solid #1e293b;

    vertical-align: middle;

}


.appointment-table tr:hover {

    background: #111c31;

}


.appointment-table tr:last-child td {

    border-bottom: none;

}


/* =========================================================
   PATIENT / DOCTOR
========================================================= */

.person {

    display: flex;

    align-items: center;

    gap: 10px;

}


.person-icon {

    width: 35px;

    height: 35px;

    min-width: 35px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

}


.person-name {

    color: white;

    font-weight: bold;

    font-size: 12px;

}


.person-id {

    color: #64748b;

    font-size: 9px;

    margin-top: 3px;

}


/* =========================================================
   DATE
========================================================= */

.date-box {

    color: #e2e8f0;

    font-weight: bold;

}


.time-box {

    color: #60a5fa;

    margin-top: 4px;

    font-size: 11px;

}


/* =========================================================
   PROBLEM
========================================================= */

.problem {

    max-width: 180px;

    color: #94a3b8;

    line-height: 1.5;

}


/* =========================================================
   STATUS
========================================================= */

.status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 11px;

    border-radius: 20px;

    font-size: 9px;

    font-weight: bold;

    white-space: nowrap;

}


.status-pending {

    background: #422006;

    border: 1px solid #92400e;

    color: #fbbf24;

}


.status-approved {

    background: #052e16;

    border: 1px solid #166534;

    color: #4ade80;

}


.status-completed {

    background: #172554;

    border: 1px solid #1e40af;

    color: #60a5fa;

}


.status-cancelled {

    background: #450a0a;

    border: 1px solid #991b1b;

    color: #f87171;

}


/* =========================================================
   ACTIONS
========================================================= */

.actions {

    display: flex;

    align-items: center;

    gap: 6px;

    flex-wrap: wrap;

}


.action-btn {

    border: none;

    cursor: pointer;

    padding: 7px 10px;

    border-radius: 6px;

    font-size: 9px;

    font-weight: bold;

    transition: 0.2s;

}


.approve-btn {

    background: #052e16;

    color: #4ade80;

    border: 1px solid #166534;

}


.approve-btn:hover {

    background: #166534;

    color: white;

}


.complete-btn {

    background: #172554;

    color: #60a5fa;

    border: 1px solid #1e40af;

}


.complete-btn:hover {

    background: #1e40af;

    color: white;

}


.cancel-btn {

    background: #450a0a;

    color: #f87171;

    border: 1px solid #991b1b;

}


.cancel-btn:hover {

    background: #991b1b;

    color: white;

}


.view-btn {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 7px 10px;

    background: #1e3a8a;

    color: #60a5fa;

    border-radius: 6px;

    text-decoration: none;

    font-size: 9px;

}


.view-btn:hover {

    background: #2563eb;

    color: white;

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    padding: 70px 20px;

    text-align: center;

    color: #64748b;

}


.empty i {

    font-size: 45px;

    color: #334155;

    margin-bottom: 15px;

}


.empty h3 {

    color: #94a3b8;

    margin-bottom: 7px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 1100px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width: 800px) {

    .sidebar {

        width: 210px;

    }

    .main {

        margin-left: 210px;

        width: calc(100% - 210px);

        padding: 20px;

    }

}


@media(max-width: 600px) {

    .sidebar {

        position: relative;

        width: 100%;

    }

    .main {

        margin-left: 0;

        width: 100%;

    }

    .dashboard {

        display: block;

    }

    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

    .stats {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<div class="dashboard">


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">


    <div class="logo">

        <h2>
            Health<span>Sync</span>
        </h2>

        <p>
            Admin Portal
        </p>

    </div>


    <a href="dashboard.php">

        <i class="fa-solid fa-house"></i>

        Dashboard

    </a>


    <a href="doctors.php">

        <i class="fa-solid fa-user-doctor"></i>

        Doctors

    </a>


    <a href="patients.php">

        <i class="fa-solid fa-users"></i>

        Patients

    </a>


    <a
        href="appointments.php"
        class="active"
    >

        <i class="fa-solid fa-calendar-check"></i>

        Appointments

    </a>


    <a href="prescriptions.php">

        <i class="fa-solid fa-file-prescription"></i>

        Prescriptions

    </a>


   <a href="/HealthSync/admin/reports.php">

    <i class="fa-solid fa-chart-line"></i>

    Reports

</a>


  <a href="/HealthSync/admin/settings.php">

    <i class="fa-solid fa-gear"></i>

    Settings

</a>

    <a
        href="../logout.php"
        class="logout"
    >

        <i class="fa-solid fa-right-from-bracket"></i>

        Logout

    </a>


</aside>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">


        <div class="page-title">

            <h1>
                Manage Appointments
            </h1>

            <p>
                View and manage all HealthSync appointments.
            </p>

        </div>


        <div class="admin-profile">


            <div class="admin-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $_SESSION['fullname']
                        ?? 'Administrator'
                    );

                    ?>

                </strong>

                <small>
                    Administrator
                </small>

            </div>


        </div>


    </div>



    <!-- =================================================
         STATISTICS
    ================================================== -->

    <div class="stats">


        <!-- TOTAL -->

        <div class="stat-card">


            <div class="stat-top">

                <div class="stat-icon">

                    <i class="fa-solid fa-calendar-days"></i>

                </div>

            </div>


            <div class="stat-number">

                <?php echo $appointment_count; ?>

            </div>


            <div class="stat-label">

                Total Appointments

            </div>


        </div>



        <!-- PENDING -->

        <div class="stat-card">


            <div class="stat-top">

                <div class="stat-icon">

                    <i class="fa-solid fa-clock"></i>

                </div>

            </div>


            <div class="stat-number">

                <?php echo $pending_count; ?>

            </div>


            <div class="stat-label">

                Pending

            </div>


        </div>



        <!-- APPROVED -->

        <div class="stat-card">


            <div class="stat-top">

                <div class="stat-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>

            </div>


            <div class="stat-number">

                <?php echo $approved_count; ?>

            </div>


            <div class="stat-label">

                Approved

            </div>


        </div>



        <!-- COMPLETED -->

        <div class="stat-card">


            <div class="stat-top">

                <div class="stat-icon">

                    <i class="fa-solid fa-check-double"></i>

                </div>

            </div>


            <div class="stat-number">

                <?php echo $completed_count; ?>

            </div>


            <div class="stat-label">

                Completed

            </div>


        </div>


    </div>



    <!-- =================================================
         APPOINTMENTS
    ================================================== -->

    <div class="table-card">


        <div class="table-header">

            <h2>
                All Appointments
            </h2>

            <p>
                Manage patient appointments and their current status.
            </p>

        </div>



        <div class="table-wrapper">


        <?php if ($appointment_count > 0): ?>


            <table class="appointment-table">


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            PATIENT
                        </th>

                        <th>
                            DOCTOR
                        </th>

                        <th>
                            DATE / TIME
                        </th>

                        <th>
                            PROBLEM
                        </th>

                        <th>
                            STATUS
                        </th>

                        <th>
                            ACTION
                        </th>

                    </tr>

                </thead>



                <tbody>


                <?php foreach (
                    $appointments as $appointment
                ): ?>


                    <tr>


                        <!-- ID -->

                        <td>

                            #

                            <?php

                            echo intval(
                                $appointment['id']
                            );

                            ?>

                        </td>



                        <!-- PATIENT -->

                        <td>


                            <div class="person">


                                <div class="person-icon">

                                    <i class="fa-solid fa-user"></i>

                                </div>


                                <div>


                                    <div class="person-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $appointment['patient_name']
                                            ?? 'Unknown Patient'
                                        );

                                        ?>

                                    </div>


                                    <div class="person-id">

                                        Patient #

                                        <?php

                                        echo intval(
                                            $appointment['patient_id']
                                        );

                                        ?>

                                    </div>


                                </div>


                            </div>


                        </td>



                        <!-- DOCTOR -->

                        <td>


                            <div class="person">


                                <div class="person-icon">

                                    <i class="fa-solid fa-user-doctor"></i>

                                </div>


                                <div>


                                    <div class="person-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $appointment['doctor_name']
                                            ?? 'Unknown Doctor'
                                        );

                                        ?>

                                    </div>


                                    <div class="person-id">

                                        Doctor #

                                        <?php

                                        echo intval(
                                            $appointment['doctor_id']
                                        );

                                        ?>

                                    </div>


                                </div>


                            </div>


                        </td>



                        <!-- DATE -->

                        <td>


                            <div class="date-box">

                                <?php

                                echo htmlspecialchars(
                                    date(
                                        "d M Y",
                                        strtotime(
                                            $appointment[
                                                'appointment_date'
                                            ]
                                        )
                                    )
                                );

                                ?>

                            </div>


                            <div class="time-box">

                                <i class="fa-regular fa-clock"></i>

                                <?php

                                echo htmlspecialchars(
                                    date(
                                        "h:i A",
                                        strtotime(
                                            $appointment[
                                                'appointment_time'
                                            ]
                                        )
                                    )
                                );

                                ?>

                            </div>


                        </td>



                        <!-- PROBLEM -->

                        <td>


                            <div class="problem">

                                <?php

                                if (
                                    !empty(
                                        $appointment['problem']
                                    )
                                ) {

                                    echo nl2br(
                                        htmlspecialchars(
                                            $appointment['problem']
                                        )
                                    );

                                } else {

                                    echo "No problem specified.";

                                }

                                ?>

                            </div>


                        </td>



                        <!-- STATUS -->

                        <td>


                            <?php

                            $status =
                                $appointment['status']
                                ?? 'Pending';


                            $status_class =
                                'status-pending';


                            $status_icon =
                                'fa-clock';


                            if (
                                $status === 'Approved'
                            ) {

                                $status_class =
                                    'status-approved';

                                $status_icon =
                                    'fa-circle-check';

                            }


                            elseif (
                                $status === 'Completed'
                            ) {

                                $status_class =
                                    'status-completed';

                                $status_icon =
                                    'fa-check-double';

                            }


                            elseif (
                                $status === 'Cancelled'
                            ) {

                                $status_class =
                                    'status-cancelled';

                                $status_icon =
                                    'fa-circle-xmark';

                            }

                            ?>


                            <span
                                class="status
                                <?php echo $status_class; ?>"
                            >

                                <i
                                    class="fa-solid
                                    <?php echo $status_icon; ?>"
                                ></i>

                                <?php

                                echo htmlspecialchars(
                                    $status
                                );

                                ?>

                            </span>


                        </td>



                        <!-- ACTION -->

                        <td>


                            <div class="actions">


                                <!-- VIEW -->

                                <a
                                    href="view-appointment.php?id=<?php echo intval($appointment['id']); ?>"
                                    class="view-btn"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                    View

                                </a>



                                <?php if (
                                    $status === 'Pending'
                                ): ?>


                                    <!-- APPROVE -->

                                    <form
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?php
                                            echo intval(
                                                $appointment['id']
                                            );
                                            ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="new_status"
                                            value="Approved"
                                        >


                                        <button
                                            type="submit"
                                            name="update_status"
                                            class="action-btn approve-btn"
                                        >

                                            <i
                                                class="fa-solid
                                                fa-check"
                                            ></i>

                                            Approve

                                        </button>

                                    </form>


                                    <!-- CANCEL -->

                                    <form
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?php
                                            echo intval(
                                                $appointment['id']
                                            );
                                            ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="new_status"
                                            value="Cancelled"
                                        >


                                        <button
                                            type="submit"
                                            name="update_status"
                                            class="action-btn cancel-btn"
                                        >

                                            <i
                                                class="fa-solid
                                                fa-xmark"
                                            ></i>

                                            Cancel

                                        </button>

                                    </form>


                                <?php
                                elseif (
                                    $status === 'Approved'
                                ):
                                ?>


                                    <!-- COMPLETE -->

                                    <form
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?php
                                            echo intval(
                                                $appointment['id']
                                            );
                                            ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="new_status"
                                            value="Completed"
                                        >


                                        <button
                                            type="submit"
                                            name="update_status"
                                            class="action-btn complete-btn"
                                        >

                                            <i
                                                class="fa-solid
                                                fa-check-double"
                                            ></i>

                                            Complete

                                        </button>

                                    </form>


                                    <!-- CANCEL -->

                                    <form
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?php
                                            echo intval(
                                                $appointment['id']
                                            );
                                            ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="new_status"
                                            value="Cancelled"
                                        >


                                        <button
                                            type="submit"
                                            name="update_status"
                                            class="action-btn cancel-btn"
                                        >

                                            <i
                                                class="fa-solid
                                                fa-xmark"
                                            ></i>

                                            Cancel

                                        </button>

                                    </form>


                                <?php endif; ?>


                            </div>


                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="empty">


                <i class="fa-solid fa-calendar-xmark"></i>


                <h3>
                    No Appointments Found
                </h3>


                <p>
                    There are currently no appointments in HealthSync.
                </p>


            </div>


        <?php endif; ?>


        </div>


    </div>


</main>


</div>


</body>

</html>