<?php

session_start();

/* =====================================================
   ADMIN ACCESS CHECK
===================================================== */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'admin'
) {
    header("Location: ../login.php");
    exit();
}


/* =====================================================
   DATABASE
===================================================== */

include("../config/database.php");


/* =====================================================
   DELETE DOCTOR
===================================================== */

if (
    isset($_GET['delete']) &&
    is_numeric($_GET['delete'])
) {

    $doctor_id = intval($_GET['delete']);

    $deleteQuery = mysqli_query(
        $conn,
        "DELETE FROM doctors WHERE id='$doctor_id'"
    );

    if ($deleteQuery) {

        header("Location: doctors.php?deleted=1");
        exit();

    } else {

        header("Location: doctors.php?error=1");
        exit();

    }
}


/* =====================================================
   SUCCESS / ERROR MESSAGE
===================================================== */

$message = "";
$messageType = "";

if (isset($_GET['deleted'])) {

    $message = "Doctor deleted successfully.";
    $messageType = "success";

}

if (isset($_GET['error'])) {

    $message = "Unable to delete doctor.";
    $messageType = "error";

}


/* =====================================================
   GET ALL DOCTORS
===================================================== */

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
        image,
        user_id
    FROM doctors
    ORDER BY id DESC
    "
);


if (!$query) {

    die(
        "Database Error: " .
        mysqli_error($conn)
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

<title>Manage Doctors | HealthSync</title>


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

    display: flex;

    min-height: 100vh;

}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    width: 250px;

    background: #0f172a;

    border-right: 1px solid #1e293b;

    position: fixed;

    left: 0;

    top: 0;

    bottom: 0;

    padding: 25px 15px;

}


/* LOGO */

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


/* SIDEBAR LINKS */

.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    margin-bottom: 6px;

    color: #94a3b8;

    text-decoration: none;

    border-radius: 8px;

    font-size: 14px;

    transition: 0.2s;

}

.sidebar a i {

    width: 20px;

    text-align: center;

}

.sidebar a:hover {

    background: #172554;

    color: #60a5fa;

}

.sidebar a.active {

    background: #1e3a8a;

    color: white;

}


/* LOGOUT */

.sidebar .logout {

    margin-top: 30px;

    color: #f87171;

}

.sidebar .logout:hover {

    background: #450a0a;

    color: #fca5a5;

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
   TOP BAR
===================================================== */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.page-title h1 {

    color: white;

    font-size: 27px;

}

.page-title p {

    color: #64748b;

    margin-top: 7px;

    font-size: 13px;

}


/* ADMIN PROFILE */

.admin-profile {

    display: flex;

    align-items: center;

    gap: 10px;

    background: #0f172a;

    border: 1px solid #1e293b;

    padding: 9px 14px;

    border-radius: 10px;

}

.admin-icon {

    width: 35px;

    height: 35px;

    border-radius: 50%;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

}

.admin-profile strong {

    color: white;

    font-size: 13px;

}

.admin-profile small {

    display: block;

    color: #64748b;

    font-size: 10px;

    margin-top: 3px;

}


/* =====================================================
   MESSAGE
===================================================== */

.message {

    padding: 13px 16px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 13px;

}

.message.success {

    background: #052e16;

    border: 1px solid #166534;

    color: #86efac;

}

.message.error {

    background: #450a0a;

    border: 1px solid #991b1b;

    color: #fca5a5;

}


/* =====================================================
   TOOLBAR
===================================================== */

.toolbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;

}

.doctor-count {

    color: #94a3b8;

    font-size: 13px;

}

.doctor-count strong {

    color: #60a5fa;

}


/* ADD BUTTON */

.add-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    background: #2563eb;

    color: white;

    padding: 11px 16px;

    border-radius: 8px;

    text-decoration: none;

    font-size: 13px;

    transition: 0.2s;

}

.add-btn:hover {

    background: #1d4ed8;

}


/* =====================================================
   TABLE CONTAINER
===================================================== */

.table-container {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 12px;

    overflow-x: auto;

}


/* =====================================================
   TABLE
===================================================== */

.doctor-table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1000px;

}

.doctor-table th {

    background: #111c31;

    color: #94a3b8;

    font-size: 11px;

    font-weight: 600;

    text-align: left;

    padding: 15px;

    border-bottom: 1px solid #1e293b;

}

.doctor-table td {

    padding: 15px;

    border-bottom: 1px solid #1e293b;

    color: #cbd5e1;

    font-size: 12px;

}

.doctor-table tr:last-child td {

    border-bottom: none;

}

.doctor-table tbody tr:hover {

    background: #111c31;

}


/* =====================================================
   DOCTOR INFO
===================================================== */

.doctor-info {

    display: flex;

    align-items: center;

    gap: 11px;

}


/* DOCTOR IMAGE */

.doctor-image {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    object-fit: cover;

    border: 2px solid #1e3a8a;

    background: #172554;

}


/* PLACEHOLDER */

.doctor-placeholder {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 16px;

}


.doctor-name {

    color: white;

    font-weight: 600;

    font-size: 12px;

}

.doctor-id {

    color: #64748b;

    font-size: 10px;

    margin-top: 3px;

}


/* =====================================================
   STATUS
===================================================== */

.status {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 10px;

    font-weight: 600;

}

.status.available {

    background: #052e16;

    color: #86efac;

}

.status.unavailable {

    background: #450a0a;

    color: #fca5a5;

}


/* =====================================================
   ACTION BUTTONS
===================================================== */

.actions {

    display: flex;

    gap: 7px;

}


.action-btn {

    width: 32px;

    height: 32px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 7px;

    text-decoration: none;

    font-size: 12px;

}


/* VIEW */

.view-btn {

    background: #172554;

    color: #60a5fa;

}

.view-btn:hover {

    background: #1e3a8a;

    color: white;

}


/* EDIT */

.edit-btn {

    background: #172554;

    color: #60a5fa;

}

.edit-btn:hover {

    background: #1e3a8a;

    color: white;

}


/* DELETE */

.delete-btn {

    background: #450a0a;

    color: #f87171;

}

.delete-btn:hover {

    background: #7f1d1d;

    color: white;

}


/* =====================================================
   EMPTY STATE
===================================================== */

.empty {

    text-align: center;

    padding: 60px 20px;

}

.empty i {

    font-size: 45px;

    color: #334155;

    margin-bottom: 15px;

}

.empty h3 {

    color: #cbd5e1;

    font-size: 17px;

}

.empty p {

    color: #64748b;

    font-size: 12px;

    margin-top: 7px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:750px) {

    .sidebar {

        width: 210px;

    }

    .main {

        margin-left: 210px;

        width: calc(100% - 210px);

        padding: 20px;

    }

    .topbar {

        align-items: flex-start;

        gap: 15px;

    }

}


@media(max-width:600px) {

    .dashboard {

        display: block;

    }

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        border-right: none;

        border-bottom: 1px solid #1e293b;

    }

    .main {

        margin-left: 0;

        width: 100%;

    }

    .topbar {

        flex-direction: column;

    }

    .toolbar {

        align-items: flex-start;

        gap: 12px;

        flex-direction: column;

    }

}

</style>

</head>


<body>


<div class="dashboard">


<!-- =================================================
     SIDEBAR
================================================== -->

<aside class="sidebar">


<div class="logo">

    <h2>
        Health<span>Sync</span>
    </h2>

    <p>
        Admin Portal
    </p>

</div>


<nav>

    <!-- DASHBOARD -->
    <a href="dashboard.php">

        <i class="fa-solid fa-house"></i>

        <span>
            Dashboard
        </span>

    </a>


    <!-- DOCTORS -->
    <a
        href="doctors.php"
        class="active"
    >

        <i class="fa-solid fa-user-doctor"></i>

        <span>
            Doctors
        </span>

    </a>


    <!-- PATIENTS -->
    <a href="patients.php">

        <i class="fa-solid fa-users"></i>

        <span>
            Patients
        </span>

    </a>


    <!-- APPOINTMENTS -->
    <a href="appointments.php">

        <i class="fa-solid fa-calendar-check"></i>

        <span>
            Appointments
        </span>

    </a>


    <!-- PRESCRIPTIONS -->
    <a href="prescriptions.php">

        <i class="fa-solid fa-file-prescription"></i>

        <span>
            Prescriptions
        </span>

    </a>


    <!-- REPORTS -->
    <a href="reports.php">

        <i class="fa-solid fa-chart-line"></i>

        <span>
            Reports
        </span>

    </a>


    <!-- SETTINGS -->
    <a href="settings.php">

        <i class="fa-solid fa-gear"></i>

        <span>
            Settings
        </span>

    </a>


    <!-- LOGOUT -->
    <a
        href="../logout.php"
        class="logout"
    >

        <i class="fa-solid fa-right-from-bracket"></i>

        <span>
            Logout
        </span>

    </a>

</nav>


</aside>



<!-- =================================================
     MAIN
================================================== -->

<main class="main">


    <!-- TOP BAR -->

    <div class="topbar">


        <div class="page-title">

            <h1>
                Manage Doctors
            </h1>

            <p>
                View and manage registered HealthSync doctors.
            </p>

        </div>


        <div class="admin-profile">


            <div class="admin-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $_SESSION['fullname']
                    );
                    ?>

                </strong>


                <small>
                    Administrator
                </small>

            </div>


        </div>


    </div>



    <!-- MESSAGE -->

    <?php if ($message != "") { ?>

        <div class="message <?php echo $messageType; ?>">

            <?php if ($messageType == "success") { ?>

                <i class="fa-solid fa-circle-check"></i>

            <?php } else { ?>

                <i class="fa-solid fa-circle-exclamation"></i>

            <?php } ?>

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php } ?>



    <!-- TOOLBAR -->

    <div class="toolbar">


        <div class="doctor-count">

            Total Doctors:

            <strong>
                <?php echo mysqli_num_rows($query); ?>
            </strong>

        </div>


        <a
            href="add-doctor.php"
            class="add-btn"
        >

            <i class="fa-solid fa-plus"></i>

            Add Doctor

        </a>


    </div>



    <!-- =================================================
         DOCTOR TABLE
    ================================================== -->

    <div class="table-container">


        <?php if (mysqli_num_rows($query) > 0) { ?>


        <table class="doctor-table">


            <thead>

                <tr>

                    <th>
                        DOCTOR
                    </th>

                    <th>
                        SPECIALIZATION
                    </th>

                    <th>
                        EMAIL
                    </th>

                    <th>
                        PHONE
                    </th>

                    <th>
                        EXPERIENCE
                    </th>

                    <th>
                        STATUS
                    </th>

                    <th>
                        ACTION
                    </th>

                </tr>

            </thead>



            <tbody>


            <?php while ($doctor = mysqli_fetch_assoc($query)) { ?>


                <tr>


                    <!-- =========================
                         DOCTOR
                    ========================== -->

                    <td>


                        <div class="doctor-info">


                            <?php

                            $image = $doctor['image'];

                            /*
                             * ACTUAL IMAGE LOCATION:
                             *
                             * HealthSync/
                             *   assets/
                             *     images/
                             *       doctors/
                             *
                             */

                            $imageFile =
                                __DIR__
                                . "/../assets/images/doctors/"
                                . $image;


                            $imageUrl =
                                "/HealthSync/assets/images/doctors/"
                                . rawurlencode($image);


                            if (
                                !empty($image) &&
                                file_exists($imageFile)
                            ) {

                            ?>


                                <img
                                    src="<?php echo htmlspecialchars($imageUrl); ?>"
                                    class="doctor-image"
                                    alt="<?php echo htmlspecialchars($doctor['fullname']); ?>"
                                >


                            <?php

                            } else {

                            ?>


                                <div class="doctor-placeholder">

                                    <i class="fa-solid fa-user-doctor"></i>

                                </div>


                            <?php

                            }

                            ?>


                            <div>


                                <div class="doctor-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $doctor['fullname']
                                    );

                                    ?>

                                </div>


                                <div class="doctor-id">

                                    ID #<?php echo $doctor['id']; ?>

                                </div>


                            </div>


                        </div>


                    </td>



                    <!-- =========================
                         SPECIALIZATION
                    ========================== -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $doctor['specialization']
                        );

                        ?>

                    </td>



                    <!-- =========================
                         EMAIL
                    ========================== -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $doctor['email']
                        );

                        ?>

                    </td>



                    <!-- =========================
                         PHONE
                    ========================== -->

                    <td>

                        <?php

                        echo !empty($doctor['phone'])
                            ? htmlspecialchars($doctor['phone'])
                            : "Not provided";

                        ?>

                    </td>



                    <!-- =========================
                         EXPERIENCE
                    ========================== -->

                    <td>

                        <?php

                        if (
                            $doctor['experience'] !== null &&
                            $doctor['experience'] !== ''
                        ) {

                            echo htmlspecialchars(
                                $doctor['experience']
                            ) . " years";

                        } else {

                            echo "N/A";

                        }

                        ?>

                    </td>



                    <!-- =========================
                         STATUS
                    ========================== -->

                    <td>


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


                    </td>



                    <!-- =========================
                         ACTIONS
                    ========================== -->

                    <td>


                        <div class="actions">


                            <!-- VIEW -->

                            <a
                                href="view-doctor.php?id=<?php echo $doctor['id']; ?>"
                                class="action-btn view-btn"
                                title="View Doctor"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </a>



                            <!-- EDIT -->

                            <a
                                href="edit-doctor.php?id=<?php echo $doctor['id']; ?>"
                                class="action-btn edit-btn"
                                title="Edit Doctor"
                            >

                                <i class="fa-solid fa-pen"></i>

                            </a>



                            <!-- DELETE -->

                            <a
                                href="doctors.php?delete=<?php echo $doctor['id']; ?>"
                                class="action-btn delete-btn"
                                title="Delete Doctor"
                                onclick="return confirm('Are you sure you want to delete this doctor?');"
                            >

                                <i class="fa-solid fa-trash"></i>

                            </a>


                        </div>


                    </td>


                </tr>


            <?php } ?>


            </tbody>


        </table>


        <?php } else { ?>


            <div class="empty">


                <i class="fa-solid fa-user-doctor"></i>


                <h3>
                    No Doctors Found
                </h3>


                <p>
                    There are currently no doctors registered.
                </p>


            </div>


        <?php } ?>


    </div>


</main>


</div>


</body>

</html>