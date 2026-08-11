<?php

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


// =====================================================
// LOGGED-IN USER
// =====================================================

$user_id = $_SESSION['user_id'];


// =====================================================
// GET DOCTOR
// =====================================================

$doctorQuery = mysqli_query(
    $conn,
    "
    SELECT *
    FROM doctors
    WHERE user_id='$user_id'
    LIMIT 1
    "
);


if (mysqli_num_rows($doctorQuery) == 0) {
    die("Doctor profile not found.");
}


$doctor = mysqli_fetch_assoc($doctorQuery);

$doctor_id = $doctor['id'];


// =====================================================
// TOTAL APPOINTMENTS
// =====================================================

$totalAppointmentsQuery = mysqli_query(
    $conn,
    "
    SELECT id
    FROM appointments
    WHERE doctor_id='$doctor_id'
    "
);

$totalAppointments = mysqli_num_rows(
    $totalAppointmentsQuery
);


// =====================================================
// PENDING APPOINTMENTS
// =====================================================

$pendingQuery = mysqli_query(
    $conn,
    "
    SELECT id
    FROM appointments
    WHERE doctor_id='$doctor_id'
    AND status='pending'
    "
);

$pendingAppointments = mysqli_num_rows(
    $pendingQuery
);


// =====================================================
// APPROVED APPOINTMENTS
// =====================================================

$approvedQuery = mysqli_query(
    $conn,
    "
    SELECT id
    FROM appointments
    WHERE doctor_id='$doctor_id'
    AND status='approved'
    "
);

$approvedAppointments = mysqli_num_rows(
    $approvedQuery
);


// =====================================================
// TOTAL PATIENTS
// =====================================================

$patientsQuery = mysqli_query(
    $conn,
    "
    SELECT DISTINCT patient_id
    FROM appointments
    WHERE doctor_id='$doctor_id'
    "
);

$totalPatients = mysqli_num_rows(
    $patientsQuery
);


// =====================================================
// RECENT APPOINTMENTS
// =====================================================

$appointmentsQuery = mysqli_query(
    $conn,
    "
    SELECT
        appointments.*,
        users.fullname,
        users.email

    FROM appointments

    INNER JOIN users
        ON appointments.patient_id = users.id

    WHERE appointments.doctor_id='$doctor_id'

    ORDER BY
        appointments.appointment_date DESC,
        appointments.appointment_time DESC

    LIMIT 10
    "
);


// =====================================================
// PROFILE IMAGE
// =====================================================

if (!empty($doctor['image'])) {

    $doctorImage =
        "../assets/images/doctors/" .
        $doctor['image'];

} else {

    $doctorImage = "";

}

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Doctor Dashboard | HealthSync
    </title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

</head>


<body>


<div class="doctor-dashboard">


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">


    <div class="sidebar-logo">

        <h2>
            Health<span>Sync</span>
        </h2>

        <p>
            Doctor Portal
        </p>

    </div>


    <ul>


        <li class="active">

            <a href="dashboard.php">

                <i class="fa-solid fa-house"></i>

                Dashboard

            </a>

        </li>


        <li>

            <a href="appointments.php">

                <i class="fa-solid fa-calendar-check"></i>

                Appointments

            </a>

        </li>


        <li>

            <a href="prescription.php">

                <i class="fa-solid fa-file-prescription"></i>

                Prescriptions

            </a>

        </li>


        <li>

            <a href="profile.php">

                <i class="fa-solid fa-user-doctor"></i>

                My Profile

            </a>

        </li>


        <li class="logout-item">

            <a href="logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </a>

        </li>


    </ul>


</aside>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="main-content">


    <!-- TOP BAR -->

    <div class="topbar">


        <div>

            <h1>
                Doctor Dashboard
            </h1>

            <p>
                Welcome back, Doctor.
            </p>

        </div>



        <a
            href="profile.php"
            class="doctor-mini-profile"
        >


            <?php if ($doctorImage != "") { ?>

                <img
                    src="<?php echo htmlspecialchars($doctorImage); ?>"
                    alt="Doctor"
                >

            <?php } else { ?>

                <div class="mini-avatar">

                    <i class="fa-solid fa-user-doctor"></i>

                </div>

            <?php } ?>


            <div>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $doctor['fullname']
                    );
                    ?>

                </strong>

                <span>

                    <?php
                    echo htmlspecialchars(
                        $doctor['specialization']
                    );
                    ?>

                </span>

            </div>


        </a>


    </div>



    <!-- =================================================
         WELCOME CARD
    ================================================== -->

    <div class="welcome-card">


        <div>

            <span class="small-title">
                HEALTHSYNC DOCTOR PORTAL
            </span>


            <h2>

                Hello,
                <?php
                echo htmlspecialchars(
                    $doctor['fullname']
                );
                ?>

                👋

            </h2>


            <p>

                Manage your appointments,
                patients and professional profile
                from one place.

            </p>


        </div>


        <i class="fa-solid fa-user-doctor welcome-icon"></i>


    </div>



    <!-- =================================================
         STATISTICS
    ================================================== -->

    <div class="stats-grid">


        <div class="stat-card">


            <div class="stat-icon blue">

                <i class="fa-solid fa-calendar-check"></i>

            </div>


            <div>

                <span>
                    Total Appointments
                </span>

                <strong>
                    <?php echo $totalAppointments; ?>
                </strong>

            </div>


        </div>



        <div class="stat-card">


            <div class="stat-icon orange">

                <i class="fa-solid fa-clock"></i>

            </div>


            <div>

                <span>
                    Pending
                </span>

                <strong>
                    <?php echo $pendingAppointments; ?>
                </strong>

            </div>


        </div>



        <div class="stat-card">


            <div class="stat-icon green">

                <i class="fa-solid fa-circle-check"></i>

            </div>


            <div>

                <span>
                    Approved
                </span>

                <strong>
                    <?php echo $approvedAppointments; ?>
                </strong>

            </div>


        </div>



        <div class="stat-card">


            <div class="stat-icon purple">

                <i class="fa-solid fa-users"></i>

            </div>


            <div>

                <span>
                    Patients
                </span>

                <strong>
                    <?php echo $totalPatients; ?>
                </strong>

            </div>


        </div>


    </div>



    <!-- =================================================
         APPOINTMENTS
    ================================================== -->

    <div class="appointments-card">


        <div class="section-header">


            <div>

                <h2>
                    Recent Appointments
                </h2>

                <p>
                    Manage your latest patient requests.
                </p>

            </div>


            <a href="appointments.php">

                View All

                <i class="fa-solid fa-arrow-right"></i>

            </a>


        </div>



        <?php if (mysqli_num_rows($appointmentsQuery) > 0) { ?>


        <div class="table-container">


            <table>


                <thead>

                    <tr>

                        <th>Patient</th>

                        <th>Date</th>

                        <th>Time</th>

                        <th>Problem</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                <?php while (
                    $row =
                    mysqli_fetch_assoc(
                        $appointmentsQuery
                    )
                ) { ?>


                    <tr>


                        <!-- PATIENT -->

                        <td>

                            <div class="patient-cell">


                                <div class="patient-avatar">

                                    <i class="fa-solid fa-user"></i>

                                </div>


                                <div>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $row['fullname']
                                        );
                                        ?>

                                    </strong>

                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            $row['email']
                                        );
                                        ?>

                                    </small>

                                </div>


                            </div>

                        </td>



                        <!-- DATE -->

                        <td>

                            <?php
                            echo date(
                                "d M Y",
                                strtotime(
                                    $row['appointment_date']
                                )
                            );
                            ?>

                        </td>



                        <!-- TIME -->

                        <td>

                            <?php
                            echo date(
                                "h:i A",
                                strtotime(
                                    $row['appointment_time']
                                )
                            );
                            ?>

                        </td>



                        <!-- PROBLEM -->

                        <td>

                            <span class="problem">

                                <?php
                                echo htmlspecialchars(
                                    $row['problem']
                                );
                                ?>

                            </span>

                        </td>



                        <!-- STATUS -->

                        <td>


                            <?php

                            $status =
                                strtolower(
                                    $row['status']
                                );

                            ?>


                            <span
                                class="status <?php echo $status; ?>"
                            >

                                <?php
                                echo ucfirst(
                                    $status
                                );
                                ?>

                            </span>


                        </td>



                        <!-- ACTION -->

                        <td>


                            <?php if ($status == 'pending') { ?>


                                <div class="action-buttons">


                                    <a
                                        href="approve.php?id=<?php echo $row['id']; ?>"
                                        class="approve-btn"
                                    >

                                        <i class="fa-solid fa-check"></i>

                                        Approve

                                    </a>


                                    <a
                                        href="reject.php?id=<?php echo $row['id']; ?>"
                                        class="reject-btn"
                                    >

                                        <i class="fa-solid fa-xmark"></i>

                                        Reject

                                    </a>


                                </div>


                            <?php } else { ?>


                                <span class="no-action">

                                    No action

                                </span>


                            <?php } ?>


                        </td>


                    </tr>


                <?php } ?>


                </tbody>


            </table>


        </div>


        <?php } else { ?>


            <div class="empty-state">


                <i class="fa-solid fa-calendar-xmark"></i>


                <h3>
                    No appointments yet
                </h3>


                <p>
                    Patient appointments will appear here.
                </p>


            </div>


        <?php } ?>


    </div>


</main>


</div>



<style>

/* =====================================================
   RESET
===================================================== */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


body {

    font-family: Arial, sans-serif;

    background: #020617;

}


/* =====================================================
   MAIN LAYOUT
===================================================== */

.doctor-dashboard {

    display: flex;

    min-height: 100vh;

    background: #020617;

    color: #e2e8f0;

}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    width: 250px;

    min-height: 100vh;

    background: #0f172a;

    border-right: 1px solid #1e293b;

    padding: 25px 15px;

}


.sidebar-logo {

    text-align: center;

    margin-bottom: 35px;

}


.sidebar-logo h2 {

    color: white;

    font-size: 25px;

}


.sidebar-logo h2 span {

    color: #3b82f6;

}


.sidebar-logo p {

    margin-top: 5px;

    color: #64748b;

    font-size: 12px;

}


.sidebar ul {

    list-style: none;

}


.sidebar li {

    margin-bottom: 7px;

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    color: #94a3b8;

    text-decoration: none;

    border-radius: 8px;

    transition: .25s;

}


.sidebar a:hover,
.sidebar li.active a {

    background: #1e3a8a;

    color: white;

}


.sidebar a i {

    width: 20px;

}


.logout-item {

    margin-top: 35px;

}


/* =====================================================
   MAIN CONTENT
===================================================== */

.main-content {

    flex: 1;

    padding: 30px 40px;

    overflow-x: auto;

}


/* =====================================================
   TOP BAR
===================================================== */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.topbar h1 {

    color: white;

    font-size: 28px;

}


.topbar p {

    color: #64748b;

    margin-top: 5px;

}


.doctor-mini-profile {

    display: flex;

    align-items: center;

    gap: 12px;

    text-decoration: none;

    color: white;

}


.doctor-mini-profile img,
.mini-avatar {

    width: 48px;

    height: 48px;

    border-radius: 50%;

}


.doctor-mini-profile img {

    object-fit: cover;

    border: 2px solid #3b82f6;

}


.mini-avatar {

    background: #1e3a8a;

    display: flex;

    justify-content: center;

    align-items: center;

    color: #93c5fd;

}


.doctor-mini-profile strong {

    display: block;

    font-size: 14px;

}


.doctor-mini-profile span {

    display: block;

    color: #64748b;

    font-size: 12px;

    margin-top: 3px;

}


/* =====================================================
   WELCOME CARD
===================================================== */

.welcome-card {

    background:
        linear-gradient(
            135deg,
            #172554,
            #1e3a8a
        );

    border: 1px solid #1d4ed8;

    border-radius: 15px;

    padding: 28px 32px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.small-title {

    color: #93c5fd;

    font-size: 11px;

    font-weight: bold;

    letter-spacing: 1px;

}


.welcome-card h2 {

    color: white;

    font-size: 25px;

    margin: 8px 0;

}


.welcome-card p {

    color: #bfdbfe;

}


.welcome-icon {

    font-size: 70px;

    color: #60a5fa;

    opacity: .5;

}


/* =====================================================
   STATS
===================================================== */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 25px;

}


.stat-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 12px;

    padding: 20px;

    display: flex;

    align-items: center;

    gap: 15px;

}


.stat-icon {

    width: 50px;

    height: 50px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

}


.stat-icon.blue {

    background: #172554;

    color: #60a5fa;

}


.stat-icon.orange {

    background: #431407;

    color: #fb923c;

}


.stat-icon.green {

    background: #052e16;

    color: #4ade80;

}


.stat-icon.purple {

    background: #2e1065;

    color: #c084fc;

}


.stat-card span {

    display: block;

    color: #64748b;

    font-size: 12px;

}


.stat-card strong {

    display: block;

    color: white;

    font-size: 25px;

    margin-top: 5px;

}


/* =====================================================
   APPOINTMENTS CARD
===================================================== */

.appointments-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    overflow: hidden;

}


.section-header {

    padding: 22px 25px;

    border-bottom: 1px solid #1e293b;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.section-header h2 {

    color: white;

    font-size: 19px;

}


.section-header p {

    color: #64748b;

    font-size: 13px;

    margin-top: 5px;

}


.section-header a {

    color: #60a5fa;

    text-decoration: none;

    font-size: 13px;

}


.section-header a i {

    margin-left: 5px;

}


/* =====================================================
   TABLE
===================================================== */

.table-container {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

}


th {

    text-align: left;

    padding: 15px 20px;

    background: #111827;

    color: #64748b;

    font-size: 12px;

    white-space: nowrap;

}


td {

    padding: 16px 20px;

    border-top: 1px solid #1e293b;

    color: #cbd5e1;

    font-size: 13px;

    white-space: nowrap;

}


tr:hover td {

    background: #111827;

}


/* =====================================================
   PATIENT
===================================================== */

.patient-cell {

    display: flex;

    align-items: center;

    gap: 10px;

}


.patient-avatar {

    width: 38px;

    height: 38px;

    border-radius: 50%;

    background: #172554;

    color: #60a5fa;

    display: flex;

    justify-content: center;

    align-items: center;

}


.patient-cell strong {

    display: block;

    color: white;

}


.patient-cell small {

    display: block;

    color: #64748b;

    margin-top: 3px;

}


/* =====================================================
   PROBLEM
===================================================== */

.problem {

    color: #cbd5e1;

    max-width: 160px;

    display: inline-block;

    overflow: hidden;

    text-overflow: ellipsis;

}


/* =====================================================
   STATUS
===================================================== */

.status {

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;

}


.status.pending {

    background: #431407;

    color: #fb923c;

}


.status.approved {

    background: #052e16;

    color: #4ade80;

}


.status.rejected {

    background: #450a0a;

    color: #f87171;

}


/* =====================================================
   ACTION BUTTONS
===================================================== */

.action-buttons {

    display: flex;

    gap: 7px;

}


.approve-btn,
.reject-btn {

    padding: 7px 10px;

    border-radius: 6px;

    text-decoration: none;

    font-size: 11px;

    font-weight: bold;

}


.approve-btn {

    background: #065f46;

    color: #6ee7b7;

}


.approve-btn:hover {

    background: #047857;

}


.reject-btn {

    background: #7f1d1d;

    color: #fca5a5;

}


.reject-btn:hover {

    background: #991b1b;

}


.no-action {

    color: #475569;

    font-size: 11px;

}


/* =====================================================
   EMPTY STATE
===================================================== */

.empty-state {

    padding: 60px 20px;

    text-align: center;

}


.empty-state i {

    color: #334155;

    font-size: 45px;

}


.empty-state h3 {

    color: #cbd5e1;

    margin-top: 15px;

}


.empty-state p {

    color: #64748b;

    margin-top: 7px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:1000px) {

    .stats-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width:700px) {

    .doctor-dashboard {

        flex-direction: column;

    }


    .sidebar {

        width: 100%;

        min-height: auto;

    }


    .sidebar ul {

        display: flex;

        flex-wrap: wrap;

        gap: 5px;

    }


    .sidebar li {

        margin: 0;

    }


    .logout-item {

        margin-top: 0;

    }


    .main-content {

        padding: 20px;

    }


    .topbar {

        align-items: flex-start;

        gap: 15px;

    }


    .welcome-icon {

        display: none;

    }

}


@media(max-width:500px) {

    .stats-grid {

        grid-template-columns: 1fr;

    }


    .topbar {

        flex-direction: column;

    }

}

</style>


</body>

</html>