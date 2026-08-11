<?php

session_start();

include("../config/database.php");


// =====================================================
// CHECK PATIENT LOGIN
// =====================================================

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] != 'patient'
) {
    header("Location: ../login.php");
    exit();
}


$patient_id = $_SESSION['user_id'];


// =====================================================
// GET PATIENT NAME
// =====================================================

$patientQuery = mysqli_query(
    $conn,
    "
    SELECT fullname
    FROM users
    WHERE id='$patient_id'
    LIMIT 1
    "
);

$patient = mysqli_fetch_assoc($patientQuery);


// =====================================================
// GET PRESCRIPTIONS
// =====================================================

$query = mysqli_query(
    $conn,
    "
    SELECT

        prescriptions.id,

        prescriptions.diagnosis,

        prescriptions.medicines,

        prescriptions.advice,

        prescriptions.created_at,

        doctors.fullname AS doctor_name,

        doctors.specialization,

        appointments.appointment_date,

        appointments.appointment_time

    FROM prescriptions

    INNER JOIN doctors
        ON prescriptions.doctor_id = doctors.id

    LEFT JOIN appointments
        ON prescriptions.appointment_id = appointments.id

    WHERE prescriptions.patient_id='$patient_id'

    ORDER BY prescriptions.created_at DESC
    "
);

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
        My Prescriptions | HealthSync
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
   LAYOUT
===================================================== */

.patient-dashboard {

    display: flex;

    min-height: 100vh;

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


.logo {

    text-align: center;

    margin-bottom: 35px;

}


.logo h2 {

    color: white;

    font-size: 25px;

}


.logo h2 span {

    color: #3b82f6;

}


.logo p {

    color: #64748b;

    font-size: 12px;

    margin-top: 5px;

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

    transition: .2s;

}


.sidebar a:hover {

    background: #1e3a8a;

    color: white;

}


.sidebar li.active a {

    background: #1d4ed8;

    color: white;

}


.sidebar a i {

    width: 20px;

}


.logout {

    margin-top: 35px;

}


/* =====================================================
   MAIN
===================================================== */

.main-content {

    flex: 1;

    padding: 35px 40px;

}


.page-header {

    margin-bottom: 30px;

}


.page-header h1 {

    color: white;

    font-size: 30px;

}


.page-header p {

    color: #64748b;

    margin-top: 7px;

}


/* =====================================================
   WELCOME
===================================================== */

.welcome-box {

    background: linear-gradient(
        135deg,
        #172554,
        #0f172a
    );

    border: 1px solid #1e3a8a;

    border-radius: 14px;

    padding: 20px;

    margin-bottom: 30px;

}


.welcome-box h3 {

    color: white;

    font-size: 18px;

    margin-bottom: 6px;

}


.welcome-box p {

    color: #94a3b8;

    font-size: 13px;

}


/* =====================================================
   PRESCRIPTION CARD
===================================================== */

.prescription-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    margin-bottom: 25px;

    overflow: hidden;

    box-shadow:
        0 10px 30px
        rgba(0,0,0,.15);

}


/* =====================================================
   CARD HEADER
===================================================== */

.prescription-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 22px 25px;

    background: #111c33;

    border-bottom: 1px solid #1e293b;

}


.doctor-info {

    display: flex;

    align-items: center;

    gap: 15px;

}


.doctor-icon {

    width: 48px;

    height: 48px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

    font-size: 20px;

}


.doctor-info h3 {

    color: white;

    font-size: 17px;

}


.doctor-info p {

    color: #64748b;

    font-size: 12px;

    margin-top: 4px;

}


.prescription-date {

    text-align: right;

}


.prescription-date span {

    display: block;

    color: #64748b;

    font-size: 11px;

    margin-bottom: 4px;

}


.prescription-date strong {

    color: #cbd5e1;

    font-size: 12px;

}


/* =====================================================
   CARD BODY
===================================================== */

.prescription-body {

    padding: 25px;

}


/* =====================================================
   SECTION
===================================================== */

.medical-section {

    margin-bottom: 23px;

}


.medical-section:last-child {

    margin-bottom: 0;

}


.section-title {

    display: flex;

    align-items: center;

    gap: 9px;

    color: #60a5fa;

    font-size: 14px;

    font-weight: bold;

    margin-bottom: 10px;

}


.section-title i {

    width: 22px;

}


.section-content {

    background: #020617;

    border: 1px solid #1e293b;

    border-radius: 9px;

    padding: 14px;

    color: #cbd5e1;

    font-size: 13px;

    line-height: 1.7;

    white-space: pre-line;

}


/* =====================================================
   APPOINTMENT INFO
===================================================== */

.appointment-info {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-bottom: 25px;

}


.info-item {

    display: flex;

    align-items: center;

    gap: 7px;

    background: #172033;

    border: 1px solid #26344d;

    padding: 8px 12px;

    border-radius: 7px;

    color: #94a3b8;

    font-size: 11px;

}


.info-item i {

    color: #60a5fa;

}


/* =====================================================
   EMPTY STATE
===================================================== */

.empty-state {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    padding: 60px 30px;

    text-align: center;

}


.empty-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 20px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

    font-size: 28px;

}


.empty-state h2 {

    color: white;

    font-size: 20px;

    margin-bottom: 8px;

}


.empty-state p {

    color: #64748b;

    font-size: 13px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:750px) {

    .patient-dashboard {

        flex-direction: column;

    }


    .sidebar {

        width: 100%;

        min-height: auto;

    }


    .sidebar ul {

        display: flex;

        flex-wrap: wrap;

    }


    .logout {

        margin-top: 0;

    }


    .main-content {

        padding: 20px;

    }


    .prescription-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .prescription-date {

        text-align: left;

    }

}

</style>

</head>


<body>


<div class="patient-dashboard">


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">


    <div class="logo">

        <h2>
            Health<span>Sync</span>
        </h2>

        <p>
            Patient Portal
        </p>

    </div>


    <ul>


        <li>

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


        <li class="active">

            <a href="prescriptions.php">

                <i class="fa-solid fa-file-prescription"></i>

                Prescriptions

            </a>

        </li>


        <li>

            <a href="medical-records.php">

                <i class="fa-solid fa-folder-open"></i>

                Medical Records

            </a>

        </li>


        <li>

            <a href="profile.php">

                <i class="fa-solid fa-user"></i>

                My Profile

            </a>

        </li>


        <li class="logout">

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


    <div class="page-header">

        <h1>
            My Prescriptions
        </h1>

        <p>
            View prescriptions provided by your doctors.
        </p>

    </div>



    <div class="welcome-box">

        <h3>

            Hello,
            <?php
            echo htmlspecialchars(
                $patient['fullname']
            );
            ?> 👋

        </h3>

        <p>
            Your prescriptions and medical instructions
            are listed below.
        </p>

    </div>



<?php

if (mysqli_num_rows($query) > 0) {


    while (
        $row =
        mysqli_fetch_assoc($query)
    ) {

?>


<!-- =====================================================
     PRESCRIPTION CARD
===================================================== -->

<div class="prescription-card">


    <!-- HEADER -->

    <div class="prescription-header">


        <div class="doctor-info">


            <div class="doctor-icon">

                <i class="fa-solid fa-user-doctor"></i>

            </div>


            <div>

                <h3>

                    <?php

                    echo htmlspecialchars(
                        $row['doctor_name']
                    );

                    ?>

                </h3>


                <p>

                    <?php

                    echo htmlspecialchars(
                        $row['specialization']
                    );

                    ?>

                </p>

            </div>


        </div>


        <div class="prescription-date">

            <span>
                Prescription Date
            </span>

            <strong>

                <?php

                echo date(
                    "d M Y, h:i A",
                    strtotime(
                        $row['created_at']
                    )
                );

                ?>

            </strong>

        </div>


    </div>



    <!-- BODY -->

    <div class="prescription-body">


        <!-- APPOINTMENT -->

        <div class="appointment-info">


            <?php

            if (
                !empty(
                    $row['appointment_date']
                )
            ) {

            ?>


            <div class="info-item">

                <i
                    class="fa-solid fa-calendar"
                ></i>

                Appointment:

                <?php

                echo date(
                    "d M Y",
                    strtotime(
                        $row['appointment_date']
                    )
                );

                ?>

            </div>


            <div class="info-item">

                <i
                    class="fa-solid fa-clock"
                ></i>

                <?php

                echo date(
                    "h:i A",
                    strtotime(
                        $row['appointment_time']
                    )
                );

                ?>

            </div>


            <?php

            }

            ?>


        </div>



        <!-- DIAGNOSIS -->

        <div class="medical-section">


            <div class="section-title">

                <i
                    class="fa-solid fa-stethoscope"
                ></i>

                Diagnosis

            </div>


            <div class="section-content">

                <?php

                echo nl2br(
                    htmlspecialchars(
                        $row['diagnosis']
                    )
                );

                ?>

            </div>


        </div>



        <!-- MEDICINES -->

        <div class="medical-section">


            <div class="section-title">

                <i
                    class="fa-solid fa-pills"
                ></i>

                Medicines

            </div>


            <div class="section-content">

                <?php

                echo nl2br(
                    htmlspecialchars(
                        $row['medicines']
                    )
                );

                ?>

            </div>


        </div>



        <!-- ADVICE -->

        <?php

        if (
            !empty(
                $row['advice']
            )
        ) {

        ?>


        <div class="medical-section">


            <div class="section-title">

                <i
                    class="fa-solid fa-notes-medical"
                ></i>

                Doctor's Advice

            </div>


            <div class="section-content">

                <?php

                echo nl2br(
                    htmlspecialchars(
                        $row['advice']
                    )
                );

                ?>

            </div>


        </div>


        <?php

        }

        ?>


    </div>


</div>


<?php

    }

}

else {

?>


<!-- =====================================================
     EMPTY STATE
===================================================== -->

<div class="empty-state">


    <div class="empty-icon">

        <i
            class="fa-solid fa-file-prescription"
        ></i>

    </div>


    <h2>
        No Prescriptions Yet
    </h2>


    <p>

        You don't have any prescriptions
        from your doctors yet.

    </p>


</div>


<?php

}

?>


</main>


</div>


</body>

</html>