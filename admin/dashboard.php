<?php

session_start();


// =====================================================
// ADMIN LOGIN CHECK
// =====================================================

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'admin'
) {
    header("Location: ../login.php");
    exit();
}


// =====================================================
// DATABASE
// =====================================================

include("../config/database.php");


// =====================================================
// ADMIN INFORMATION
// =====================================================

$admin_name = $_SESSION['fullname'];


// =====================================================
// DASHBOARD STATISTICS
// =====================================================


// Total Doctors

$doctorQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM doctors"
);

$doctorData = mysqli_fetch_assoc($doctorQuery);

$totalDoctors = $doctorData['total'];


// Total Patients

$patientQuery = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM users
    WHERE role='patient'
    "
);

$patientData = mysqli_fetch_assoc($patientQuery);

$totalPatients = $patientData['total'];


// Total Appointments

$appointmentQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM appointments"
);

$appointmentData = mysqli_fetch_assoc(
    $appointmentQuery
);

$totalAppointments = $appointmentData['total'];


// Pending Appointments

$pendingQuery = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM appointments
    WHERE status='pending'
    "
);

$pendingData = mysqli_fetch_assoc(
    $pendingQuery
);

$pendingAppointments = $pendingData['total'];


// Total Prescriptions

$prescriptionQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM prescriptions"
);

$prescriptionData = mysqli_fetch_assoc(
    $prescriptionQuery
);

$totalPrescriptions = $prescriptionData['total'];

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
        Admin Dashboard | HealthSync
    </title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >


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

    color: #e2e8f0;

}


/* =====================================================
   DASHBOARD
===================================================== */

.dashboard {

    display: flex;

    min-height: 100vh;

}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    width: 250px;

    background: #0f172a;

    border-right: 1px solid #1e293b;

    position: fixed;

    left: 0;

    top: 0;

    bottom: 0;

    padding: 25px 15px;

}


/* LOGO */

.logo {

    text-align: center;

    margin-bottom: 35px;

}


.logo h2 {

    color: white;

    font-size: 24px;

}


.logo span {

    color: #3b82f6;

}


/* SIDEBAR LINKS */

.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    margin-bottom: 6px;

    color: #94a3b8;

    text-decoration: none;

    border-radius: 8px;

    font-size: 14px;

    transition: 0.2s;

}


.sidebar a i {

    width: 20px;

    text-align: center;

}


.sidebar a:hover {

    background: #172554;

    color: #60a5fa;

}


.sidebar a.active {

    background: #1e3a8a;

    color: white;

}


/* LOGOUT */

.sidebar .logout {

    margin-top: 30px;

    color: #f87171;

}


.sidebar .logout:hover {

    background: #450a0a;

    color: #fca5a5;

}


/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 250px;

    width: calc(100% - 250px);

    padding: 30px;

}


/* =====================================================
   TOP BAR
===================================================== */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 30px;

}


.welcome h1 {

    font-size: 27px;

    color: white;

}


.welcome p {

    margin-top: 7px;

    color: #64748b;

    font-size: 13px;

}


/* ADMIN PROFILE */

.admin-profile {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 9px 14px;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 10px;

}


.admin-icon {

    width: 35px;

    height: 35px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

}


.admin-profile strong {

    color: white;

    font-size: 13px;

}


.admin-profile small {

    display: block;

    color: #64748b;

    font-size: 10px;

    margin-top: 3px;

}


/* =====================================================
   STAT CARDS
===================================================== */

.stats {

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

    padding: 22px;

    display: flex;

    align-items: center;

    gap: 15px;

    transition: 0.2s;

}


.stat-card:hover {

    transform: translateY(-2px);

    border-color: #334155;

}


.stat-icon {

    width: 48px;

    height: 48px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

    font-size: 20px;

}


.stat-card h3 {

    color: white;

    font-size: 24px;

}


.stat-card p {

    color: #64748b;

    font-size: 11px;

    margin-top: 4px;

}


/* =====================================================
   CONTENT CARDS
===================================================== */

.content-grid {

    display: grid;

    grid-template-columns:
        2fr 1fr;

    gap: 20px;

}


.panel {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 12px;

    padding: 22px;

}


.panel-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

}


.panel-header h2 {

    color: white;

    font-size: 17px;

}


.panel-header i {

    color: #60a5fa;

}


/* =====================================================
   QUICK ACTIONS
===================================================== */

.quick-actions {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 12px;

}


.action {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 15px;

    background: #020617;

    border: 1px solid #1e293b;

    border-radius: 9px;

    text-decoration: none;

    color: #cbd5e1;

    transition: 0.2s;

}


.action:hover {

    border-color: #2563eb;

    background: #0b1220;

    color: white;

}


.action-icon {

    width: 38px;

    height: 38px;

    border-radius: 8px;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

}


.action strong {

    display: block;

    font-size: 12px;

}


.action span {

    display: block;

    font-size: 10px;

    color: #64748b;

    margin-top: 3px;

}


/* =====================================================
   SUMMARY
===================================================== */

.summary-item {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 13px 0;

    border-bottom: 1px solid #1e293b;

}


.summary-item:last-child {

    border-bottom: none;

}


.summary-item span {

    color: #94a3b8;

    font-size: 12px;

}


.summary-item strong {

    color: #60a5fa;

    font-size: 15px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:1000px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width:750px) {

    .sidebar {

        width: 210px;

    }


    .main {

        margin-left: 210px;

        width: calc(100% - 210px);

    }


    .content-grid {

        grid-template-columns: 1fr;

    }

}


@media(max-width:600px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        border-right: none;

        border-bottom: 1px solid #1e293b;

    }


    .dashboard {

        display: block;

    }


    .main {

        margin-left: 0;

        width: 100%;

        padding: 20px;

    }


    .stats {

        grid-template-columns: 1fr;

    }


    .topbar {

        align-items: flex-start;

        flex-direction: column;

        gap: 15px;

    }

}

</style>

</head>


<body>


<div class="dashboard">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside class="sidebar">


        <div class="logo">

            <h2>
                Health<span>Sync</span>
            </h2>

        </div>


        <a
            href="dashboard.php"
            class="active"
        >

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


        <a href="appointments.php">

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



    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="main">


        <!-- TOP BAR -->

        <div class="topbar">


            <div class="welcome">

                <h1>
                    Admin Dashboard
                </h1>

                <p>
                    Welcome back. Manage your HealthSync system.
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
                            $admin_name
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


            <!-- DOCTORS -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-user-doctor"></i>

                </div>


                <div>

                    <h3>
                        <?php echo $totalDoctors; ?>
                    </h3>

                    <p>
                        Total Doctors
                    </p>

                </div>

            </div>



            <!-- PATIENTS -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-users"></i>

                </div>


                <div>

                    <h3>
                        <?php echo $totalPatients; ?>
                    </h3>

                    <p>
                        Total Patients
                    </p>

                </div>

            </div>



            <!-- APPOINTMENTS -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-calendar-check"></i>

                </div>


                <div>

                    <h3>
                        <?php echo $totalAppointments; ?>
                    </h3>

                    <p>
                        Total Appointments
                    </p>

                </div>

            </div>



            <!-- PRESCRIPTIONS -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-file-prescription"></i>

                </div>


                <div>

                    <h3>
                        <?php echo $totalPrescriptions; ?>
                    </h3>

                    <p>
                        Prescriptions
                    </p>

                </div>

            </div>


        </div>



        <!-- =================================================
             LOWER CONTENT
        ================================================== -->

        <div class="content-grid">


            <!-- QUICK ACTIONS -->

            <div class="panel">


                <div class="panel-header">

                    <h2>
                        Quick Management
                    </h2>

                    <i class="fa-solid fa-bolt"></i>

                </div>


                <div class="quick-actions">


                    <a
                        href="doctors.php"
                        class="action"
                    >

                        <div class="action-icon">

                            <i class="fa-solid fa-user-doctor"></i>

                        </div>


                        <div>

                            <strong>
                                Manage Doctors
                            </strong>

                            <span>
                                View and manage doctors
                            </span>

                        </div>

                    </a>



                    <a
                        href="patients.php"
                        class="action"
                    >

                        <div class="action-icon">

                            <i class="fa-solid fa-users"></i>

                        </div>


                        <div>

                            <strong>
                                Manage Patients
                            </strong>

                            <span>
                                View registered patients
                            </span>

                        </div>

                    </a>



                    <a
                        href="appointments.php"
                        class="action"
                    >

                        <div class="action-icon">

                            <i class="fa-solid fa-calendar-check"></i>

                        </div>


                        <div>

                            <strong>
                                Appointments
                            </strong>

                            <span>
                                Monitor appointments
                            </span>

                        </div>

                    </a>



                    <a
                        href="prescriptions.php"
                        class="action"
                    >

                        <div class="action-icon">

                            <i class="fa-solid fa-file-prescription"></i>

                        </div>


                        <div>

                            <strong>
                                Prescriptions
                            </strong>

                            <span>
                                View prescriptions
                            </span>

                        </div>

                    </a>


                </div>


            </div>



            <!-- SUMMARY -->

            <div class="panel">


                <div class="panel-header">

                    <h2>
                        System Summary
                    </h2>

                    <i class="fa-solid fa-chart-pie"></i>

                </div>


                <div class="summary-item">

                    <span>
                        Doctors
                    </span>

                    <strong>
                        <?php echo $totalDoctors; ?>
                    </strong>

                </div>


                <div class="summary-item">

                    <span>
                        Patients
                    </span>

                    <strong>
                        <?php echo $totalPatients; ?>
                    </strong>

                </div>


                <div class="summary-item">

                    <span>
                        Appointments
                    </span>

                    <strong>
                        <?php echo $totalAppointments; ?>
                    </strong>

                </div>


                <div class="summary-item">

                    <span>
                        Pending
                    </span>

                    <strong>
                        <?php echo $pendingAppointments; ?>
                    </strong>

                </div>


                <div class="summary-item">

                    <span>
                        Prescriptions
                    </span>

                    <strong>
                        <?php echo $totalPrescriptions; ?>
                    </strong>

                </div>


            </div>


        </div>


    </main>


</div>


</body>

</html>