<?php

session_start();

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| PATIENT ID
|--------------------------------------------------------------------------
*/

$patient_id = intval($_SESSION['user_id']);


/*
|--------------------------------------------------------------------------
| PATIENT INFORMATION
|--------------------------------------------------------------------------
|
| Patients are stored in the users table.
|
*/

$patient_query = mysqli_query(
    $conn,
    "
    SELECT
        id,
        fullname,
        email,
        phone,
        gender,
        dob,
        blood_group,
        address,
        image
    FROM users
    WHERE id = $patient_id
      AND role = 'patient'
    LIMIT 1
    "
);


/*
|--------------------------------------------------------------------------
| CHECK PATIENT
|--------------------------------------------------------------------------
*/

if (!$patient_query || mysqli_num_rows($patient_query) === 0) {

    session_destroy();

    header("Location: ../login.php");
    exit();

}


$patient = mysqli_fetch_assoc($patient_query);


/*
|--------------------------------------------------------------------------
| BASIC INFORMATION
|--------------------------------------------------------------------------
*/

$fullname = $patient['fullname'] ?? 'Patient';

$email = $patient['email'] ?? '';

$phone = $patient['phone'] ?? '';

$gender = $patient['gender'] ?? '';

$dob = $patient['dob'] ?? '';

$blood_group = $patient['blood_group'] ?? '';

$address = $patient['address'] ?? '';

$image = $patient['image'] ?? '';


/*
|--------------------------------------------------------------------------
| APPOINTMENT STATISTICS
|--------------------------------------------------------------------------
*/

$total_appointments_query = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM appointments
    WHERE patient_id = $patient_id
    "
);

$total_appointments = 0;

if ($total_appointments_query) {

    $row = mysqli_fetch_assoc(
        $total_appointments_query
    );

    $total_appointments =
        intval($row['total']);

}


/*
|--------------------------------------------------------------------------
| PENDING APPOINTMENTS
|--------------------------------------------------------------------------
*/

$pending_query = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM appointments
    WHERE patient_id = $patient_id
      AND status = 'Pending'
    "
);

$pending_appointments = 0;

if ($pending_query) {

    $row = mysqli_fetch_assoc(
        $pending_query
    );

    $pending_appointments =
        intval($row['total']);

}


/*
|--------------------------------------------------------------------------
| APPROVED APPOINTMENTS
|--------------------------------------------------------------------------
*/

$approved_query = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM appointments
    WHERE patient_id = $patient_id
      AND status = 'Approved'
    "
);

$approved_appointments = 0;

if ($approved_query) {

    $row = mysqli_fetch_assoc(
        $approved_query
    );

    $approved_appointments =
        intval($row['total']);

}


/*
|--------------------------------------------------------------------------
| PRESCRIPTION COUNT
|--------------------------------------------------------------------------
*/

$prescription_query = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM prescriptions
    WHERE patient_id = $patient_id
    "
);

$total_prescriptions = 0;

if ($prescription_query) {

    $row = mysqli_fetch_assoc(
        $prescription_query
    );

    $total_prescriptions =
        intval($row['total']);

}


/*
|--------------------------------------------------------------------------
| UPCOMING APPOINTMENTS
|--------------------------------------------------------------------------
*/

$upcoming_query = mysqli_query(
    $conn,
    "
    SELECT
        appointments.id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.problem,
        appointments.status,
        doctors.fullname AS doctor_name,
        doctors.specialization
    FROM appointments

    INNER JOIN doctors
        ON appointments.doctor_id = doctors.id

    WHERE appointments.patient_id = $patient_id

      AND appointments.appointment_date >= CURDATE()

    ORDER BY
        appointments.appointment_date ASC,
        appointments.appointment_time ASC

    LIMIT 5
    "
);


/*
|--------------------------------------------------------------------------
| NOTIFICATION COUNT
|--------------------------------------------------------------------------
*/

$notification_query = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = $patient_id
      AND is_read = 0
    "
);

$unread_notifications = 0;

if ($notification_query) {

    $row = mysqli_fetch_assoc(
        $notification_query
    );

    $unread_notifications =
        intval($row['total']);

}


/*
|--------------------------------------------------------------------------
| PATIENT IMAGE
|--------------------------------------------------------------------------
*/

$patient_image_url = '';

if (!empty($image)) {

    $image_file =
        __DIR__
        . "/../assets/images/"
        . $image;

    if (file_exists($image_file)) {

        $patient_image_url =
            "/HealthSync/assets/images/"
            . rawurlencode($image);

    }

}


/*
|--------------------------------------------------------------------------
| DATE FORMAT
|--------------------------------------------------------------------------
*/

$formatted_dob = 'Not provided';

if (!empty($dob)) {

    $formatted_dob =
        date(
            "d M Y",
            strtotime($dob)
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
        Patient Dashboard - HealthSync
    </title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- PATIENT DASHBOARD CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/dashboard.css"
    >

</head>


<body>


<div class="dashboard">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <?php

    include "../includes/patient-sidebar.php";

    ?>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="main-content">


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <header class="topbar">


            <div class="page-title">

                <h1>
                    Patient Dashboard
                </h1>

                <p>
                    Welcome back,
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $fullname
                        );
                        ?>
                    </strong>
                </p>

            </div>


            <!-- NOTIFICATION -->

            <div class="topbar-actions">


                <a
                    href="notifications.php"
                    class="notification-btn"
                    title="Notifications"
                >

                    <i
                        class="fa-solid fa-bell"
                    ></i>


                    <?php if (
                        $unread_notifications > 0
                    ): ?>

                        <span class="notification-badge">

                            <?php

                            echo $unread_notifications > 9
                                ? '9+'
                                : $unread_notifications;

                            ?>

                        </span>

                    <?php endif; ?>

                </a>


                <!-- PROFILE -->

                <div class="patient-profile">


                    <?php if (
                        !empty(
                            $patient_image_url
                        )
                    ): ?>

                        <img
                            src="<?php
                            echo htmlspecialchars(
                                $patient_image_url
                            );
                            ?>"
                            alt="Patient"
                        >

                    <?php else: ?>

                        <div class="profile-icon">

                            <i
                                class="fa-solid fa-user"
                            ></i>

                        </div>

                    <?php endif; ?>


                    <div>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $fullname
                            );

                            ?>

                        </strong>

                        <small>
                            Patient
                        </small>

                    </div>


                </div>


            </div>


        </header>



        <!-- =================================================
             WELCOME CARD
        ================================================== -->

        <section class="welcome-card">


            <div>


                <span class="welcome-label">

                    HEALTHSYNC

                </span>


                <h2>

                    Hello,
                    <?php

                    echo htmlspecialchars(
                        $fullname
                    );

                    ?>

                    👋

                </h2>


                <p>

                    Manage your appointments,
                    prescriptions and health
                    information from one place.

                </p>


                <a
                    href="book-appointment.php"
                    class="primary-btn"
                >

                    <i
                        class="fa-solid fa-calendar-plus"
                    ></i>

                    Book Appointment

                </a>


            </div>


            <div class="welcome-icon">

                <i
                    class="fa-solid fa-heart-pulse"
                ></i>

            </div>


        </section>



        <!-- =================================================
             STATISTICS
        ================================================== -->

        <section class="stats-grid">


            <!-- TOTAL APPOINTMENTS -->

            <div class="stat-card">

                <div class="stat-icon blue">

                    <i
                        class="fa-solid fa-calendar-check"
                    ></i>

                </div>


                <div>

                    <span>
                        Total Appointments
                    </span>

                    <strong>

                        <?php

                        echo $total_appointments;

                        ?>

                    </strong>

                </div>

            </div>



            <!-- PENDING -->

            <div class="stat-card">

                <div class="stat-icon yellow">

                    <i
                        class="fa-solid fa-clock"
                    ></i>

                </div>


                <div>

                    <span>
                        Pending
                    </span>

                    <strong>

                        <?php

                        echo $pending_appointments;

                        ?>

                    </strong>

                </div>

            </div>



            <!-- APPROVED -->

            <div class="stat-card">

                <div class="stat-icon green">

                    <i
                        class="fa-solid fa-circle-check"
                    ></i>

                </div>


                <div>

                    <span>
                        Approved
                    </span>

                    <strong>

                        <?php

                        echo $approved_appointments;

                        ?>

                    </strong>

                </div>

            </div>



            <!-- PRESCRIPTIONS -->

            <div class="stat-card">

                <div class="stat-icon purple">

                    <i
                        class="fa-solid fa-prescription-bottle-medical"
                    ></i>

                </div>


                <div>

                    <span>
                        Prescriptions
                    </span>

                    <strong>

                        <?php

                        echo $total_prescriptions;

                        ?>

                    </strong>

                </div>

            </div>


        </section>



        <!-- =================================================
             CONTENT GRID
        ================================================== -->

        <section class="dashboard-grid">


            <!-- =================================================
                 UPCOMING APPOINTMENTS
            ================================================== -->

            <div class="dashboard-card appointments-card">


                <div class="card-header">


                    <div>

                        <h3>
                            Upcoming Appointments
                        </h3>

                        <p>
                            Your next scheduled visits
                        </p>

                    </div>


                    <a
                        href="appointments.php"
                    >

                        View All

                    </a>


                </div>



                <div class="appointment-list">


                    <?php

                    if (
                        $upcoming_query &&
                        mysqli_num_rows(
                            $upcoming_query
                        ) > 0
                    ):

                    ?>


                        <?php

                        while (
                            $appointment =
                            mysqli_fetch_assoc(
                                $upcoming_query
                            )
                        ):

                        ?>


                            <div
                                class="appointment-item"
                            >


                                <div
                                    class="appointment-date"
                                >

                                    <strong>

                                        <?php

                                        echo date(
                                            "d",
                                            strtotime(
                                                $appointment[
                                                    'appointment_date'
                                                ]
                                            )
                                        );

                                        ?>

                                    </strong>


                                    <span>

                                        <?php

                                        echo date(
                                            "M",
                                            strtotime(
                                                $appointment[
                                                    'appointment_date'
                                                ]
                                            )
                                        );

                                        ?>

                                    </span>

                                </div>



                                <div
                                    class="appointment-details"
                                >

                                    <strong>

                                        Dr.
                                        <?php

                                        echo htmlspecialchars(
                                            $appointment[
                                                'doctor_name'
                                            ]
                                        );

                                        ?>

                                    </strong>


                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $appointment[
                                                'specialization'
                                            ]
                                        );

                                        ?>

                                    </span>


                                    <small>

                                        <i
                                            class="fa-regular fa-clock"
                                        ></i>

                                        <?php

                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $appointment[
                                                    'appointment_time'
                                                ]
                                            )
                                        );

                                        ?>

                                    </small>

                                </div>



                                <div>

                                    <?php

                                    $status =
                                        strtolower(
                                            $appointment[
                                                'status'
                                            ]
                                        );

                                    ?>


                                    <span
                                        class="appointment-status
                                        <?php
                                        echo htmlspecialchars(
                                            $status
                                        );
                                        ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $appointment[
                                                'status'
                                            ]
                                        );

                                        ?>

                                    </span>

                                </div>


                            </div>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <div class="empty-state">

                            <i
                                class="fa-regular fa-calendar-xmark"
                            ></i>

                            <h4>
                                No Upcoming Appointments
                            </h4>

                            <p>
                                You don't have any
                                upcoming appointments.
                            </p>


                            <a
                                href="book-appointment.php"
                                class="secondary-btn"
                            >

                                Book Appointment

                            </a>

                        </div>


                    <?php endif; ?>


                </div>


            </div>



            <!-- =================================================
                 PATIENT INFORMATION
            ================================================== -->

            <div class="dashboard-card profile-card">


                <div class="card-header">


                    <div>

                        <h3>
                            My Health Profile
                        </h3>

                        <p>
                            Your personal information
                        </p>

                    </div>


                    <a
                        href="profile.php"
                        title="Edit Profile"
                    >

                        <i
                            class="fa-solid fa-pen"
                        ></i>

                    </a>


                </div>



                <div class="health-profile">


                    <div
                        class="health-profile-avatar"
                    >


                        <?php if (
                            !empty(
                                $patient_image_url
                            )
                        ): ?>

                            <img
                                src="<?php
                                echo htmlspecialchars(
                                    $patient_image_url
                                );
                                ?>"
                                alt="Patient"
                            >

                        <?php else: ?>

                            <i
                                class="fa-solid fa-user"
                            ></i>

                        <?php endif; ?>


                    </div>


                    <div>

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $fullname
                            );

                            ?>

                        </h3>

                        <span>
                            Patient
                        </span>

                    </div>


                </div>



                <div
                    class="profile-details"
                >


                    <div>

                        <span>
                            <i
                                class="fa-solid fa-envelope"
                            ></i>

                            Email
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $email
                            );

                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            <i
                                class="fa-solid fa-phone"
                            ></i>

                            Phone
                        </span>

                        <strong>

                            <?php

                            echo !empty(
                                $phone
                            )
                                ? htmlspecialchars(
                                    $phone
                                )
                                : 'Not provided';

                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            <i
                                class="fa-solid fa-venus-mars"
                            ></i>

                            Gender
                        </span>

                        <strong>

                            <?php

                            echo !empty(
                                $gender
                            )
                                ? htmlspecialchars(
                                    $gender
                                )
                                : 'Not provided';

                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            <i
                                class="fa-solid fa-calendar"
                            ></i>

                            Date of Birth
                        </span>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $formatted_dob
                            );

                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            <i
                                class="fa-solid fa-droplet"
                            ></i>

                            Blood Group
                        </span>

                        <strong>

                            <?php

                            echo !empty(
                                $blood_group
                            )
                                ? htmlspecialchars(
                                    $blood_group
                                )
                                : 'Not provided';

                            ?>

                        </strong>

                    </div>


                </div>


            </div>


        </section>



        <!-- =================================================
             QUICK ACTIONS
        ================================================== -->

        <section
            class="dashboard-card quick-actions"
        >


            <div class="card-header">


                <div>

                    <h3>
                        Quick Actions
                    </h3>

                    <p>
                        Frequently used services
                    </p>

                </div>


            </div>



            <div class="quick-action-grid">


                <a
                    href="book-appointment.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon blue">

                        <i
                            class="fa-solid fa-calendar-plus"
                        ></i>

                    </div>

                    <div>

                        <strong>
                            Book Appointment
                        </strong>

                        <span>
                            Schedule a doctor visit
                        </span>

                    </div>

                </a>



                <a
                    href="appointments.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon green">

                        <i
                            class="fa-solid fa-calendar-check"
                        ></i>

                    </div>

                    <div>

                        <strong>
                            My Appointments
                        </strong>

                        <span>
                            View appointment history
                        </span>

                    </div>

                </a>



                <a
                    href="prescriptions.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon purple">

                        <i
                            class="fa-solid fa-prescription-bottle-medical"
                        ></i>

                    </div>

                    <div>

                        <strong>
                            Prescriptions
                        </strong>

                        <span>
                            View your medicines
                        </span>

                    </div>

                </a>



                <a
                    href="medical-records.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon orange">

                        <i
                            class="fa-solid fa-file-medical"
                        ></i>

                    </div>

                    <div>

                        <strong>
                            Medical Records
                        </strong>

                        <span>
                            View your health records
                        </span>

                    </div>

                </a>


            </div>


        </section>


    </main>


</div>


</body>

</html>