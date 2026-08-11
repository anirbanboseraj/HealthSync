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
   GET APPOINTMENT ID
========================================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: appointments.php");
    exit();
}

$appointment_id = intval($_GET['id']);


/* =========================================================
   GET APPOINTMENT
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

        p.fullname AS patient_name,
        p.email AS patient_email,

        d.fullname AS doctor_name

    FROM appointments a

    LEFT JOIN users p
        ON a.patient_id = p.id

    LEFT JOIN doctors d
        ON a.doctor_id = d.id

    WHERE a.id = $appointment_id

    LIMIT 1

";


$result = mysqli_query($conn, $sql);


if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}


$appointment = mysqli_fetch_assoc($result);


if (!$appointment) {
    header("Location: appointments.php");
    exit();
}


/* =========================================================
   STATUS
========================================================= */

$status = $appointment['status'] ?? 'Pending';


$status_class = 'pending';

$status_icon = 'fa-clock';

$status_text = 'Pending';


if ($status === 'Approved') {

    $status_class = 'approved';

    $status_icon = 'fa-circle-check';

    $status_text = 'Approved';

}
elseif ($status === 'Completed') {

    $status_class = 'completed';

    $status_icon = 'fa-check-double';

    $status_text = 'Completed';

}
elseif ($status === 'Cancelled') {

    $status_class = 'cancelled';

    $status_icon = 'fa-circle-xmark';

    $status_text = 'Cancelled';

}


/* =========================================================
   FORMATTED DATE / TIME
========================================================= */

$formatted_date = date(
    "d M Y",
    strtotime($appointment['appointment_date'])
);

$formatted_time = date(
    "h:i A",
    strtotime($appointment['appointment_time'])
);

$created_date = date(
    "d M Y, h:i A",
    strtotime($appointment['created_at'])
);

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
    Appointment Details | HealthSync
</title>


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

    padding: 35px;

}


/* =========================================================
   TOP
========================================================= */

.top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 25px;

}


.heading h1 {

    color: white;

    font-size: 28px;

}


.heading p {

    color: #64748b;

    font-size: 13px;

    margin-top: 7px;

}


.back-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 10px 15px;

    border-radius: 8px;

    background: #172554;

    border: 1px solid #1e40af;

    color: #60a5fa;

    text-decoration: none;

    font-size: 12px;

    font-weight: bold;

}


.back-btn:hover {

    background: #1e3a8a;

    color: white;

}


/* =========================================================
   MAIN CARD
========================================================= */

.details-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 16px;

    overflow: hidden;

}


/* =========================================================
   CARD HEADER
========================================================= */

.card-header {

    padding: 25px;

    border-bottom: 1px solid #1e293b;

    display: flex;

    align-items: center;

    justify-content: space-between;

}


.header-left {

    display: flex;

    align-items: center;

    gap: 15px;

}


.appointment-icon {

    width: 52px;

    height: 52px;

    border-radius: 12px;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;

}


.header-left h2 {

    color: white;

    font-size: 18px;

}


.header-left p {

    color: #64748b;

    font-size: 11px;

    margin-top: 4px;

}


/* =========================================================
   STATUS
========================================================= */

.status {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 8px 14px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;

}


.status.pending {

    background: #422006;

    border: 1px solid #92400e;

    color: #fbbf24;

}


.status.approved {

    background: #052e16;

    border: 1px solid #166534;

    color: #4ade80;

}


.status.completed {

    background: #172554;

    border: 1px solid #1e40af;

    color: #60a5fa;

}


.status.cancelled {

    background: #450a0a;

    border: 1px solid #991b1b;

    color: #f87171;

}


/* =========================================================
   CONTENT
========================================================= */

.content {

    padding: 30px;

}


/* =========================================================
   SECTION
========================================================= */

.section {

    margin-bottom: 30px;

}


.section:last-child {

    margin-bottom: 0;

}


.section-title {

    display: flex;

    align-items: center;

    gap: 9px;

    color: white;

    font-size: 15px;

    margin-bottom: 15px;

}


.section-title i {

    color: #60a5fa;

}


/* =========================================================
   PEOPLE GRID
========================================================= */

.people {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;

}


.person-card {

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 12px;

    padding: 20px;

    display: flex;

    align-items: center;

    gap: 15px;

}


.person-icon {

    width: 48px;

    height: 48px;

    min-width: 48px;

    border-radius: 50%;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 19px;

}


.person-info h3 {

    color: white;

    font-size: 14px;

}


.person-info p {

    color: #64748b;

    font-size: 11px;

    margin-top: 5px;

}


.person-info .id {

    color: #60a5fa;

}


/* =========================================================
   APPOINTMENT INFO
========================================================= */

.info-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

}


.info-box {

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 10px;

    padding: 17px;

}


.info-label {

    color: #64748b;

    font-size: 10px;

    text-transform: uppercase;

    margin-bottom: 8px;

}


.info-value {

    color: #e2e8f0;

    font-size: 13px;

    font-weight: bold;

}


.info-value.blue {

    color: #60a5fa;

}


/* =========================================================
   PROBLEM
========================================================= */

.problem-box {

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 10px;

    padding: 20px;

    color: #cbd5e1;

    font-size: 13px;

    line-height: 1.7;

    min-height: 80px;

}


/* =========================================================
   ACTIONS
========================================================= */

.actions {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

}


.action-form {

    display: inline;

}


.action-btn {

    border: none;

    cursor: pointer;

    padding: 10px 16px;

    border-radius: 8px;

    font-size: 11px;

    font-weight: bold;

}


.approve {

    background: #166534;

    color: white;

}


.approve:hover {

    background: #15803d;

}


.complete {

    background: #1e40af;

    color: white;

}


.complete:hover {

    background: #2563eb;

}


.cancel {

    background: #991b1b;

    color: white;

}


.cancel:hover {

    background: #dc2626;

}


/* =========================================================
   FOOTER INFO
========================================================= */

.created {

    margin-top: 25px;

    padding-top: 20px;

    border-top: 1px solid #1e293b;

    color: #64748b;

    font-size: 10px;

}


.created i {

    margin-right: 5px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 900px) {

    .people {

        grid-template-columns: 1fr;

    }

    .info-grid {

        grid-template-columns:
            1fr 1fr;

    }

}


@media(max-width: 650px) {

    .sidebar {

        position: relative;

        width: 100%;

    }

    .dashboard {

        display: block;

    }

    .main {

        margin-left: 0;

        width: 100%;

        padding: 20px;

    }

    .top {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

    .card-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

    .info-grid {

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


    <a href="#">

        <i class="fa-solid fa-chart-line"></i>

        Reports

    </a>


    <a href="#">

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


    <!-- TOP -->

    <div class="top">


        <div class="heading">

            <h1>
                Appointment Details
            </h1>

            <p>
                Complete information about this appointment.
            </p>

        </div>


        <a
            href="appointments.php"
            class="back-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Appointments

        </a>


    </div>



    <!-- =================================================
         CARD
    ================================================== -->

    <div class="details-card">


        <!-- HEADER -->

        <div class="card-header">


            <div class="header-left">


                <div class="appointment-icon">

                    <i class="fa-solid fa-calendar-check"></i>

                </div>


                <div>

                    <h2>

                        Appointment #

                        <?php

                        echo intval(
                            $appointment['id']
                        );

                        ?>

                    </h2>


                    <p>
                        HealthSync Appointment
                    </p>

                </div>


            </div>



            <span
                class="status <?php echo $status_class; ?>"
            >

                <i
                    class="fa-solid
                    <?php echo $status_icon; ?>"
                ></i>

                <?php echo htmlspecialchars($status_text); ?>

            </span>


        </div>



        <!-- CONTENT -->

        <div class="content">


            <!-- =========================================
                 PATIENT & DOCTOR
            ========================================== -->

            <div class="section">


                <div class="section-title">

                    <i class="fa-solid fa-users"></i>

                    People

                </div>


                <div class="people">


                    <!-- PATIENT -->

                    <div class="person-card">


                        <div class="person-icon">

                            <i class="fa-solid fa-user"></i>

                        </div>


                        <div class="person-info">


                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $appointment['patient_name']
                                    ?? 'Unknown Patient'
                                );

                                ?>

                            </h3>


                            <p>

                                Patient #

                                <span class="id">

                                    <?php

                                    echo intval(
                                        $appointment['patient_id']
                                    );

                                    ?>

                                </span>

                            </p>


                            <p>

                                <?php

                                echo htmlspecialchars(
                                    $appointment['patient_email']
                                    ?? 'No email available'
                                );

                                ?>

                            </p>


                        </div>


                    </div>



                    <!-- DOCTOR -->

                    <div class="person-card">


                        <div class="person-icon">

                            <i class="fa-solid fa-user-doctor"></i>

                        </div>


                        <div class="person-info">


                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $appointment['doctor_name']
                                    ?? 'Unknown Doctor'
                                );

                                ?>

                            </h3>


                            <p>

                                Doctor #

                                <span class="id">

                                    <?php

                                    echo intval(
                                        $appointment['doctor_id']
                                    );

                                    ?>

                                </span>

                            </p>


                        </div>


                    </div>


                </div>


            </div>



            <!-- =========================================
                 APPOINTMENT INFORMATION
            ========================================== -->

            <div class="section">


                <div class="section-title">

                    <i class="fa-solid fa-calendar-days"></i>

                    Appointment Information

                </div>


                <div class="info-grid">


                    <!-- DATE -->

                    <div class="info-box">


                        <div class="info-label">
                            Appointment Date
                        </div>


                        <div class="info-value">

                            <i
                                class="fa-regular fa-calendar"
                            ></i>

                            <?php

                            echo htmlspecialchars(
                                $formatted_date
                            );

                            ?>

                        </div>


                    </div>



                    <!-- TIME -->

                    <div class="info-box">


                        <div class="info-label">
                            Appointment Time
                        </div>


                        <div class="info-value blue">

                            <i
                                class="fa-regular fa-clock"
                            ></i>

                            <?php

                            echo htmlspecialchars(
                                $formatted_time
                            );

                            ?>

                        </div>


                    </div>



                    <!-- STATUS -->

                    <div class="info-box">


                        <div class="info-label">
                            Current Status
                        </div>


                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $status_text
                            );

                            ?>

                        </div>


                    </div>


                </div>


            </div>



            <!-- =========================================
                 PROBLEM
            ========================================== -->

            <div class="section">


                <div class="section-title">

                    <i class="fa-solid fa-notes-medical"></i>

                    Patient's Problem

                </div>


                <div class="problem-box">

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

                    }
                    else {

                        echo "No problem description was provided.";

                    }

                    ?>

                </div>


            </div>



            <!-- =========================================
                 ACTIONS
            ========================================== -->

            <?php if (
                $status === 'Pending'
            ): ?>


                <div class="section">


                    <div class="section-title">

                        <i class="fa-solid fa-sliders"></i>

                        Appointment Actions

                    </div>


                    <div class="actions">


                        <!-- APPROVE -->

                        <form
                            method="POST"
                            action="appointments.php"
                            class="action-form"
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
                                class="action-btn approve"
                            >

                                <i
                                    class="fa-solid fa-check"
                                ></i>

                                Approve Appointment

                            </button>

                        </form>



                        <!-- CANCEL -->

                        <form
                            method="POST"
                            action="appointments.php"
                            class="action-form"
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
                                class="action-btn cancel"
                            >

                                <i
                                    class="fa-solid fa-xmark"
                                ></i>

                                Cancel Appointment

                            </button>

                        </form>


                    </div>


                </div>


            <?php
            elseif (
                $status === 'Approved'
            ):
            ?>


                <div class="section">


                    <div class="section-title">

                        <i class="fa-solid fa-sliders"></i>

                        Appointment Actions

                    </div>


                    <div class="actions">


                        <!-- COMPLETE -->

                        <form
                            method="POST"
                            action="appointments.php"
                            class="action-form"
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
                                class="action-btn complete"
                            >

                                <i
                                    class="fa-solid fa-check-double"
                                ></i>

                                Mark as Completed

                            </button>

                        </form>



                        <!-- CANCEL -->

                        <form
                            method="POST"
                            action="appointments.php"
                            class="action-form"
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
                                class="action-btn cancel"
                            >

                                <i
                                    class="fa-solid fa-xmark"
                                ></i>

                                Cancel Appointment

                            </button>

                        </form>


                    </div>


                </div>


            <?php endif; ?>



            <!-- CREATED -->

            <div class="created">

                <i class="fa-regular fa-clock"></i>

                Appointment created:

                <?php

                echo htmlspecialchars(
                    $created_date
                );

                ?>

            </div>


        </div>


    </div>


</main>


</div>


</body>

</html>