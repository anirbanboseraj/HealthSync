<?php

session_start();


/* =====================================================
   ADMIN SECURITY
===================================================== */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {

    header("Location: ../login.php");

    exit();

}


/* =====================================================
   DATABASE
===================================================== */

require_once("../config/database.php");


/* =====================================================
   GET PATIENT ID
===================================================== */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    header("Location: patients.php");

    exit();

}


$patient_id = intval($_GET['id']);


/* =====================================================
   GET PATIENT
===================================================== */

$sql = "
    SELECT
        id,
        fullname,
        email,
        role,
        image
    FROM users
    WHERE id = $patient_id
    AND role = 'patient'
    LIMIT 1
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {

    die(
        "Database Error: " .
        mysqli_error($conn)
    );

}


if (
    mysqli_num_rows($result) == 0
) {

    die("Patient not found.");

}


$patient = mysqli_fetch_assoc($result);


/* =====================================================
   PATIENT DATA
===================================================== */

$fullname =
    $patient['fullname'] ?? 'Unknown Patient';

$email =
    $patient['email'] ?? 'Not provided';

$role =
    $patient['role'] ?? 'patient';


/* =====================================================
   PROFILE IMAGE
===================================================== */

$profileImage = '';

if (
    isset($patient['image']) &&
    !empty($patient['image'])
) {

    $profileImage =
        "../assets/uploads/" .
        $patient['image'];

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
Patient Profile | HealthSync
</title>


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


/* =====================================================
   BODY
===================================================== */

body {

    font-family: Arial, sans-serif;

    background: #020617;

    color: #e2e8f0;

}


/* =====================================================
   LAYOUT
===================================================== */

.dashboard {

    min-height: 100vh;

    display: flex;

}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    width: 250px;

    position: fixed;

    top: 0;

    bottom: 0;

    left: 0;

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


/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 250px;

    width: calc(100% - 250px);

    padding: 30px;

}


/* =====================================================
   TOPBAR
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

    font-size: 13px;

    margin-top: 7px;

}


.back-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 10px 16px;

    background: #172554;

    border: 1px solid #1e3a8a;

    color: #60a5fa;

    text-decoration: none;

    border-radius: 8px;

    font-size: 13px;

}


.back-btn:hover {

    background: #1e3a8a;

    color: white;

}


/* =====================================================
   PROFILE CARD
===================================================== */

.profile-card {

    max-width: 950px;

    margin: 0 auto;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    overflow: hidden;

}


/* =====================================================
   PROFILE HEADER
===================================================== */

.profile-header {

    padding: 40px;

    display: flex;

    align-items: center;

    gap: 30px;

    background:

        linear-gradient(
            135deg,
            #0f172a,
            #172554
        );

    border-bottom: 1px solid #1e293b;

}


/* =====================================================
   PATIENT IMAGE
===================================================== */

.patient-avatar {

    width: 145px;

    height: 145px;

    min-width: 145px;

    border-radius: 50%;

    overflow: hidden;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    border: 4px solid #2563eb;

    box-shadow:

        0 0 0 5px
        rgba(
            37,
            99,
            235,
            0.15
        );

}


/* IMAGE */

.patient-avatar img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

}


/* DEFAULT ICON */

.patient-avatar i {

    font-size: 55px;

    color: #60a5fa;

}


/* =====================================================
   PROFILE INFO
===================================================== */

.profile-info h2 {

    color: white;

    font-size: 29px;

    margin-bottom: 10px;

}


.patient-role {

    color: #60a5fa;

    font-size: 14px;

}


.patient-id {

    color: #64748b;

    font-size: 12px;

    margin-top: 12px;

}


/* =====================================================
   DETAILS
===================================================== */

.details {

    padding: 30px;

}


.details-title {

    color: white;

    font-size: 18px;

    padding-bottom: 13px;

    margin-bottom: 20px;

    border-bottom: 1px solid #1e293b;

}


.details-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;

}


.detail-box {

    padding: 18px;

    background: #111c31;

    border: 1px solid #1e293b;

    border-radius: 9px;

}


.detail-label {

    color: #64748b;

    font-size: 10px;

    text-transform: uppercase;

    margin-bottom: 8px;

}


.detail-value {

    color: #e2e8f0;

    font-size: 14px;

    word-break: break-word;

}


/* =====================================================
   STATUS
===================================================== */

.status-box {

    margin-top: 20px;

    padding: 18px;

    background: #111c31;

    border: 1px solid #1e293b;

    border-radius: 9px;

}


.status-label {

    color: #64748b;

    font-size: 10px;

    text-transform: uppercase;

    margin-bottom: 8px;

}


.status {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 7px 13px;

    background: #052e16;

    border: 1px solid #166534;

    color: #4ade80;

    border-radius: 20px;

    font-size: 11px;

}


/* =====================================================
   ACTIONS
===================================================== */

.actions {

    padding: 0 30px 30px;

}


.back-action {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 11px 18px;

    background: #172554;

    border: 1px solid #1e3a8a;

    color: #60a5fa;

    text-decoration: none;

    border-radius: 8px;

    font-size: 13px;

}


.back-action:hover {

    background: #1e3a8a;

    color: white;

}


/* =====================================================
   RESPONSIVE
===================================================== */

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

    .dashboard {

        display: block;

    }

    .sidebar {

        position: relative;

        width: 100%;

    }

    .main {

        margin-left: 0;

        width: 100%;

    }

    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

    .profile-header {

        flex-direction: column;

        text-align: center;

    }

    .details-grid {

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


    <a
    href="patients.php"
    class="active"
    >

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


    <div class="topbar">


        <div>

            <h1>
                Patient Profile
            </h1>

            <p>
                View complete patient account information.
            </p>

        </div>


        <a
        href="patients.php"
        class="back-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Patients

        </a>


    </div>



    <!-- =================================================
         PROFILE
    ================================================== -->

    <div class="profile-card">


        <!-- PROFILE HEADER -->

        <div class="profile-header">


            <div class="patient-avatar">


                <?php if (!empty($profileImage)): ?>


                    <img
                    src="<?php
                    echo htmlspecialchars(
                        $profileImage
                    );
                    ?>"
                    alt="Patient Profile Picture"
                    >


                <?php else: ?>


                    <i class="fa-solid fa-user"></i>


                <?php endif; ?>


            </div>



            <div class="profile-info">


                <h2>

                    <?php

                    echo htmlspecialchars(
                        $fullname
                    );

                    ?>

                </h2>


                <div class="patient-role">

                    <i class="fa-solid fa-user-injured"></i>

                    HealthSync Patient

                </div>


                <div class="patient-id">

                    Patient Account ID:

                    #

                    <?php

                    echo intval(
                        $patient['id']
                    );

                    ?>

                </div>


            </div>


        </div>



        <!-- =================================================
             DETAILS
        ================================================== -->

        <div class="details">


            <div class="details-title">

                <i class="fa-solid fa-circle-info"></i>

                Patient Information

            </div>



            <div class="details-grid">


                <!-- FULL NAME -->

                <div class="detail-box">

                    <div class="detail-label">

                        Full Name

                    </div>


                    <div class="detail-value">

                        <?php

                        echo htmlspecialchars(
                            $fullname
                        );

                        ?>

                    </div>

                </div>



                <!-- EMAIL -->

                <div class="detail-box">

                    <div class="detail-label">

                        Email

                    </div>


                    <div class="detail-value">

                        <?php

                        echo htmlspecialchars(
                            $email
                        );

                        ?>

                    </div>

                </div>



                <!-- ROLE -->

                <div class="detail-box">

                    <div class="detail-label">

                        Account Role

                    </div>


                    <div class="detail-value">

                        <?php

                        echo htmlspecialchars(
                            ucfirst($role)
                        );

                        ?>

                    </div>

                </div>



                <!-- ID -->

                <div class="detail-box">

                    <div class="detail-label">

                        Account ID

                    </div>


                    <div class="detail-value">

                        #

                        <?php

                        echo intval(
                            $patient['id']
                        );

                        ?>

                    </div>

                </div>


            </div>



            <!-- STATUS -->

            <div class="status-box">


                <div class="status-label">

                    Account Status

                </div>


                <span class="status">

                    <i class="fa-solid fa-circle"></i>

                    Active Patient

                </span>


            </div>


        </div>



        <!-- ACTION -->

        <div class="actions">


            <a
            href="patients.php"
            class="back-action"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back to Patients

            </a>


        </div>


    </div>


</main>


</div>


</body>

</html>