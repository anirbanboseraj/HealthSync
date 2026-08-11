<?php

session_start();

include("../config/database.php");


// =====================================================
// CHECK DOCTOR LOGIN
// =====================================================

if(
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] != 'doctor'
)
{
    header("Location: ../login.php");
    exit();
}


// =====================================================
// GET LOGGED-IN USER
// =====================================================

$user_id = $_SESSION['user_id'];


// =====================================================
// GET DOCTOR INFORMATION
// =====================================================

$query = mysqli_query(
    $conn,
    "
    SELECT *
    FROM doctors
    WHERE user_id='$user_id'
    LIMIT 1
    "
);


if(mysqli_num_rows($query) == 0)
{
    die("Doctor profile not found.");
}


$doctor = mysqli_fetch_assoc($query);


// =====================================================
// IMAGE
// =====================================================

if(!empty($doctor['image']))
{
    $profileImage =
        "../assets/images/doctors/" .
        $doctor['image'];
}
else
{
    $profileImage = "";
}


// =====================================================
// HEADER
// =====================================================

include("../includes/header.php");

?>

<div class="doctor-profile-page">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">

            <h2>
                Health<span>Sync</span>
            </h2>

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


            <li>

                <a href="prescriptions.php">

                    <i class="fa-solid fa-file-prescription"></i>

                    Prescriptions

                </a>

            </li>


            <li class="active">

                <a href="profile.php">

                    <i class="fa-solid fa-user-doctor"></i>

                    Profile

                </a>

            </li>


            <li>

                <a href="logout.php">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Logout

                </a>

            </li>

        </ul>

    </aside>



    <!-- =================================================
         MAIN
    ================================================== -->

    <main class="profile-main">


        <div class="profile-header">

            <div>

                <h1>My Profile</h1>

                <p>
                    Manage your professional HealthSync profile.
                </p>

            </div>


            <a
                href="edit-profile.php"
                class="edit-btn"
            >

                <i class="fa-solid fa-pen"></i>

                Edit Profile

            </a>

        </div>



        <!-- =================================================
             PROFILE TOP CARD
        ================================================== -->

        <div class="profile-card">


            <div class="profile-top">


                <!-- PROFILE IMAGE -->

                <div class="profile-photo">


                    <?php if($profileImage != "") { ?>


                        <img
                            src="<?php echo htmlspecialchars($profileImage); ?>"
                            alt="Doctor Profile"
                        >


                    <?php } else { ?>


                        <div class="default-photo">

                            <i class="fa-solid fa-user-doctor"></i>

                        </div>


                    <?php } ?>


                </div>



                <!-- BASIC INFORMATION -->

                <div class="basic-info">


                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $doctor['fullname']
                        );
                        ?>

                    </h2>


                    <p class="specialization">

                        <i class="fa-solid fa-stethoscope"></i>

                        <?php
                        echo htmlspecialchars(
                            $doctor['specialization']
                        );
                        ?>

                    </p>


                    <div class="availability">


                        <span
                            class="<?php
                            echo (
                                $doctor['status'] == 'Available'
                            )
                            ? 'available'
                            : 'unavailable';
                            ?>"
                        >

                            <span class="status-dot"></span>

                            <?php
                            echo htmlspecialchars(
                                $doctor['status']
                            );
                            ?>

                        </span>


                    </div>


                </div>


            </div>



            <!-- =================================================
                 INFORMATION
            ================================================== -->

            <div class="information-section">


                <h3>

                    <i class="fa-solid fa-circle-info"></i>

                    Professional Information

                </h3>



                <div class="info-grid">


                    <!-- EMAIL -->

                    <div class="info-item">

                        <span class="info-label">

                            <i class="fa-solid fa-envelope"></i>

                            Email

                        </span>


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $doctor['email']
                            );
                            ?>

                        </strong>

                    </div>



                    <!-- PHONE -->

                    <div class="info-item">

                        <span class="info-label">

                            <i class="fa-solid fa-phone"></i>

                            Phone

                        </span>


                        <strong>

                            <?php
                            echo !empty($doctor['phone'])
                                ? htmlspecialchars(
                                    $doctor['phone']
                                )
                                : "Not provided";
                            ?>

                        </strong>

                    </div>



                    <!-- QUALIFICATION -->

                    <div class="info-item">

                        <span class="info-label">

                            <i class="fa-solid fa-graduation-cap"></i>

                            Qualification

                        </span>


                        <strong>

                            <?php
                            echo !empty($doctor['qualification'])
                                ? htmlspecialchars(
                                    $doctor['qualification']
                                )
                                : "Not provided";
                            ?>

                        </strong>

                    </div>



                    <!-- EXPERIENCE -->

                    <div class="info-item">

                        <span class="info-label">

                            <i class="fa-solid fa-briefcase"></i>

                            Experience

                        </span>


                        <strong>

                            <?php

                            if(
                                $doctor['experience'] !== null &&
                                $doctor['experience'] !== ''
                            )
                            {
                                echo
                                    htmlspecialchars(
                                        $doctor['experience']
                                    )
                                    . " Years";
                            }
                            else
                            {
                                echo "Not provided";
                            }

                            ?>

                        </strong>

                    </div>


                </div>

            </div>



            <!-- =================================================
                 ABOUT
            ================================================== -->

            <div class="about-section">


                <h3>

                    <i class="fa-solid fa-user-doctor"></i>

                    About Me

                </h3>


                <p>

                    <?php

                    if(!empty($doctor['about']))
                    {

                        echo nl2br(
                            htmlspecialchars(
                                $doctor['about']
                            )
                        );

                    }
                    else
                    {

                        echo
                            "No information has been added yet.";

                    }

                    ?>

                </p>


            </div>



            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div class="profile-actions">


                <a
                    href="edit-profile.php"
                    class="primary-btn"
                >

                    <i class="fa-solid fa-pen"></i>

                    Edit Profile

                </a>


                <a
                    href="change-password.php"
                    class="secondary-btn"
                >

                    <i class="fa-solid fa-lock"></i>

                    Change Password

                </a>


            </div>


        </div>


    </main>

</div>



<style>

/* =====================================================
   PAGE
===================================================== */

.doctor-profile-page {

    display: flex;

    min-height: 100vh;

    background: #020617;

    color: #e2e8f0;

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


.sidebar-logo {

    text-align: center;

    margin-bottom: 35px;

}


.sidebar-logo h2 {

    color: white;

    font-size: 25px;

}


.sidebar-logo span {

    color: #3b82f6;

}


.sidebar ul {

    list-style: none;

    margin: 0;

    padding: 0;

}


.sidebar li {

    margin-bottom: 8px;

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    color: #94a3b8;

    text-decoration: none;

    border-radius: 8px;

    transition: .3s;

}


.sidebar a:hover,
.sidebar li.active a {

    background: #1e3a8a;

    color: white;

}


.sidebar i {

    width: 20px;

}


/* =====================================================
   MAIN
===================================================== */

.profile-main {

    flex: 1;

    padding: 40px;

    max-width: 1200px;

}


/* =====================================================
   HEADER
===================================================== */

.profile-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 30px;

}


.profile-header h1 {

    margin: 0;

    color: white;

    font-size: 30px;

}


.profile-header p {

    margin-top: 8px;

    color: #94a3b8;

}


.edit-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    padding: 11px 18px;

    border-radius: 8px;

}


.edit-btn:hover {

    background: #1d4ed8;

}


/* =====================================================
   PROFILE CARD
===================================================== */

.profile-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    overflow: hidden;

}


/* =====================================================
   PROFILE TOP
===================================================== */

.profile-top {

    display: flex;

    align-items: center;

    gap: 30px;

    padding: 35px;

    background: linear-gradient(
        135deg,
        #0f172a,
        #172554
    );

    border-bottom: 1px solid #1e293b;

}


/* =====================================================
   PHOTO
===================================================== */

.profile-photo {

    width: 140px;

    height: 140px;

    flex-shrink: 0;

}


.profile-photo img {

    width: 140px;

    height: 140px;

    object-fit: cover;

    border-radius: 50%;

    border: 5px solid #3b82f6;

}


.default-photo {

    width: 140px;

    height: 140px;

    border-radius: 50%;

    background: #172554;

    border: 5px solid #3b82f6;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 55px;

    color: #60a5fa;

}


/* =====================================================
   BASIC INFO
===================================================== */

.basic-info h2 {

    margin: 0 0 10px;

    color: white;

    font-size: 28px;

}


.specialization {

    margin: 0 0 15px;

    color: #93c5fd;

    font-size: 16px;

}


.availability span {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: 600;

}


.available {

    background: #064e3b;

    color: #6ee7b7;

}


.unavailable {

    background: #450a0a;

    color: #fca5a5;

}


.status-dot {

    width: 8px;

    height: 8px;

    border-radius: 50%;

    background: currentColor;

}


/* =====================================================
   INFORMATION
===================================================== */

.information-section {

    padding: 30px 35px;

    border-bottom: 1px solid #1e293b;

}


.information-section h3,
.about-section h3 {

    margin: 0 0 22px;

    color: white;

    font-size: 18px;

}


.information-section h3 i,
.about-section h3 i {

    color: #3b82f6;

    margin-right: 8px;

}


.info-grid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 18px;

}


.info-item {

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 9px;

    padding: 16px;

}


.info-label {

    display: block;

    color: #64748b;

    font-size: 13px;

    margin-bottom: 7px;

}


.info-label i {

    margin-right: 6px;

    color: #60a5fa;

}


.info-item strong {

    color: #e2e8f0;

    font-size: 14px;

    word-break: break-word;

}


/* =====================================================
   ABOUT
===================================================== */

.about-section {

    padding: 30px 35px;

    border-bottom: 1px solid #1e293b;

}


.about-section p {

    margin: 0;

    line-height: 1.8;

    color: #94a3b8;

}


/* =====================================================
   ACTIONS
===================================================== */

.profile-actions {

    display: flex;

    gap: 12px;

    padding: 25px 35px;

}


.primary-btn,
.secondary-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 11px 18px;

    border-radius: 8px;

    text-decoration: none;

}


.primary-btn {

    background: #2563eb;

    color: white;

}


.primary-btn:hover {

    background: #1d4ed8;

}


.secondary-btn {

    border: 1px solid #334155;

    color: #cbd5e1;

}


.secondary-btn:hover {

    background: #1e293b;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:800px)
{

    .sidebar {

        width: 200px;

    }


    .profile-main {

        padding: 25px;

    }


    .info-grid {

        grid-template-columns: 1fr;

    }

}


@media(max-width:600px)
{

    .doctor-profile-page {

        flex-direction: column;

    }


    .sidebar {

        width: 100%;

        min-height: auto;

    }


    .profile-main {

        padding: 20px;

    }


    .profile-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .profile-top {

        flex-direction: column;

        text-align: center;

    }


    .profile-actions {

        flex-direction: column;

    }


    .primary-btn,
    .secondary-btn {

        justify-content: center;

    }

}

</style>


<?php

include("../includes/footer.php");

?>