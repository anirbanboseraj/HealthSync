<?php

session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit();
}

require_once("../config/database.php");


/* ================================
   GET PATIENTS
================================ */

$sql = "
    SELECT
        id,
        fullname,
        email,
        role,
        image
    FROM users
    WHERE role = 'patient'
    ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

$patient_count = mysqli_num_rows($result);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Patients | HealthSync</title>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>

<style>

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

.dashboard {

    min-height: 100vh;

    display: flex;

}


/* SIDEBAR */

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


/* MAIN */

.main {

    margin-left: 250px;

    width: calc(100% - 250px);

    padding: 30px;

}


/* TOPBAR */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 30px;

}

.page-title h1 {

    color: white;

    font-size: 28px;

}

.page-title p {

    color: #64748b;

    font-size: 13px;

    margin-top: 7px;

}

.admin-profile {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 10px 15px;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 10px;

}

.admin-icon {

    width: 38px;

    height: 38px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #1e3a8a;

    color: #60a5fa;

}

.admin-profile strong {

    display: block;

    color: white;

    font-size: 13px;

}

.admin-profile small {

    color: #64748b;

    font-size: 10px;

}


/* STAT */

.stat-card {

    width: 255px;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    padding: 25px;

    margin-bottom: 28px;

}

.stat-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

    border-radius: 10px;

    font-size: 22px;

    margin-bottom: 17px;

}

.stat-number {

    font-size: 30px;

    font-weight: bold;

    color: white;

}

.stat-label {

    color: #64748b;

    font-size: 13px;

    margin-top: 5px;

}


/* TABLE */

.table-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    overflow: hidden;

}

.table-header {

    padding: 25px;

    border-bottom: 1px solid #1e293b;

}

.table-header h2 {

    color: white;

    font-size: 19px;

}

.table-header p {

    color: #64748b;

    font-size: 12px;

    margin-top: 6px;

}

.table-wrapper {

    overflow-x: auto;

}

.patient-table {

    width: 100%;

    border-collapse: collapse;

}

.patient-table th {

    text-align: left;

    padding: 16px 22px;

    background: #111827;

    color: #64748b;

    font-size: 11px;

    border-bottom: 1px solid #1e293b;

}

.patient-table td {

    padding: 18px 22px;

    color: #cbd5e1;

    font-size: 13px;

    border-bottom: 1px solid #1e293b;

}

.patient-table tr:hover {

    background: #111c31;

}

.patient-table tr:last-child td {

    border-bottom: none;

}


/* PATIENT */

.patient-info {

    display: flex;

    align-items: center;

    gap: 12px;

}

.patient-avatar {

    width: 42px;

    height: 42px;

    min-width: 42px;

    border-radius: 50%;

    overflow: hidden;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #1e3a8a;

    color: #60a5fa;

}

.patient-avatar img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}

.patient-name {

    color: white;

    font-weight: bold;

    font-size: 14px;

}

.patient-id {

    color: #64748b;

    font-size: 10px;

    margin-top: 4px;

}


/* ROLE */

.role-badge {

    display: inline-block;

    padding: 5px 11px;

    border-radius: 20px;

    background: #052e16;

    border: 1px solid #166534;

    color: #4ade80;

    font-size: 10px;

}


/* VIEW BUTTON */

.view-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 9px 15px;

    background: #1e3a8a;

    color: #60a5fa;

    border-radius: 8px;

    text-decoration: none;

    font-size: 12px;

    transition: 0.2s;

}

.view-btn:hover {

    background: #2563eb;

    color: white;

}


/* EMPTY */

.empty {

    text-align: center;

    padding: 60px 20px;

    color: #64748b;

}

.empty i {

    font-size: 40px;

    color: #334155;

    margin-bottom: 15px;

}

.empty h3 {

    color: #94a3b8;

    margin-bottom: 7px;

}


/* RESPONSIVE */

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

}

</style>

</head>


<body>


<div class="dashboard">


<!-- SIDEBAR -->
<div class="logo">

    <h2>
        Health<span>Sync</span>
    </h2>

    <p>
        Admin Portal
    </p>

</div>


<!-- =================================================
     ADMIN SIDEBAR
================================================== -->

<aside class="sidebar">

    <!-- LOGO -->
    <div class="logo">

        <h2>
            Health<span>Sync</span>
        </h2>

        <p>
            Admin Portal
        </p>

    </div>


    <!-- NAVIGATION -->
    <nav>

        <!-- DASHBOARD -->
        <a href="dashboard.php">

            <i class="fa-solid fa-house"></i>

            <span>
                Dashboard
            </span>

        </a>


        <!-- DOCTORS -->
        <a href="doctors.php">

            <i class="fa-solid fa-user-doctor"></i>

            <span>
                Doctors
            </span>

        </a>


        <!-- PATIENTS -->
        <a
            href="patients.php"
            class="active"
        >

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



<!-- MAIN -->

<main class="main">


    <div class="topbar">


        <div class="page-title">

            <h1>
                Manage Patients
            </h1>

            <p>
                View and manage registered HealthSync patients.
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
                        $_SESSION['fullname'] ?? 'Administrator'
                    );

                    ?>

                </strong>

                <small>
                    Administrator
                </small>

            </div>


        </div>


    </div>



    <!-- PATIENT COUNT -->

    <div class="stat-card">


        <div class="stat-icon">

            <i class="fa-solid fa-users"></i>

        </div>


        <div class="stat-number">

            <?php echo $patient_count; ?>

        </div>


        <div class="stat-label">

            Registered Patients

        </div>


    </div>



    <!-- TABLE -->

    <div class="table-card">


        <div class="table-header">

            <h2>
                Patient Accounts
            </h2>

            <p>
                All registered users with the patient role.
            </p>

        </div>


        <div class="table-wrapper">


        <?php if ($patient_count > 0): ?>


            <table class="patient-table">


                <thead>

                    <tr>

                        <th>PATIENT</th>

                        <th>EMAIL</th>

                        <th>ROLE</th>

                        <th>ACCOUNT ID</th>

                        <th>ACTION</th>

                    </tr>

                </thead>


                <tbody>


                <?php while (
                    $patient = mysqli_fetch_assoc($result)
                ): ?>


                    <tr>


                        <!-- PATIENT -->

                        <td>


                            <div class="patient-info">


                                <div class="patient-avatar">


                                    <?php

                                    if (
                                        !empty($patient['image'])
                                    ) {

                                        $imagePath =
                                            "../assets/uploads/" .
                                            $patient['image'];

                                        echo
                                        '<img src="' .
                                        htmlspecialchars(
                                            $imagePath
                                        ) .
                                        '" alt="Patient">';

                                    } else {

                                        echo
                                        '<i class="fa-solid fa-user"></i>';

                                    }

                                    ?>


                                </div>


                                <div>


                                    <div class="patient-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $patient['fullname']
                                        );

                                        ?>

                                    </div>


                                    <div class="patient-id">

                                        Patient #

                                        <?php

                                        echo intval(
                                            $patient['id']
                                        );

                                        ?>

                                    </div>


                                </div>


                            </div>


                        </td>



                        <!-- EMAIL -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $patient['email']
                            );

                            ?>

                        </td>



                        <!-- ROLE -->

                        <td>

                            <span class="role-badge">

                                <?php

                                echo htmlspecialchars(
                                    ucfirst(
                                        $patient['role']
                                    )
                                );

                                ?>

                            </span>

                        </td>



                        <!-- ID -->

                        <td>

                            #

                            <?php

                            echo intval(
                                $patient['id']
                            );

                            ?>

                        </td>



                        <!-- VIEW -->

                        <td>

                            <a
                            href="view-patient.php?id=<?php echo intval($patient['id']); ?>"
                            class="view-btn"
                            >

                                <i class="fa-solid fa-eye"></i>

                                View

                            </a>

                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="empty">

                <i class="fa-solid fa-user-slash"></i>

                <h3>
                    No Patients Found
                </h3>

                <p>
                    There are currently no registered patients.
                </p>

            </div>


        <?php endif; ?>


        </div>


    </div>


</main>


</div>


</body>

</html>