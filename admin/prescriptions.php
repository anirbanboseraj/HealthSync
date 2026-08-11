<?php

session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit();
}

require_once("../config/database.php");


/* =========================================================
   GET PRESCRIPTIONS
========================================================= */

$sql = "
    SELECT
        p.id,
        p.appointment_id,
        p.diagnosis,
        p.medicines,
        p.advice,
        p.created_at,

        a.patient_id,
        a.doctor_id,
        a.appointment_date,

        u.fullname AS patient_name,
        u.email AS patient_email,

        d.fullname AS doctor_name

    FROM prescriptions p

    LEFT JOIN appointments a
        ON p.appointment_id = a.id

    LEFT JOIN users u
        ON a.patient_id = u.id

    LEFT JOIN doctors d
        ON a.doctor_id = d.id

    ORDER BY p.id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}


/* =========================================================
   COUNT
========================================================= */

$count_sql = "SELECT COUNT(*) AS total FROM prescriptions";

$count_result = mysqli_query($conn, $count_sql);

$total_prescriptions = 0;

if ($count_result) {

    $count_row = mysqli_fetch_assoc($count_result);

    $total_prescriptions = intval($count_row['total']);
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

<title>Prescriptions | HealthSync Admin</title>


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
   HEADER
========================================================= */

.page-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

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


/* =========================================================
   STAT CARD
========================================================= */

.stat-card {

    width: 250px;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 14px;

    padding: 20px;

    margin-bottom: 25px;

}


.stat-icon {

    width: 45px;

    height: 45px;

    border-radius: 10px;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 19px;

    margin-bottom: 14px;

}


.stat-number {

    font-size: 27px;

    color: white;

    font-weight: bold;

}


.stat-label {

    color: #64748b;

    font-size: 12px;

    margin-top: 5px;

}


/* =========================================================
   TABLE CARD
========================================================= */

.table-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    overflow: hidden;

}


.table-header {

    padding: 22px;

    border-bottom: 1px solid #1e293b;

}


.table-header h2 {

    color: white;

    font-size: 17px;

}


.table-header p {

    color: #64748b;

    font-size: 11px;

    margin-top: 5px;

}


/* =========================================================
   TABLE
========================================================= */

.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 900px;

}


thead {

    background: #111827;

}


th {

    padding: 15px;

    text-align: left;

    color: #64748b;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    border-bottom: 1px solid #1e293b;

}


td {

    padding: 17px 15px;

    border-bottom: 1px solid #1e293b;

    font-size: 12px;

    color: #cbd5e1;

}


tbody tr:hover {

    background: #111827;

}


tbody tr:last-child td {

    border-bottom: none;

}


/* =========================================================
   PATIENT
========================================================= */

.patient {

    display: flex;

    align-items: center;

    gap: 10px;

}


.patient-icon {

    width: 36px;

    height: 36px;

    border-radius: 50%;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

}


.patient-name {

    color: white;

    font-weight: bold;

}


.patient-email {

    color: #64748b;

    font-size: 10px;

    margin-top: 3px;

}


/* =========================================================
   DOCTOR
========================================================= */

.doctor {

    color: #93c5fd;

    font-weight: bold;

}


/* =========================================================
   DIAGNOSIS
========================================================= */

.diagnosis {

    max-width: 180px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}


/* =========================================================
   DATE
========================================================= */

.date {

    color: #cbd5e1;

}


.appointment-id {

    color: #60a5fa;

    font-weight: bold;

}


/* =========================================================
   VIEW BUTTON
========================================================= */

.view-btn {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 9px 13px;

    border-radius: 7px;

    background: #172554;

    border: 1px solid #1e40af;

    color: #60a5fa;

    text-decoration: none;

    font-size: 10px;

    font-weight: bold;

}


.view-btn:hover {

    background: #1e3a8a;

    color: white;

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding: 60px 20px;

}


.empty i {

    font-size: 45px;

    color: #334155;

    margin-bottom: 15px;

}


.empty h3 {

    color: #94a3b8;

    font-size: 16px;

}


.empty p {

    color: #64748b;

    font-size: 11px;

    margin-top: 5px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 700px) {

    .sidebar {

        width: 200px;

    }

    .main {

        margin-left: 200px;

        width: calc(100% - 200px);

        padding: 20px;

    }

    .page-header {

        align-items: flex-start;

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


    <a href="appointments.php">

        <i class="fa-solid fa-calendar-check"></i>

        Appointments

    </a>


    <a
        href="prescriptions.php"
        class="active"
    >

        <i class="fa-solid fa-file-prescription"></i>

        Prescriptions

    </a>



    <a
    href="/HealthSync/admin/reports.php"
>

    <i class="fa-solid fa-chart-line"></i>

    Reports

</a>


<a
    href="/HealthSync/admin/settings.php"
>

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


    <!-- HEADER -->

    <div class="page-header">


        <div class="page-title">

            <h1>
                Prescription Management
            </h1>

            <p>
                View prescriptions created by HealthSync doctors.
            </p>

        </div>


    </div>



    <!-- =================================================
         STAT
    ================================================== -->

    <div class="stat-card">


        <div class="stat-icon">

            <i class="fa-solid fa-file-prescription"></i>

        </div>


        <div class="stat-number">

            <?php echo $total_prescriptions; ?>

        </div>


        <div class="stat-label">

            Total Prescriptions

        </div>


    </div>



    <!-- =================================================
         TABLE
    ================================================== -->

    <div class="table-card">


        <div class="table-header">

            <h2>
                All Prescriptions
            </h2>

            <p>
                Prescriptions created for registered patients.
            </p>

        </div>



        <?php if (mysqli_num_rows($result) > 0): ?>


            <div class="table-wrapper">


                <table>


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Patient
                            </th>

                            <th>
                                Doctor
                            </th>

                            <th>
                                Appointment
                            </th>

                            <th>
                                Diagnosis
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $row = mysqli_fetch_assoc($result)
                    ): ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <span class="appointment-id">

                                    #<?php
                                    echo intval(
                                        $row['id']
                                    );
                                    ?>

                                </span>

                            </td>



                            <!-- PATIENT -->

                            <td>


                                <div class="patient">


                                    <div class="patient-icon">

                                        <i
                                            class="fa-solid fa-user"
                                        ></i>

                                    </div>


                                    <div>


                                        <div class="patient-name">

                                            <?php

                                            echo htmlspecialchars(
                                                $row['patient_name']
                                                ?? 'Unknown Patient'
                                            );

                                            ?>

                                        </div>


                                        <div class="patient-email">

                                            <?php

                                            echo htmlspecialchars(
                                                $row['patient_email']
                                                ?? 'No email'
                                            );

                                            ?>

                                        </div>


                                    </div>


                                </div>


                            </td>



                            <!-- DOCTOR -->

                            <td>

                                <span class="doctor">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['doctor_name']
                                        ?? 'Unknown Doctor'
                                    );

                                    ?>

                                </span>

                            </td>



                            <!-- APPOINTMENT -->

                            <td>

                                <span class="appointment-id">

                                    #

                                    <?php

                                    echo intval(
                                        $row['appointment_id']
                                    );

                                    ?>

                                </span>

                            </td>



                            <!-- DIAGNOSIS -->

                            <td>

                                <div class="diagnosis">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['diagnosis']
                                        ?? 'Not provided'
                                    );

                                    ?>

                                </div>

                            </td>



                            <!-- DATE -->

                            <td>

                                <span class="date">

                                    <?php

                                    if (
                                        !empty(
                                            $row['created_at']
                                        )
                                    ) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $row['created_at']
                                            )
                                        );

                                    }
                                    else {

                                        echo "N/A";

                                    }

                                    ?>

                                </span>

                            </td>



                            <!-- VIEW -->

                            <td>

                                <a
                                    href="view-prescription.php?id=<?php
                                    echo intval(
                                        $row['id']
                                    );
                                    ?>"
                                    class="view-btn"
                                >

                                    <i
                                        class="fa-solid fa-eye"
                                    ></i>

                                    View

                                </a>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <div class="empty">


                <i
                    class="fa-solid fa-file-prescription"
                ></i>


                <h3>
                    No prescriptions found
                </h3>


                <p>
                    Doctor prescriptions will appear here.
                </p>


            </div>


        <?php endif; ?>


    </div>


</main>


</div>


</body>

</html>