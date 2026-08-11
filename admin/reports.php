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
   HELPER FUNCTION
========================================================= */

function getCount($conn, $sql)
{
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return intval($row['total']);
}


/* =========================================================
   TOTAL COUNTS
========================================================= */

/* Doctors */

$total_doctors = getCount(
    $conn,
    "SELECT COUNT(*) AS total FROM doctors"
);


/* Patients */

$total_patients = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'patient'"
);


/* Appointments */

$total_appointments = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointments"
);


/* Prescriptions */

$total_prescriptions = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM prescriptions"
);


/* =========================================================
   APPOINTMENT STATUS
========================================================= */

/* Pending */

$pending = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE status = 'Pending'"
);


/* Approved */

$approved = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE status = 'Approved'"
);


/* Completed */

$completed = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE status = 'Completed'"
);


/* Cancelled */

$cancelled = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE status = 'Cancelled'"
);


/* =========================================================
   MONTHLY APPOINTMENTS
========================================================= */

$monthly_sql = "

    SELECT

        DATE_FORMAT(
            appointment_date,
            '%b'
        ) AS month_name,

        MONTH(
            appointment_date
        ) AS month_number,

        COUNT(*) AS total

    FROM appointments

    WHERE appointment_date >=
          DATE_SUB(
              CURDATE(),
              INTERVAL 5 MONTH
          )

    GROUP BY

        YEAR(appointment_date),

        MONTH(appointment_date)

    ORDER BY

        YEAR(appointment_date),

        MONTH(appointment_date)

";


$monthly_result = mysqli_query(
    $conn,
    $monthly_sql
);


/* =========================================================
   STORE MONTHLY DATA
========================================================= */

$months = [];

$monthly_counts = [];


if ($monthly_result) {

    while (
        $row = mysqli_fetch_assoc(
            $monthly_result
        )
    ) {

        $months[] =
            $row['month_name'];

        $monthly_counts[] =
            intval($row['total']);

    }

}


/* =========================================================
   RECENT APPOINTMENTS
========================================================= */

$recent_appointments_sql = "

    SELECT

        a.id,

        a.appointment_date,

        a.appointment_time,

        a.status,

        a.problem,

        u.fullname AS patient_name,

        d.fullname AS doctor_name

    FROM appointments a

    LEFT JOIN users u
        ON a.patient_id = u.id

    LEFT JOIN doctors d
        ON a.doctor_id = d.id

    ORDER BY a.id DESC

    LIMIT 5

";


$recent_appointments_result =
    mysqli_query(
        $conn,
        $recent_appointments_sql
    );


/* =========================================================
   RECENT PRESCRIPTIONS
========================================================= */

$recent_prescriptions_sql = "

    SELECT

        p.id,

        p.created_at,

        p.diagnosis,

        u.fullname AS patient_name,

        d.fullname AS doctor_name

    FROM prescriptions p

    LEFT JOIN users u
        ON p.patient_id = u.id

    LEFT JOIN doctors d
        ON p.doctor_id = d.id

    ORDER BY p.id DESC

    LIMIT 5

";


$recent_prescriptions_result =
    mysqli_query(
        $conn,
        $recent_prescriptions_sql
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
    Reports | HealthSync
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

    left: 0;

    top: 0;

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

.header {

    margin-bottom: 30px;

}


.header h1 {

    color: white;

    font-size: 28px;

}


.header p {

    color: #64748b;

    font-size: 13px;

    margin-top: 7px;

}


/* =========================================================
   STAT CARDS
========================================================= */

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

    border-radius: 14px;

    padding: 22px;

    min-height: 145px;

    position: relative;

    overflow: hidden;

}


.stat-icon {

    width: 45px;

    height: 45px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

    font-size: 19px;

    margin-bottom: 15px;

}


.stat-card h2 {

    color: white;

    font-size: 28px;

}


.stat-card p {

    color: #64748b;

    font-size: 11px;

    margin-top: 5px;

}


.stat-card::after {

    content: "";

    position: absolute;

    width: 80px;

    height: 80px;

    border-radius: 50%;

    right: -30px;

    bottom: -30px;

    background: rgba(
        59,
        130,
        246,
        0.06
    );

}


/* =========================================================
   STATUS CARDS
========================================================= */

.status-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 25px;

}


.status-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 12px;

    padding: 18px;

    display: flex;

    align-items: center;

    gap: 14px;

}


.status-icon {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

}


.status-icon.pending {

    background: #422006;

    color: #f59e0b;

}


.status-icon.approved {

    background: #172554;

    color: #60a5fa;

}


.status-icon.completed {

    background: #052e16;

    color: #22c55e;

}


.status-icon.cancelled {

    background: #450a0a;

    color: #f87171;

}


.status-info h3 {

    color: white;

    font-size: 20px;

}


.status-info p {

    color: #64748b;

    font-size: 10px;

    margin-top: 4px;

}


/* =========================================================
   GRID
========================================================= */

.report-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 20px;

    margin-bottom: 25px;

}


/* =========================================================
   REPORT CARD
========================================================= */

.report-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 14px;

    overflow: hidden;

}


.report-header {

    padding: 20px;

    border-bottom: 1px solid #1e293b;

}


.report-header h2 {

    color: white;

    font-size: 15px;

}


.report-header p {

    color: #64748b;

    font-size: 10px;

    margin-top: 5px;

}


.report-body {

    padding: 20px;

}


/* =========================================================
   BAR CHART
========================================================= */

.chart {

    height: 260px;

    display: flex;

    align-items: flex-end;

    justify-content: space-around;

    gap: 12px;

    padding: 20px 10px 30px;

    border-bottom: 1px solid #1e293b;

    position: relative;

}


.bar-container {

    height: 100%;

    flex: 1;

    display: flex;

    flex-direction: column;

    justify-content: flex-end;

    align-items: center;

    gap: 8px;

}


.bar {

    width: 45px;

    max-width: 80%;

    min-height: 4px;

    border-radius:
        7px 7px 0 0;

    background: #2563eb;

    transition: 0.3s;

}


.bar:hover {

    background: #60a5fa;

}


.bar-value {

    color: #94a3b8;

    font-size: 10px;

}


.bar-label {

    color: #64748b;

    font-size: 10px;

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

}


th {

    text-align: left;

    color: #64748b;

    font-size: 10px;

    text-transform: uppercase;

    padding: 12px;

    border-bottom: 1px solid #1e293b;

}


td {

    color: #cbd5e1;

    font-size: 11px;

    padding: 13px 12px;

    border-bottom: 1px solid #1e293b;

}


tr:last-child td {

    border-bottom: none;

}


.patient-name {

    color: white;

    font-weight: bold;

}


.doctor-name {

    color: #60a5fa;

}


/* =========================================================
   STATUS BADGE
========================================================= */

.badge {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 9px;

    font-weight: bold;

}


.badge.pending {

    background: #422006;

    color: #fbbf24;

}


.badge.approved {

    background: #172554;

    color: #60a5fa;

}


.badge.completed {

    background: #052e16;

    color: #4ade80;

}


.badge.cancelled {

    background: #450a0a;

    color: #f87171;

}


/* =========================================================
   PRESCRIPTION LIST
========================================================= */

.prescription-row {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 0;

    border-bottom: 1px solid #1e293b;

}


.prescription-row:last-child {

    border-bottom: none;

}


.prescription-icon {

    width: 40px;

    height: 40px;

    min-width: 40px;

    border-radius: 10px;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

}


.prescription-info {

    flex: 1;

}


.prescription-info h4 {

    color: white;

    font-size: 12px;

}


.prescription-info p {

    color: #64748b;

    font-size: 10px;

    margin-top: 4px;

}


.prescription-id {

    color: #60a5fa;

    font-size: 10px;

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding: 35px 10px;

    color: #64748b;

    font-size: 12px;

}


.empty i {

    display: block;

    font-size: 25px;

    margin-bottom: 10px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1100px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .status-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width:850px) {

    .report-grid {

        grid-template-columns: 1fr;

    }

}


@media(max-width:650px) {

    .sidebar {

        width: 200px;

    }


    .main {

        margin-left: 200px;

        width: calc(100% - 200px);

        padding: 20px;

    }


    .stats {

        grid-template-columns: 1fr;

    }


    .status-grid {

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


    <a href="appointments.php">

        <i class="fa-solid fa-calendar-check"></i>

        Appointments

    </a>


    <a href="prescriptions.php">

        <i class="fa-solid fa-file-prescription"></i>

        Prescriptions

    </a>


    <a
        href="reports.php"
        class="active"
    >

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


    <!-- HEADER -->

    <div class="header">

        <h1>
            Reports & Statistics
        </h1>

        <p>
            Overview of HealthSync healthcare activities and records.
        </p>

    </div>



    <!-- =================================================
         MAIN STATISTICS
    ================================================== -->

    <div class="stats">


        <!-- DOCTORS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i
                    class="fa-solid fa-user-doctor"
                ></i>

            </div>

            <h2>

                <?php

                echo $total_doctors;

                ?>

            </h2>

            <p>
                Registered Doctors
            </p>

        </div>



        <!-- PATIENTS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i
                    class="fa-solid fa-users"
                ></i>

            </div>

            <h2>

                <?php

                echo $total_patients;

                ?>

            </h2>

            <p>
                Registered Patients
            </p>

        </div>



        <!-- APPOINTMENTS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i
                    class="fa-solid fa-calendar-check"
                ></i>

            </div>

            <h2>

                <?php

                echo $total_appointments;

                ?>

            </h2>

            <p>
                Total Appointments
            </p>

        </div>



        <!-- PRESCRIPTIONS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i
                    class="fa-solid fa-file-prescription"
                ></i>

            </div>

            <h2>

                <?php

                echo $total_prescriptions;

                ?>

            </h2>

            <p>
                Total Prescriptions
            </p>

        </div>


    </div>



    <!-- =================================================
         APPOINTMENT STATUS
    ================================================== -->

    <div class="status-grid">


        <!-- PENDING -->

        <div class="status-card">

            <div class="status-icon pending">

                <i
                    class="fa-solid fa-clock"
                ></i>

            </div>

            <div class="status-info">

                <h3>

                    <?php

                    echo $pending;

                    ?>

                </h3>

                <p>
                    Pending
                </p>

            </div>

        </div>



        <!-- APPROVED -->

        <div class="status-card">

            <div class="status-icon approved">

                <i
                    class="fa-solid fa-check"
                ></i>

            </div>

            <div class="status-info">

                <h3>

                    <?php

                    echo $approved;

                    ?>

                </h3>

                <p>
                    Approved
                </p>

            </div>

        </div>



        <!-- COMPLETED -->

        <div class="status-card">

            <div class="status-icon completed">

                <i
                    class="fa-solid fa-circle-check"
                ></i>

            </div>

            <div class="status-info">

                <h3>

                    <?php

                    echo $completed;

                    ?>

                </h3>

                <p>
                    Completed
                </p>

            </div>

        </div>



        <!-- CANCELLED -->

        <div class="status-card">

            <div class="status-icon cancelled">

                <i
                    class="fa-solid fa-xmark"
                ></i>

            </div>

            <div class="status-info">

                <h3>

                    <?php

                    echo $cancelled;

                    ?>

                </h3>

                <p>
                    Cancelled
                </p>

            </div>

        </div>


    </div>



    <!-- =================================================
         REPORT GRID
    ================================================== -->

    <div class="report-grid">


        <!-- =================================================
             MONTHLY CHART
        ================================================== -->

        <div class="report-card">


            <div class="report-header">

                <h2>
                    Monthly Appointments
                </h2>

                <p>
                    Appointment activity over recent months.
                </p>

            </div>


            <div class="report-body">


                <?php

                if (
                    count($monthly_counts) > 0
                ) {

                    $max_value =
                        max($monthly_counts);

                    if ($max_value <= 0) {

                        $max_value = 1;

                    }

                ?>

                <div class="chart">


                    <?php

                    for (
                        $i = 0;
                        $i < count($months);
                        $i++
                    ) {

                        $value =
                            $monthly_counts[$i];


                        $height =
                            ($value / $max_value)
                            * 180;


                    ?>

                    <div
                        class="bar-container"
                    >


                        <div
                            class="bar-value"
                        >

                            <?php

                            echo $value;

                            ?>

                        </div>


                        <div
                            class="bar"
                            style="
                                height:
                                <?php
                                echo $height;
                                ?>px;
                            "
                        ></div>


                        <div
                            class="bar-label"
                        >

                            <?php

                            echo htmlspecialchars(
                                $months[$i]
                            );

                            ?>

                        </div>


                    </div>


                    <?php

                    }

                    ?>


                </div>


                <?php

                }
                else {

                ?>

                    <div class="empty">

                        <i
                            class="fa-solid fa-chart-column"
                        ></i>

                        No appointment data available.

                    </div>

                <?php

                }

                ?>


            </div>


        </div>



        <!-- =================================================
             PRESCRIPTIONS
        ================================================== -->

        <div class="report-card">


            <div class="report-header">

                <h2>
                    Recent Prescriptions
                </h2>

                <p>
                    Latest prescriptions created by doctors.
                </p>

            </div>


            <div class="report-body">


                <?php

                if (
                    $recent_prescriptions_result &&
                    mysqli_num_rows(
                        $recent_prescriptions_result
                    ) > 0
                ) {

                    while (
                        $row =
                        mysqli_fetch_assoc(
                            $recent_prescriptions_result
                        )
                    ) {

                ?>


                <div
                    class="prescription-row"
                >


                    <div
                        class="prescription-icon"
                    >

                        <i
                            class="fa-solid fa-file-prescription"
                        ></i>

                    </div>


                    <div
                        class="prescription-info"
                    >

                        <h4>

                            <?php

                            echo htmlspecialchars(
                                $row['patient_name']
                                ?? 'Unknown Patient'
                            );

                            ?>

                        </h4>


                        <p>

                            Dr.

                            <?php

                            echo htmlspecialchars(
                                $row['doctor_name']
                                ?? 'Unknown Doctor'
                            );

                            ?>

                            ·

                            <?php

                            echo htmlspecialchars(
                                $row['diagnosis']
                                ?? 'No diagnosis'
                            );

                            ?>

                        </p>

                    </div>


                    <div
                        class="prescription-id"
                    >

                        #

                        <?php

                        echo intval(
                            $row['id']
                        );

                        ?>

                    </div>


                </div>


                <?php

                    }

                }
                else {

                ?>

                    <div class="empty">

                        <i
                            class="fa-solid fa-file-prescription"
                        ></i>

                        No prescriptions available.

                    </div>

                <?php

                }

                ?>


            </div>


        </div>


    </div>



    <!-- =================================================
         RECENT APPOINTMENTS
    ================================================== -->

    <div class="report-card">


        <div class="report-header">

            <h2>
                Recent Appointments
            </h2>

            <p>
                Latest patient appointment activity.
            </p>

        </div>


        <div class="report-body">


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
                            Date
                        </th>

                        <th>
                            Time
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (
                        $recent_appointments_result &&
                        mysqli_num_rows(
                            $recent_appointments_result
                        ) > 0
                    ) {


                        while (
                            $row =
                            mysqli_fetch_assoc(
                                $recent_appointments_result
                            )
                        ) {


                            $status =
                                strtolower(
                                    trim(
                                        $row['status']
                                        ?? ''
                                    )
                                );


                    ?>

                    <tr>


                        <td>

                            #

                            <?php

                            echo intval(
                                $row['id']
                            );

                            ?>

                        </td>


                        <td>

                            <span
                                class="patient-name"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $row['patient_name']
                                    ?? 'Unknown'
                                );

                                ?>

                            </span>

                        </td>


                        <td>

                            <span
                                class="doctor-name"
                            >

                                Dr.

                                <?php

                                echo htmlspecialchars(
                                    $row['doctor_name']
                                    ?? 'Unknown'
                                );

                                ?>

                            </span>

                        </td>


                        <td>

                            <?php

                            if (
                                !empty(
                                    $row[
                                        'appointment_date'
                                    ]
                                )
                            ) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row[
                                            'appointment_date'
                                        ]
                                    )
                                );

                            }
                            else {

                                echo "N/A";

                            }

                            ?>

                        </td>


                        <td>

                            <?php

                            if (
                                !empty(
                                    $row[
                                        'appointment_time'
                                    ]
                                )
                            ) {

                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $row[
                                            'appointment_time'
                                        ]
                                    )
                                );

                            }
                            else {

                                echo "N/A";

                            }

                            ?>

                        </td>


                        <td>


                            <span
                                class="badge
                                <?php

                                if (
                                    $status === 'pending'
                                ) {

                                    echo 'pending';

                                }
                                elseif (
                                    $status === 'approved'
                                ) {

                                    echo 'approved';

                                }
                                elseif (
                                    $status === 'completed'
                                ) {

                                    echo 'completed';

                                }
                                elseif (
                                    $status === 'cancelled'
                                ) {

                                    echo 'cancelled';

                                }
                                else {

                                    echo 'pending';

                                }

                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $row['status']
                                    ?? 'Unknown'
                                );

                                ?>

                            </span>


                        </td>


                    </tr>


                    <?php

                        }

                    }
                    else {

                    ?>

                    <tr>

                        <td
                            colspan="6"
                            style="
                                text-align:center;
                                color:#64748b;
                                padding:35px;
                            "
                        >

                            No appointments available.

                        </td>

                    </tr>

                    <?php

                    }

                    ?>


                    </tbody>


                </table>


            </div>


        </div>


    </div>


</main>


</div>


</body>

</html>