<?php
session_start();

require_once("../config/database.php");

/* =========================
   CHECK PATIENT LOGIN
========================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}


/* =========================
   GET DOCTOR ID
========================= */

if (!isset($_GET['doctor_id']) || !is_numeric($_GET['doctor_id'])) {
    die("Invalid doctor.");
}

$doctor_id = (int) $_GET['doctor_id'];


/* =========================
   GET DOCTOR INFORMATION
========================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, fullname, specialization, qualification, experience, about, image
     FROM doctors
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $doctor_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Doctor not found.");
}

$doctor = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================
   PATIENT INFORMATION
========================= */

$patient_name = $_SESSION['fullname'] ?? '';
$patient_email = $_SESSION['email'] ?? '';

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Book Appointment | HealthSync
    </title>


    <!-- Dashboard CSS -->

    <link rel="stylesheet"
          href="../assets/css/dashboard.css">


    <!-- Appointment CSS -->

    <link rel="stylesheet"
          href="../assets/css/book-appointment.css">


    <!-- Font Awesome -->

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>


<body>


<?php include("../includes/patient-sidebar.php"); ?>


<!-- =========================
     MAIN CONTENT
========================= -->

<main class="main-content">


    <!-- =========================
         PAGE HEADER
    ========================== -->

    <div class="appointment-header">

        <div>

            <span class="page-label">
                <i class="fa-solid fa-calendar-check"></i>
                APPOINTMENT
            </span>

            <h1>
                Book an Appointment
            </h1>

            <p>
                Schedule a consultation with your selected doctor.
            </p>

        </div>

    </div>



    <!-- =========================
         APPOINTMENT CONTAINER
    ========================== -->

    <div class="appointment-container">


        <!-- =========================
             DOCTOR CARD
        ========================== -->

        <div class="selected-doctor">


            <div class="doctor-card-title">

                <i class="fa-solid fa-user-doctor"></i>

                <span>
                    Selected Doctor
                </span>

            </div>


            <div class="doctor-profile">


                <!-- Doctor Image -->

                <div class="doctor-photo">

                    <?php

                    $image = !empty($doctor['image'])
                        ? $doctor['image']
                        : 'default-doctor.jpg';

                    ?>

                    <img
                        src="../assets/images/doctors/<?php echo htmlspecialchars($image); ?>"
                        alt="<?php echo htmlspecialchars($doctor['fullname']); ?>"
                    >

                </div>


                <!-- Doctor Details -->

                <div class="doctor-details">

                    <h2>
                        Dr.
                        <?php echo htmlspecialchars($doctor['fullname']); ?>
                    </h2>


                    <div class="specialization">

                        <i class="fa-solid fa-stethoscope"></i>

                        <?php echo htmlspecialchars($doctor['specialization']); ?>

                    </div>


                    <?php if (!empty($doctor['qualification'])): ?>

                        <div class="doctor-detail">

                            <i class="fa-solid fa-graduation-cap"></i>

                            <span>
                                <?php echo htmlspecialchars($doctor['qualification']); ?>
                            </span>

                        </div>

                    <?php endif; ?>


                    <?php if (!empty($doctor['experience'])): ?>

                        <div class="doctor-detail">

                            <i class="fa-solid fa-briefcase"></i>

                            <span>
                                <?php echo htmlspecialchars($doctor['experience']); ?>
                                years experience
                            </span>

                        </div>

                    <?php endif; ?>


                </div>

            </div>


            <?php if (!empty($doctor['about'])): ?>

                <div class="doctor-about">

                    <h3>
                        About Doctor
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($doctor['about']); ?>
                    </p>

                </div>

            <?php endif; ?>


        </div>



        <!-- =========================
             BOOKING FORM
        ========================== -->

        <div class="booking-form-card">


            <div class="form-title">

                <div class="form-icon">

                    <i class="fa-solid fa-calendar-plus"></i>

                </div>

                <div>

                    <h2>
                        Appointment Details
                    </h2>

                    <p>
                        Please provide your preferred schedule.
                    </p>

                </div>

            </div>


            <form
                action="process-appointment.php"
                method="POST"
            >


                <!-- Doctor ID -->

                <input
                    type="hidden"
                    name="doctor_id"
                    value="<?php echo $doctor_id; ?>"
                >


                <!-- =========================
                     PATIENT INFORMATION
                ========================== -->

                <div class="form-section">

                    <h3>
                        <i class="fa-solid fa-user"></i>
                        Patient Information
                    </h3>


                    <div class="form-grid">


                        <div class="form-group">

                            <label>
                                Full Name
                            </label>

                            <div class="input-wrapper">

                                <i class="fa-solid fa-user"></i>

                                <input
                                    type="text"
                                    value="<?php echo htmlspecialchars($patient_name); ?>"
                                    readonly
                                >

                            </div>

                        </div>


                        <div class="form-group">

                            <label>
                                Email
                            </label>

                            <div class="input-wrapper">

                                <i class="fa-solid fa-envelope"></i>

                                <input
                                    type="email"
                                    value="<?php echo htmlspecialchars($patient_email); ?>"
                                    readonly
                                >

                            </div>

                        </div>


                    </div>

                </div>



                <!-- =========================
                     APPOINTMENT SCHEDULE
                ========================== -->

                <div class="form-section">

                    <h3>

                        <i class="fa-solid fa-calendar-days"></i>

                        Appointment Schedule

                    </h3>


                    <div class="form-grid">


                        <!-- DATE -->

                        <div class="form-group">

                            <label for="appointment_date">

                                Appointment Date

                                <span>*</span>

                            </label>


                            <div class="input-wrapper">

                                <i class="fa-solid fa-calendar"></i>

                                <input
                                    type="date"
                                    id="appointment_date"
                                    name="appointment_date"
                                    min="<?php echo date('Y-m-d'); ?>"
                                    required
                                >

                            </div>

                        </div>



                        <!-- TIME -->

                        <div class="form-group">

                            <label for="appointment_time">

                                Preferred Time

                                <span>*</span>

                            </label>


                            <div class="input-wrapper">

                                <i class="fa-solid fa-clock"></i>

                                <input
                                    type="time"
                                    id="appointment_time"
                                    name="appointment_time"
                                    required
                                >

                            </div>

                        </div>


                    </div>

                </div>



                <!-- =========================
                     REASON
                ========================== -->

                <div class="form-section">

                    <h3>

                        <i class="fa-solid fa-comment-medical"></i>

                        Consultation Details

                    </h3>


                    <div class="form-group">

                        <label for="reason">

                            Reason for Appointment

                            <span>*</span>

                        </label>


                        <textarea
                            id="reason"
                            name="reason"
                            rows="5"
                            placeholder="Briefly describe your health concern or reason for consultation..."
                            required
                        ></textarea>


                        <small>
                            Please provide a short description to help the doctor understand your concern.
                        </small>

                    </div>

                </div>



                <!-- =========================
                     ACTION BUTTONS
                ========================== -->

                <div class="form-actions">


                    <a
                        href="doctors.php"
                        class="cancel-btn"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Back to Doctors

                    </a>


                    <button
                        type="submit"
                        class="confirm-btn"
                    >

                        <i class="fa-solid fa-calendar-check"></i>

                        Confirm Appointment

                    </button>


                </div>


            </form>

        </div>


    </div>


</main>


</body>

</html>