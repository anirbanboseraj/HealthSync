<?php

session_start();

require_once("../config/database.php");

/* =========================================================
   GET DOCTOR ID
========================================================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: doctors.php");
    exit;
}

$doctor_id = (int) $_GET['id'];


/* =========================================================
   GET DOCTOR
========================================================= */

$sql = "SELECT *
        FROM doctors
        WHERE id = $doctor_id
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    header("Location: doctors.php");
    exit;
}

$doctor = mysqli_fetch_assoc($result);


/* =========================================================
   DOCTOR NAME
   Prevent Dr. Dr. Name
========================================================= */

$fullname = trim($doctor['fullname']);

if (stripos($fullname, 'Dr.') === 0) {
    $display_name = $fullname;
} else {
    $display_name = "Dr. " . $fullname;
}


/* =========================================================
   IMAGE
========================================================= */

$image_name = isset($doctor['image'])
    ? trim($doctor['image'])
    : "";

$image_file = __DIR__ . "/../assets/images/doctors/" . $image_name;

$image_url = "../assets/images/doctors/" . $image_name;

$has_image = (
    !empty($image_name) &&
    file_exists($image_file)
);


/* =========================================================
   OPTIONAL DATA
========================================================= */

$qualification = isset($doctor['qualification'])
    ? trim($doctor['qualification'])
    : "";

$about = isset($doctor['about'])
    ? trim($doctor['about'])
    : "";

$experience = isset($doctor['experience'])
    ? trim($doctor['experience'])
    : "";

$email = isset($doctor['email'])
    ? trim($doctor['email'])
    : "";

$phone = isset($doctor['phone'])
    ? trim($doctor['phone'])
    : "";

$specialization = isset($doctor['specialization'])
    ? trim($doctor['specialization'])
    : "";

$status = isset($doctor['status'])
    ? trim($doctor['status'])
    : "";


/* Remove useless about text */

if (
    $about === "!!!" ||
    $about === "!" ||
    $about === "-"
) {
    $about = "";
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
        <?php echo htmlspecialchars($display_name); ?> | HealthSync
    </title>


    <!-- Dashboard CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/dashboard.css"
    >


    <!-- Doctor Profile CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/doctor-details.css"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


<!-- =====================================================
     PATIENT SIDEBAR
===================================================== -->

<?php include("../includes/patient-sidebar.php"); ?>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="main-content">


    <!-- =================================================
         BACK BUTTON
    ================================================== -->

    <a
        href="doctors.php"
        class="back-button"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to Doctors

    </a>



    <!-- =================================================
         PROFILE CARD
    ================================================== -->

    <section class="doctor-profile-card">


        <!-- =================================================
             PROFILE TOP
        ================================================== -->

        <div class="profile-top">


            <!-- Doctor Image -->

            <div class="profile-image-wrapper">

                <?php if ($has_image): ?>

                    <img
                        src="<?php echo htmlspecialchars($image_url); ?>"
                        alt="<?php echo htmlspecialchars($display_name); ?>"
                        class="profile-image"
                    >

                <?php else: ?>

                    <div class="profile-placeholder">

                        <i class="fa-solid fa-user-doctor"></i>

                    </div>

                <?php endif; ?>

            </div>



            <!-- Doctor Basic Information -->

            <div class="profile-heading">

                <h1>

                    <?php echo htmlspecialchars($display_name); ?>

                </h1>


                <?php if (!empty($specialization)): ?>

                    <h2>

                        <i class="fa-solid fa-stethoscope"></i>

                        <?php echo htmlspecialchars($specialization); ?>

                    </h2>

                <?php endif; ?>


                <?php if (!empty($status)): ?>

                    <span class="doctor-status">

                        <i class="fa-solid fa-circle"></i>

                        <?php echo htmlspecialchars($status); ?>

                    </span>

                <?php endif; ?>

            </div>


        </div>



        <!-- =================================================
             INFORMATION GRID
        ================================================== -->

        <div class="profile-information">


            <?php if (!empty($qualification)): ?>

                <div class="information-box">

                    <div class="information-icon">

                        <i class="fa-solid fa-graduation-cap"></i>

                    </div>

                    <div>

                        <span>
                            Qualification
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars($qualification);
                            ?>
                        </strong>

                    </div>

                </div>

            <?php endif; ?>



            <?php if (!empty($experience)): ?>

                <div class="information-box">

                    <div class="information-icon">

                        <i class="fa-solid fa-briefcase"></i>

                    </div>

                    <div>

                        <span>
                            Experience
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars($experience);
                            ?>

                            Years

                        </strong>

                    </div>

                </div>

            <?php endif; ?>



            <?php if (!empty($email)): ?>

                <div class="information-box">

                    <div class="information-icon">

                        <i class="fa-solid fa-envelope"></i>

                    </div>

                    <div>

                        <span>
                            Email
                        </span>

                        <strong>
                            <?php echo htmlspecialchars($email); ?>
                        </strong>

                    </div>

                </div>

            <?php endif; ?>



            <?php if (!empty($phone)): ?>

                <div class="information-box">

                    <div class="information-icon">

                        <i class="fa-solid fa-phone"></i>

                    </div>

                    <div>

                        <span>
                            Phone
                        </span>

                        <strong>
                            <?php echo htmlspecialchars($phone); ?>
                        </strong>

                    </div>

                </div>

            <?php endif; ?>


        </div>



        <!-- =================================================
             ABOUT DOCTOR
        ================================================== -->

        <?php if (!empty($about)): ?>

            <div class="about-section">

                <h2>

                    <i class="fa-solid fa-user-doctor"></i>

                    About Doctor

                </h2>

                <p>

                    <?php echo nl2br(htmlspecialchars($about)); ?>

                </p>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ACTIONS
        ================================================== -->

        <div class="profile-actions">

            <a
                href="book-appointment.php?doctor_id=<?php echo $doctor_id; ?>"
                class="profile-button primary"
            >

                <i class="fa-solid fa-calendar-plus"></i>

                Book Appointment

            </a>


            <a
                href="doctors.php"
                class="profile-button secondary"
            >

                <i class="fa-solid fa-user-doctor"></i>

                View All Doctors

            </a>

        </div>


    </section>


</main>


</body>

</html>