<?php

session_start();

require_once("../config/database.php");

/*
|--------------------------------------------------------------------------
| Get Doctors
|--------------------------------------------------------------------------
| Doctors are stored in the doctors table.
*/

$sql = "SELECT 
            id,
            fullname,
            specialization,
            email,
            phone,
            experience,
            status,
            qualification,
            about,
            image
        FROM doctors
        WHERE status = 'Available'
        ORDER BY id ASC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    die("Doctor Query Error: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Doctors | HealthSync</title>

    <!-- Existing dashboard CSS -->
    <link rel="stylesheet"
          href="../assets/css/dashboard.css">

    <!-- Doctors CSS -->
    <link rel="stylesheet"
          href="../assets/css/doctors.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

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
         PAGE HEADER
    ================================================== -->

    <div class="page-header">

        <h1>
            Find Doctors
        </h1>

        <p>
            Choose a doctor and book your appointment.
        </p>

    </div>



    <!-- =================================================
         DOCTOR GRID
    ================================================== -->

    <div class="doctor-grid">


        <?php if (mysqli_num_rows($query) > 0): ?>


            <?php while ($doctor = mysqli_fetch_assoc($query)): ?>


                <?php

                /* -----------------------------------------
                   Doctor Name
                   Prevent "Dr. Dr. Name"
                ------------------------------------------ */

                $doctorName = trim($doctor['fullname']);

                if (stripos($doctorName, 'Dr.') === 0) {
                    $displayName = $doctorName;
                } else {
                    $displayName = 'Dr. ' . $doctorName;
                }


                /* -----------------------------------------
                   Doctor Image
                ------------------------------------------ */

                $imageName = trim($doctor['image']);

                $imagePath = "../assets/images/doctors/" . $imageName;

                $hasImage = (
                    !empty($imageName) &&
                    file_exists(__DIR__ . "/../assets/images/doctors/" . $imageName)
                );


                /* -----------------------------------------
                   About
                   Ignore useless values such as !!!
                ------------------------------------------ */

                $about = trim($doctor['about']);

                if (
                    $about === "!!!" ||
                    $about === "!" ||
                    $about === "-"
                ) {
                    $about = "";
                }

                ?>


                <!-- =================================================
                     DOCTOR CARD
                ================================================== -->

                <div class="doctor-card">


                    <!-- Doctor Image -->

                    <div class="doctor-image-wrapper">

                        <?php if ($hasImage): ?>

                            <img
                                src="<?php echo htmlspecialchars($imagePath); ?>"
                                alt="<?php echo htmlspecialchars($displayName); ?>"
                                class="doctor-image"
                            >

                        <?php else: ?>

                            <!-- Clean placeholder instead of broken image -->

                            <div class="doctor-placeholder">

                                <i class="fa-solid fa-user-doctor"></i>

                            </div>

                        <?php endif; ?>

                    </div>



                    <!-- Doctor Name -->

                    <h2 class="doctor-name">

                        <?php echo htmlspecialchars($displayName); ?>

                    </h2>



                    <!-- Specialization -->

                    <h4 class="doctor-specialization">

                        <i class="fa-solid fa-stethoscope"></i>

                        <?php echo htmlspecialchars($doctor['specialization']); ?>

                    </h4>



                    <!-- Doctor Information -->

                    <div class="doctor-details">


                        <!-- Qualification -->

                        <?php if (!empty($doctor['qualification'])): ?>

                            <div class="doctor-info">

                                <span>

                                    <i class="fa-solid fa-graduation-cap"></i>

                                    Qualification

                                </span>

                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $doctor['qualification']
                                    );
                                    ?>

                                </p>

                            </div>

                        <?php endif; ?>



                        <!-- Experience -->

                        <?php if (!empty($doctor['experience'])): ?>

                            <div class="doctor-info">

                                <span>

                                    <i class="fa-solid fa-briefcase"></i>

                                    Experience

                                </span>

                                <p>

                                    <?php
                                    echo htmlspecialchars(
                                        $doctor['experience']
                                    );
                                    ?>

                                    Years

                                </p>

                            </div>

                        <?php endif; ?>



                        <!-- About -->

                        <?php if (!empty($about)): ?>

                            <p class="doctor-about">

                                <?php
                                echo htmlspecialchars($about);
                                ?>

                            </p>

                        <?php endif; ?>


                    </div>



                    <!-- =================================================
                         BUTTONS
                    ================================================== -->

                    <div class="doctor-actions">


                        <!-- View Profile -->

                        <a
                            href="doctor-details.php?id=<?php echo (int)$doctor['id']; ?>"
                            class="doctor-btn"
                        >

                            <i class="fa-solid fa-user-doctor"></i>

                            View Profile

                        </a>



                        <!-- Book Appointment -->

                        <a
                            href="book-appointment.php?doctor_id=<?php echo (int)$doctor['id']; ?>"
                            class="doctor-btn secondary"
                        >

                            <i class="fa-solid fa-calendar-plus"></i>

                            Book Appointment

                        </a>


                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- =================================================
                 NO DOCTORS
            ================================================== -->

            <div class="no-doctors">

                <i class="fa-solid fa-user-doctor"></i>

                <h2>
                    No Doctors Available
                </h2>

                <p>
                    There are currently no available doctors.
                </p>

            </div>


        <?php endif; ?>


    </div>


</main>


</body>

</html>