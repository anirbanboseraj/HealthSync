<?php

session_start();

include("../config/database.php");


// ------------------------------------
// CHECK DOCTOR LOGIN
// ------------------------------------

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor')
{
    header("Location: ../login.php");
    exit();
}


// ------------------------------------
// GET LOGGED-IN USER
// ------------------------------------

$user_id = $_SESSION['user_id'];


// ------------------------------------
// FIND DOCTOR USING user_id
// ------------------------------------

$doctorQuery = mysqli_query($conn,"
    SELECT *
    FROM doctors
    WHERE user_id='$user_id'
");


if(mysqli_num_rows($doctorQuery) == 0)
{
    die("Doctor profile not found.");
}


$doctor = mysqli_fetch_assoc($doctorQuery);

$doctor_id = $doctor['id'];


// ------------------------------------
// GET THIS DOCTOR'S APPOINTMENTS
// ------------------------------------

$query = mysqli_query($conn,"

    SELECT
        appointments.*,
        users.fullname AS patient_name,
        users.email AS patient_email

    FROM appointments

    INNER JOIN users
        ON appointments.patient_id = users.id

    WHERE appointments.doctor_id = '$doctor_id'

    ORDER BY
        appointments.appointment_date DESC,
        appointments.appointment_time DESC

");


?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Doctor Appointments - HealthSync</title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <!-- Doctor CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/doctor.css"
    >

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #0f172a;
    color: #e2e8f0;
}


/* =========================
   SIDEBAR
========================= */

.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;

    background: #111827;

    border-right: 1px solid #1e3a5f;

    box-shadow: 4px 0 20px rgba(0,0,0,0.25);
}


.logo {
    padding: 28px 20px;

    text-align: center;

    border-bottom: 1px solid #1e3a5f;
}


.logo h2 {
    margin: 0;

    color: #ffffff;

    font-size: 26px;
}


.logo span {
    color: #3b82f6;
}


/* =========================
   SIDEBAR MENU
========================= */

.sidebar ul {
    list-style: none;

    padding: 0;

    margin: 25px 12px;
}


.sidebar li {
    margin: 8px 0;
}


.sidebar a {
    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px 18px;

    text-decoration: none;

    color: #cbd5e1;

    border-radius: 8px;

    transition: 0.3s;
}


.sidebar a:hover {
    background: #1e3a5f;

    color: #ffffff;
}


.sidebar li.active a {
    background: #2563eb;

    color: #ffffff;

    box-shadow: 0 4px 12px rgba(37,99,235,0.35);
}


.sidebar i {
    width: 22px;

    text-align: center;
}


/* =========================
   MAIN CONTENT
========================= */

.main-content {
    margin-left: 250px;

    padding: 40px;
}


.main-content h1 {
    margin: 0 0 8px;

    color: #ffffff;

    font-size: 32px;
}


.welcome {
    color: #94a3b8;

    margin-bottom: 30px;

    font-size: 15px;
}


/* =========================
   APPOINTMENT CONTAINER
========================= */

.appointment-container {
    background: #1e293b;

    border: 1px solid #334155;

    border-radius: 12px;

    padding: 25px;

    box-shadow: 0 8px 25px rgba(0,0,0,0.25);

    overflow-x: auto;
}


/* =========================
   TABLE
========================= */

.appointment-table {
    width: 100%;

    border-collapse: collapse;
}


.appointment-table th {
    background: #2563eb;

    color: #ffffff;

    padding: 15px;

    text-align: left;

    font-size: 14px;
}


.appointment-table td {
    padding: 15px;

    border-bottom: 1px solid #334155;

    color: #cbd5e1;

    font-size: 14px;
}


.appointment-table tbody tr {
    transition: 0.2s;
}


.appointment-table tbody tr:hover {
    background: #263449;
}


/* =========================
   STATUS
========================= */

.status {
    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}


.status.pending {
    background: #854d0e;

    color: #fef08a;
}


.status.approved {
    background: #065f46;

    color: #a7f3d0;
}


.status.rejected {
    background: #991b1b;

    color: #fecaca;
}


/* =========================
   ACTION BUTTONS
========================= */

.action-buttons {
    display: flex;

    gap: 8px;

    align-items: center;
}


.approve-btn,
.reject-btn {
    display: inline-block;

    padding: 8px 12px;

    border-radius: 6px;

    text-decoration: none;

    color: #ffffff;

    font-size: 13px;

    transition: 0.3s;
}


.approve-btn {
    background: #16a34a;
}


.approve-btn:hover {
    background: #15803d;

    transform: translateY(-1px);
}


.reject-btn {
    background: #dc2626;
}


.reject-btn:hover {
    background: #b91c1c;

    transform: translateY(-1px);
}


/* =========================
   NO APPOINTMENTS
========================= */

.no-appointments {
    text-align: center;

    padding: 50px;

    color: #64748b;

    font-size: 15px;
}


.no-appointments i {
    font-size: 35px;

    color: #3b82f6;
}


/* =========================
   MOBILE
========================= */

@media(max-width: 900px) {

    .sidebar {
        width: 210px;
    }

    .main-content {
        margin-left: 210px;

        padding: 25px;
    }

}


@media(max-width: 700px) {

    .sidebar {
        width: 70px;
    }

    .logo h2 {
        font-size: 0;
    }

    .logo h2 span {
        font-size: 20px;
    }

    .sidebar a {
        justify-content: center;

        padding: 14px 5px;
    }

    .sidebar a {
        font-size: 0;
    }

    .sidebar i {
        font-size: 18px;
    }

    .main-content {
        margin-left: 70px;

        padding: 20px;
    }

}

</style>

</head>


<body>


<!-- ================================= -->
<!-- SIDEBAR -->
<!-- ================================= -->

<aside class="sidebar">


    <div class="logo">

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


        <li class="active">

            <a href="appointments.php">

                <i class="fa-solid fa-calendar-check"></i>

                Appointments

            </a>

        </li>


        <li>

            <a href="prescription.php">

                <i class="fa-solid fa-file-prescription"></i>

                Prescriptions

            </a>

        </li>


        <li>

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



<!-- ================================= -->
<!-- MAIN CONTENT -->
<!-- ================================= -->

<main class="main-content">


    <h1>Patient Appointments</h1>


    <p class="welcome">

        Welcome, Dr. <?php echo htmlspecialchars($doctor['fullname']); ?>.
        Manage your patient appointments from here.

    </p>


    <div class="appointment-container">


        <table class="appointment-table">


            <thead>

                <tr>

                    <th>Patient</th>

                    <th>Email</th>

                    <th>Date</th>

                    <th>Time</th>

                    <th>Problem</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>


            <?php

            if(mysqli_num_rows($query) > 0)
            {

                while($row = mysqli_fetch_assoc($query))
                {

            ?>


                <tr>


                    <td>

                        <?php
                        echo htmlspecialchars($row['patient_name']);
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars($row['patient_email']);
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars($row['appointment_date']);
                        ?>

                    </td>


                    <td>

                        <?php

                        echo date(
                            "h:i A",
                            strtotime($row['appointment_time'])
                        );

                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars($row['problem']);
                        ?>

                    </td>


                    <td>


                        <?php

                        $status = strtolower($row['status']);

                        ?>


                        <span class="status <?php echo $status; ?>">

                            <?php
                            echo ucfirst($row['status']);
                            ?>

                        </span>


                    </td>


                    <td>


                        <?php

                        if($status == "pending")
                        {

                        ?>


                        <div class="action-buttons">


                            <a
                                href="approve.php?id=<?php echo $row['id']; ?>"
                                class="approve-btn"
                            >

                                <i class="fa-solid fa-check"></i>

                                Approve

                            </a>


                            <a
                                href="reject.php?id=<?php echo $row['id']; ?>"
                                class="reject-btn"
                            >

                                <i class="fa-solid fa-xmark"></i>

                                Reject

                            </a>


                        </div>


                        <?php

                        }
                        else
                        {

                            echo "—";

                        }

                        ?>


                    </td>


                </tr>


            <?php

                }

            }
            else
            {

            ?>


                <tr>

                    <td
                        colspan="7"
                        class="no-appointments"
                    >

                        <i class="fa-solid fa-calendar-xmark"></i>

                        <br><br>

                        No appointments found.

                    </td>

                </tr>


            <?php

            }

            ?>


            </tbody>


        </table>


    </div>


</main>


</body>

</html>