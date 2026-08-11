<?php

session_start();

/* ==============================
   ADMIN CHECK
================================ */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'admin'
) {
    header("Location: ../login.php");
    exit();
}


/* ==============================
   DATABASE
================================ */

include("../config/database.php");


/* ==============================
   GET DOCTOR ID
================================ */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: doctors.php");
    exit();
}

$doctor_id = intval($_GET['id']);


/* ==============================
   GET DOCTOR
================================ */

$query = mysqli_query(
    $conn,
    "
    SELECT
        id,
        fullname,
        specialization,
        email,
        phone,
        experience,
        status,
        qualification,
        about,
        image,
        user_id
    FROM doctors
    WHERE id = '$doctor_id'
    LIMIT 1
    "
);


if (!$query) {

    die(
        "Database Error: " .
        mysqli_error($conn)
    );

}


if (mysqli_num_rows($query) == 0) {

    header("Location: doctors.php");
    exit();

}


$doctor = mysqli_fetch_assoc($query);


/* ==============================
   DOCTOR IMAGE
================================ */

$image = $doctor['image'];

$imageFile =
    __DIR__ .
    "/../assets/images/doctors/" .
    $image;


if (
    !empty($image) &&
    file_exists($imageFile)
) {

    $imageUrl =
        "/HealthSync/assets/images/doctors/" .
        rawurlencode($image);

} else {

    $imageUrl =
        "/HealthSync/assets/images/doctors/doctor.png";

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
    View Doctor | HealthSync
</title>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<style>

/* ==============================
   RESET
================================ */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* ==============================
   BODY
================================ */

body {

    font-family: Arial, sans-serif;

    background: #020617;

    color: #e2e8f0;

}


/* ==============================
   LAYOUT
================================ */

.dashboard {

    min-height: 100vh;

    display: flex;

}


/* ==============================
   SIDEBAR
================================ */

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

    font-size: 24px;

}


.logo span {

    color: #3b82f6;

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

    transition: .2s;

}


.sidebar a:hover {

    background: #172554;

    color: #60a5fa;

}


.sidebar a.active {

    background: #1e3a8a;

    color: white;

}


.sidebar .logout {

    margin-top: 30px;

    color: #f87171;

}


.sidebar .logout:hover {

    background: #450a0a;

    color: #fca5a5;

}


/* ==============================
   MAIN
================================ */

.main {

    margin-left: 250px;

    width: calc(100% - 250px);

    padding: 30px;

}


/* ==============================
   TOP BAR
================================ */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.topbar h1 {

    color: white;

    font-size: 27px;

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

    padding: 10px 15px;

    background: #172554;

    border: 1px solid #1e3a8a;

    border-radius: 8px;

    color: #60a5fa;

    text-decoration: none;

    font-size: 13px;

}


.back-btn:hover {

    background: #1e3a8a;

    color: white;

}


/* ==============================
   PROFILE CARD
================================ */

.profile-card {

    max-width: 1000px;

    margin: 0 auto;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    overflow: hidden;

}


/* ==============================
   PROFILE HEADER
================================ */

.profile-header {

    padding: 35px;

    display: flex;

    align-items: center;

    gap: 30px;

    background: linear-gradient(
        135deg,
        #0f172a,
        #172554
    );

    border-bottom: 1px solid #1e293b;

}


/* ==============================
   DOCTOR IMAGE
================================ */

.doctor-profile-image {

    width: 150px;

    height: 150px;

    min-width: 150px;

    object-fit: cover;

    border-radius: 50%;

    border: 4px solid #2563eb;

    background: #172554;

    display: block;

    box-shadow:
        0 0 0 5px rgba(
            37,
            99,
            235,
            .15
        );

}


/* ==============================
   DOCTOR NAME
================================ */

.profile-info h2 {

    color: white;

    font-size: 28px;

    margin-bottom: 8px;

}


.profile-info .specialization {

    color: #60a5fa;

    font-size: 15px;

    margin-bottom: 12px;

}


.profile-info .doctor-id {

    color: #64748b;

    font-size: 12px;

}


/* ==============================
   STATUS
================================ */

.status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;

    margin-top: 12px;

}


.status.available {

    background: #052e16;

    color: #86efac;

}


.status.unavailable {

    background: #450a0a;

    color: #fca5a5;

}


/* ==============================
   DETAILS
================================ */

.details {

    padding: 30px;

}


.details-title {

    color: white;

    font-size: 17px;

    margin-bottom: 20px;

    padding-bottom: 12px;

    border-bottom: 1px solid #1e293b;

}


.details-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;

}


.detail-box {

    background: #111c31;

    border: 1px solid #1e293b;

    border-radius: 9px;

    padding: 16px;

}


.detail-label {

    color: #64748b;

    font-size: 10px;

    text-transform: uppercase;

    margin-bottom: 7px;

}


.detail-value {

    color: #e2e8f0;

    font-size: 13px;

    word-break: break-word;

}


/* ==============================
   ABOUT
================================ */

.about-box {

    margin-top: 20px;

    background: #111c31;

    border: 1px solid #1e293b;

    border-radius: 9px;

    padding: 18px;

}


.about-box h3 {

    color: white;

    font-size: 14px;

    margin-bottom: 10px;

}


.about-box p {

    color: #94a3b8;

    line-height: 1.7;

    font-size: 13px;

}


/* ==============================
   ACTIONS
================================ */

.profile-actions {

    padding: 0 30px 30px;

    display: flex;

    gap: 10px;

}


.edit-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 11px 18px;

    background: #2563eb;

    color: white;

    border-radius: 8px;

    text-decoration: none;

    font-size: 13px;

}


.edit-btn:hover {

    background: #1d4ed8;

}


.back-action {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 11px 18px;

    background: #172554;

    color: #60a5fa;

    border: 1px solid #1e3a8a;

    border-radius: 8px;

    text-decoration: none;

    font-size: 13px;

}


.back-action:hover {

    background: #1e3a8a;

    color: white;

}


/* ==============================
   RESPONSIVE
================================ */

@media(max-width:700px) {

    .sidebar {

        width: 210px;

    }

    .main {

        margin-left: 210px;

        width: calc(100% - 210px);

        padding: 20px;

    }

    .profile-header {

        flex-direction: column;

        text-align: center;

    }

    .details-grid {

        grid-template-columns: 1fr;

    }

}


@media(max-width:550px) {

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

}

</style>

</head>


<body>


<div class="dashboard">


<!-- ==============================
     SIDEBAR
================================ -->

<aside class="sidebar">


    <div class="logo">

        <h2>
            Health<span>Sync</span>
        </h2>

    </div>


    <a href="dashboard.php">

        <i class="fa-solid fa-house"></i>

        Dashboard

    </a>


    <a
        href="doctors.php"
        class="active"
    >

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



<!-- ==============================
     MAIN
================================ -->

<main class="main">


    <div class="topbar">


        <div>

            <h1>
                Doctor Profile
            </h1>

            <p>
                View complete doctor information.
            </p>

        </div>


        <a
            href="doctors.php"
            class="back-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Doctors

        </a>


    </div>



    <!-- ==============================
         PROFILE
    ================================ -->

    <div class="profile-card">


        <!-- PROFILE HEADER -->

        <div class="profile-header">


            <!-- ACTUAL DOCTOR IMAGE -->

            <img
                src="<?php echo htmlspecialchars($imageUrl); ?>"
                class="doctor-profile-image"
                alt="<?php echo htmlspecialchars($doctor['fullname']); ?>"
            >


            <div class="profile-info">


                <h2>

                    <?php

                    echo htmlspecialchars(
                        $doctor['fullname']
                    );

                    ?>

                </h2>


                <div class="specialization">

                    <i class="fa-solid fa-stethoscope"></i>

                    <?php

                    echo htmlspecialchars(
                        $doctor['specialization']
                    );

                    ?>

                </div>


                <div class="doctor-id">

                    Doctor ID:

                    #<?php echo $doctor['id']; ?>

                </div>


                <?php

                if (
                    strtolower(
                        $doctor['status']
                    ) == 'available'
                ) {

                ?>

                    <span class="status available">

                        <i class="fa-solid fa-circle"></i>

                        Available

                    </span>

                <?php

                } else {

                ?>

                    <span class="status unavailable">

                        <i class="fa-solid fa-circle"></i>

                        Unavailable

                    </span>

                <?php

                }

                ?>


            </div>


        </div>



        <!-- DETAILS -->

        <div class="details">


            <div class="details-title">

                <i class="fa-solid fa-circle-info"></i>

                Doctor Information

            </div>



            <div class="details-grid">


                <!-- EMAIL -->

                <div class="detail-box">

                    <div class="detail-label">
                        Email
                    </div>

                    <div class="detail-value">

                        <?php

                        echo !empty($doctor['email'])
                            ? htmlspecialchars($doctor['email'])
                            : "Not provided";

                        ?>

                    </div>

                </div>



                <!-- PHONE -->

                <div class="detail-box">

                    <div class="detail-label">
                        Phone
                    </div>

                    <div class="detail-value">

                        <?php

                        echo !empty($doctor['phone'])
                            ? htmlspecialchars($doctor['phone'])
                            : "Not provided";

                        ?>

                    </div>

                </div>



                <!-- EXPERIENCE -->

                <div class="detail-box">

                    <div class="detail-label">
                        Experience
                    </div>

                    <div class="detail-value">

                        <?php

                        if (
                            $doctor['experience'] !== null &&
                            $doctor['experience'] !== ''
                        ) {

                            echo htmlspecialchars(
                                $doctor['experience']
                            ) . " years";

                        } else {

                            echo "Not provided";

                        }

                        ?>

                    </div>

                </div>



                <!-- QUALIFICATION -->

                <div class="detail-box">

                    <div class="detail-label">
                        Qualification
                    </div>

                    <div class="detail-value">

                        <?php

                        echo !empty(
                            $doctor['qualification']
                        )
                            ? htmlspecialchars(
                                $doctor['qualification']
                            )
                            : "Not provided";

                        ?>

                    </div>

                </div>


            </div>



            <!-- ABOUT -->

            <div class="about-box">


                <h3>

                    <i class="fa-solid fa-user-doctor"></i>

                    About Doctor

                </h3>


                <p>

                    <?php

                    echo !empty($doctor['about'])
                        ? nl2br(
                            htmlspecialchars(
                                $doctor['about']
                            )
                        )
                        : "No information provided.";

                    ?>

                </p>


            </div>


        </div>



        <!-- ACTIONS -->

        <div class="profile-actions">


            <a
                href="edit-doctor.php?id=<?php echo $doctor['id']; ?>"
                class="edit-btn"
            >

                <i class="fa-solid fa-pen"></i>

                Edit Doctor

            </a>


            <a
                href="doctors.php"
                class="back-action"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back

            </a>


        </div>


    </div>


</main>


</div>


</body>

</html>