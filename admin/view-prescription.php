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
   GET PRESCRIPTION ID
========================================================= */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: prescriptions.php");
    exit();
}

$prescription_id = intval($_GET['id']);


/* =========================================================
   GET PRESCRIPTION
========================================================= */

$sql = "

    SELECT

        p.id,
        p.appointment_id,
        p.doctor_id,
        p.patient_id,
        p.diagnosis,
        p.medicines,
        p.advice,
        p.created_at,

        a.appointment_date,
        a.appointment_time,
        a.problem,

        patient.fullname AS patient_name,
        patient.email AS patient_email,

        d.fullname AS doctor_name

    FROM prescriptions p

    LEFT JOIN appointments a
        ON p.appointment_id = a.id

    LEFT JOIN users patient
        ON p.patient_id = patient.id

    LEFT JOIN doctors d
        ON p.doctor_id = d.id

    WHERE p.id = $prescription_id

    LIMIT 1
";


$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Database Error: " .
        mysqli_error($conn)
    );

}


$prescription = mysqli_fetch_assoc($result);


if (!$prescription) {

    header(
        "Location: prescriptions.php"
    );

    exit();

}


/* =========================================================
   FORMAT DATE / TIME
========================================================= */

$appointment_date = "N/A";

if (
    !empty(
        $prescription['appointment_date']
    )
) {

    $appointment_date = date(
        "d M Y",
        strtotime(
            $prescription['appointment_date']
        )
    );

}


$appointment_time = "N/A";

if (
    !empty(
        $prescription['appointment_time']
    )
) {

    $appointment_time = date(
        "h:i A",
        strtotime(
            $prescription['appointment_time']
        )
    );

}


$created_at = "N/A";

if (
    !empty(
        $prescription['created_at']
    )
) {

    $created_at = date(
        "d M Y, h:i A",
        strtotime(
            $prescription['created_at']
        )
    );

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
    View Prescription | HealthSync
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
   TOP BAR
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


.top-actions {

    display: flex;

    gap: 10px;

}


.back-btn,
.print-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 10px 15px;

    border-radius: 8px;

    text-decoration: none;

    font-size: 11px;

    font-weight: bold;

}


.back-btn {

    background: #172554;

    border: 1px solid #1e40af;

    color: #60a5fa;

}


.back-btn:hover {

    background: #1e3a8a;

    color: white;

}


.print-btn {

    background: #2563eb;

    border: none;

    color: white;

    cursor: pointer;

}


.print-btn:hover {

    background: #3b82f6;

}


/* =========================================================
   PRESCRIPTION CARD
========================================================= */

.prescription-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 16px;

    overflow: hidden;

}


/* =========================================================
   PRESCRIPTION HEADER
========================================================= */

.prescription-header {

    padding: 30px;

    border-bottom: 1px solid #1e293b;

    display: flex;

    align-items: center;

    justify-content: space-between;

}


.brand {

    display: flex;

    align-items: center;

    gap: 15px;

}


.brand-icon {

    width: 55px;

    height: 55px;

    border-radius: 13px;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 23px;

}


.brand h2 {

    color: white;

    font-size: 22px;

}


.brand h2 span {

    color: #3b82f6;

}


.brand p {

    color: #64748b;

    font-size: 11px;

    margin-top: 4px;

}


.prescription-number {

    text-align: right;

}


.prescription-number small {

    display: block;

    color: #64748b;

    font-size: 10px;

    margin-bottom: 5px;

}


.prescription-number strong {

    color: #60a5fa;

    font-size: 17px;

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
   PATIENT / DOCTOR GRID
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
   APPOINTMENT INFORMATION
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
   MEDICAL BOX
========================================================= */

.medical-box {

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 12px;

    padding: 22px;

}


.medical-label {

    color: #64748b;

    font-size: 10px;

    text-transform: uppercase;

    margin-bottom: 10px;

}


.diagnosis {

    color: #f1f5f9;

    font-size: 15px;

    font-weight: bold;

    line-height: 1.6;

}


/* =========================================================
   MEDICINES
========================================================= */

.medicines-box {

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 12px;

    padding: 22px;

}


.medicines {

    color: #cbd5e1;

    font-size: 13px;

    line-height: 1.8;

    white-space: pre-line;

}


/* =========================================================
   ADVICE
========================================================= */

.advice-box {

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 12px;

    padding: 22px;

}


.advice {

    color: #cbd5e1;

    font-size: 13px;

    line-height: 1.8;

    white-space: pre-line;

}


/* =========================================================
   FOOTER
========================================================= */

.prescription-footer {

    padding: 20px 30px;

    border-top: 1px solid #1e293b;

    color: #64748b;

    font-size: 10px;

    display: flex;

    align-items: center;

    justify-content: space-between;

}


/* =========================================================
   PRINT
========================================================= */

@media print {

    body {

        background: white;

        color: black;

    }


    .sidebar,
    .top,
    .no-print {

        display: none !important;

    }


    .main {

        margin: 0;

        width: 100%;

        padding: 0;

    }


    .prescription-card {

        border: none;

        border-radius: 0;

        background: white;

        color: black;

    }


    .prescription-header {

        border-bottom: 2px solid #ddd;

    }


    .brand h2,
    .heading h1,
    .section-title,
    .person-info h3,
    .info-value,
    .diagnosis {

        color: #111 !important;

    }


    .brand p,
    .person-info p,
    .info-label,
    .medicines,
    .advice,
    .prescription-footer {

        color: #555 !important;

    }


    .person-card,
    .info-box,
    .medical-box,
    .medicines-box,
    .advice-box {

        background: white;

        border: 1px solid #ddd;

    }


    .brand-icon,
    .person-icon {

        background: #f1f5f9;

        color: #2563eb;

    }

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 800px) {

    .people {

        grid-template-columns: 1fr;

    }


    .info-grid {

        grid-template-columns:
            1fr 1fr;

    }


    .prescription-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 20px;

    }


    .prescription-number {

        text-align: left;

    }

}


@media(max-width: 600px) {

    .sidebar {

        width: 200px;

    }


    .main {

        margin-left: 200px;

        width: calc(100% - 200px);

        padding: 20px;

    }


    .info-grid {

        grid-template-columns: 1fr;

    }


    .top {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .top-actions {

        width: 100%;

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
                Prescription Details
            </h1>

            <p>
                Complete medical prescription information.
            </p>

        </div>


        <div class="top-actions">


            <a
                href="prescriptions.php"
                class="back-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back

            </a>


            <button
                type="button"
                class="print-btn"
                onclick="window.print()"
            >

                <i class="fa-solid fa-print"></i>

                Print

            </button>


        </div>


    </div>



    <!-- =================================================
         PRESCRIPTION CARD
    ================================================== -->

    <div class="prescription-card">


        <!-- HEADER -->

        <div class="prescription-header">


            <div class="brand">


                <div class="brand-icon">

                    <i
                        class="fa-solid fa-file-prescription"
                    ></i>

                </div>


                <div>

                    <h2>

                        Health<span>Sync</span>

                    </h2>

                    <p>
                        Digital Healthcare Portal
                    </p>

                </div>


            </div>


            <div class="prescription-number">

                <small>
                    PRESCRIPTION
                </small>

                <strong>

                    #<?php

                    echo intval(
                        $prescription['id']
                    );

                    ?>

                </strong>

            </div>


        </div>



        <div class="content">


            <!-- =================================================
                 PATIENT / DOCTOR
            ================================================== -->

            <div class="section">


                <div class="section-title">

                    <i
                        class="fa-solid fa-users"
                    ></i>

                    Patient & Doctor

                </div>


                <div class="people">


                    <!-- PATIENT -->

                    <div class="person-card">


                        <div class="person-icon">

                            <i
                                class="fa-solid fa-user"
                            ></i>

                        </div>


                        <div class="person-info">


                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $prescription['patient_name']
                                    ?? 'Unknown Patient'
                                );

                                ?>

                            </h3>


                            <p>

                                Patient ID:

                                <span class="id">

                                    <?php

                                    echo intval(
                                        $prescription['patient_id']
                                    );

                                    ?>

                                </span>

                            </p>


                            <p>

                                <?php

                                echo htmlspecialchars(
                                    $prescription['patient_email']
                                    ?? 'No email'
                                );

                                ?>

                            </p>


                        </div>


                    </div>



                    <!-- DOCTOR -->

                    <div class="person-card">


                        <div class="person-icon">

                            <i
                                class="fa-solid fa-user-doctor"
                            ></i>

                        </div>


                        <div class="person-info">


                            <h3>

                                <?php

                                echo htmlspecialchars(
                                    $prescription['doctor_name']
                                    ?? 'Unknown Doctor'
                                );

                                ?>

                            </h3>


                            <p>

                                Doctor ID:

                                <span class="id">

                                    <?php

                                    echo intval(
                                        $prescription['doctor_id']
                                    );

                                    ?>

                                </span>

                            </p>


                        </div>


                    </div>


                </div>


            </div>



            <!-- =================================================
                 APPOINTMENT
            ================================================== -->

            <div class="section">


                <div class="section-title">

                    <i
                        class="fa-solid fa-calendar-check"
                    ></i>

                    Appointment Information

                </div>


                <div class="info-grid">


                    <!-- APPOINTMENT ID -->

                    <div class="info-box">


                        <div class="info-label">
                            Appointment ID
                        </div>


                        <div class="info-value blue">

                            #

                            <?php

                            echo intval(
                                $prescription['appointment_id']
                            );

                            ?>

                        </div>


                    </div>



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
                                $appointment_date
                            );

                            ?>

                        </div>


                    </div>



                    <!-- TIME -->

                    <div class="info-box">


                        <div class="info-label">
                            Appointment Time
                        </div>


                        <div class="info-value">

                            <i
                                class="fa-regular fa-clock"
                            ></i>

                            <?php

                            echo htmlspecialchars(
                                $appointment_time
                            );

                            ?>

                        </div>


                    </div>


                </div>


            </div>



            <!-- =================================================
                 DIAGNOSIS
            ================================================== -->

            <div class="section">


                <div class="section-title">

                    <i
                        class="fa-solid fa-stethoscope"
                    ></i>

                    Diagnosis

                </div>


                <div class="medical-box">


                    <div class="medical-label">
                        Doctor's Diagnosis
                    </div>


                    <div class="diagnosis">

                        <?php

                        if (
                            !empty(
                                $prescription['diagnosis']
                            )
                        ) {

                            echo nl2br(
                                htmlspecialchars(
                                    $prescription['diagnosis']
                                )
                            );

                        }
                        else {

                            echo "No diagnosis provided.";

                        }

                        ?>

                    </div>


                </div>


            </div>



            <!-- =================================================
                 MEDICINES
            ================================================== -->

            <div class="section">


                <div class="section-title">

                    <i
                        class="fa-solid fa-pills"
                    ></i>

                    Medicines

                </div>


                <div class="medicines-box">


                    <div class="medical-label">
                        Prescribed Medicines
                    </div>


                    <div class="medicines">

                        <?php

                        if (
                            !empty(
                                $prescription['medicines']
                            )
                        ) {

                            echo nl2br(
                                htmlspecialchars(
                                    $prescription['medicines']
                                )
                            );

                        }
                        else {

                            echo "No medicines prescribed.";

                        }

                        ?>

                    </div>


                </div>


            </div>



            <!-- =================================================
                 ADVICE
            ================================================== -->

            <div class="section">


                <div class="section-title">

                    <i
                        class="fa-solid fa-notes-medical"
                    ></i>

                    Advice & Instructions

                </div>


                <div class="advice-box">


                    <div class="medical-label">
                        Doctor's Advice
                    </div>


                    <div class="advice">

                        <?php

                        if (
                            !empty(
                                $prescription['advice']
                            )
                        ) {

                            echo nl2br(
                                htmlspecialchars(
                                    $prescription['advice']
                                )
                            );

                        }
                        else {

                            echo "No additional advice provided.";

                        }

                        ?>

                    </div>


                </div>


            </div>



        </div>



        <!-- FOOTER -->

        <div class="prescription-footer">


            <span>

                <i
                    class="fa-regular fa-clock"
                ></i>

                Created:

                <?php

                echo htmlspecialchars(
                    $created_at
                );

                ?>

            </span>


            <span>

                HealthSync Admin Portal

            </span>


        </div>


    </div>


</main>


</div>


</body>

</html>