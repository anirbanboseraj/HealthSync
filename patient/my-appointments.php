<?php

session_start();

/* =====================================================
   CHECK LOGIN
===================================================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}


/* =====================================================
   DATABASE
===================================================== */

require_once "../config/database.php";


/* =====================================================
   PATIENT ID
===================================================== */

$patient_id = intval($_SESSION['user_id']);


/* =====================================================
   GET PATIENT APPOINTMENTS
===================================================== */

$sql = "
    SELECT
        appointments.id,
        appointments.patient_id,
        appointments.doctor_id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.problem,
        appointments.status,

        doctors.fullname AS doctor_name,
        doctors.specialization AS specialization

    FROM appointments

    INNER JOIN doctors
        ON appointments.doctor_id = doctors.id

    WHERE appointments.patient_id = ?

    ORDER BY
        appointments.appointment_date DESC,
        appointments.appointment_time DESC
";


$stmt = mysqli_prepare($conn, $sql);


/* =====================================================
   QUERY CHECK
===================================================== */

if (!$stmt) {

    die(
        "Appointment query preparation failed: "
        . htmlspecialchars(mysqli_error($conn))
    );

}


/* =====================================================
   BIND PATIENT ID
===================================================== */

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $patient_id
);


/* =====================================================
   EXECUTE
===================================================== */

if (!mysqli_stmt_execute($stmt)) {

    die(
        "Appointment query failed: "
        . htmlspecialchars(mysqli_stmt_error($stmt))
    );

}


/* =====================================================
   GET RESULT
===================================================== */

$result = mysqli_stmt_get_result($stmt);


/* =====================================================
   COUNT
===================================================== */

$appointment_count = mysqli_num_rows($result);


/* =====================================================
   PATIENT NAME
===================================================== */

$patient_name =
    $_SESSION['fullname'] ?? 'Patient';

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
        My Appointments - HealthSync
    </title>


    <!-- =================================================
         FONT AWESOME
    ================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- =================================================
         PATIENT DASHBOARD CSS
    ================================================== -->

    <link
        rel="stylesheet"
        href="../assets/css/dashboard.css"
    >


    <!-- =================================================
         APPOINTMENT PAGE CSS
    ================================================== -->

    <style>

        /* ================================================
           PAGE
        ================================================ */

        .appointments-page {

            width: 100%;

        }


        /* ================================================
           TOPBAR
        ================================================ */

        .appointments-page .topbar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 30px;

        }


        .page-title h1 {

            margin: 0;

            color: #f8fafc;

            font-size: 32px;

        }


        .page-title p {

            margin-top: 8px;

            color: #94a3b8;

            font-size: 15px;

        }


        .page-title strong {

            color: #60a5fa;

        }


        /* ================================================
           APPOINTMENT COUNT
        ================================================ */

        .appointment-summary {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 20px;

            color: #94a3b8;

            font-size: 15px;

        }


        .appointment-summary strong {

            color: #60a5fa;

            font-size: 18px;

        }


        /* ================================================
           TABLE CONTAINER
        ================================================ */

        .appointment-table-container {

            width: 100%;

            overflow-x: auto;

            background: #111a2d;

            border: 1px solid #263553;

            border-radius: 14px;

        }


        /* ================================================
           TABLE
        ================================================ */

        .appointment-table {

            width: 100%;

            min-width: 900px;

            border-collapse: collapse;

        }


        /* ================================================
           TABLE HEADER
        ================================================ */

        .appointment-table thead {

            background: #2563eb;

        }


        .appointment-table th {

            padding: 17px 18px;

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;

            text-align: left;

            white-space: nowrap;

        }


        /* ================================================
           TABLE BODY
        ================================================ */

        .appointment-table td {

            padding: 17px 18px;

            border-top: 1px solid #1e2a42;

            color: #dbe4f0;

            font-size: 14px;

            vertical-align: middle;

        }


        .appointment-table tbody tr {

            transition: 0.2s ease;

        }


        .appointment-table tbody tr:hover {

            background: rgba(
                37,
                99,
                235,
                0.05
            );

        }


        /* ================================================
           DOCTOR
        ================================================ */

        .doctor-name {

            color: #f8fafc;

            font-weight: 700;

            white-space: nowrap;

        }


        /* ================================================
           SPECIALIZATION
        ================================================ */

        .specialization {

            color: #60a5fa;

            font-weight: 600;

            white-space: nowrap;

        }


        /* ================================================
           PROBLEM
        ================================================ */

        .problem-text {

            max-width: 220px;

            color: #cbd5e1;

        }


        /* ================================================
           STATUS
        ================================================ */

        .appointment-status {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;

        }


        .appointment-status i {

            font-size: 8px;

        }


        /* APPROVED */

        .appointment-status.approved {

            color: #4ade80;

            background: rgba(
                34,
                197,
                94,
                0.12
            );

        }


        /* PENDING */

        .appointment-status.pending {

            color: #facc15;

            background: rgba(
                234,
                179,
                8,
                0.12
            );

        }


        /* REJECTED */

        .appointment-status.rejected {

            color: #f87171;

            background: rgba(
                239,
                68,
                68,
                0.12
            );

        }


        /* CANCELLED */

        .appointment-status.cancelled {

            color: #94a3b8;

            background: rgba(
                148,
                163,
                184,
                0.12
            );

        }


        /* ================================================
           EMPTY STATE
        ================================================ */

        .empty-appointments {

            padding: 70px 20px;

            text-align: center;

        }


        .empty-appointments i {

            font-size: 45px;

            color: #475569;

            margin-bottom: 18px;

        }


        .empty-appointments h3 {

            margin-bottom: 8px;

            color: #e2e8f0;

            font-size: 20px;

        }


        .empty-appointments p {

            color: #64748b;

            margin-bottom: 20px;

        }


        .book-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 11px 17px;

            background: #2563eb;

            color: #ffffff;

            text-decoration: none;

            border-radius: 8px;

            font-weight: 600;

            transition: 0.2s;

        }


        .book-btn:hover {

            background: #1d4ed8;

            transform: translateY(-1px);

        }


        /* ================================================
           RESPONSIVE
        ================================================ */

        @media (max-width: 700px) {

            .appointments-page .topbar {

                align-items: flex-start;

            }


            .page-title h1 {

                font-size: 26px;

            }

        }

    </style>

</head>


<body>


<div class="dashboard">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <?php include "../includes/patient-sidebar.php"; ?>


    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="main-content">


        <div class="appointments-page">


            <!-- =================================================
                 TOPBAR
            ================================================== -->

            <header class="topbar">


                <div class="page-title">

                    <h1>
                        My Appointments
                    </h1>

                    <p>

                        Welcome,

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $patient_name
                            );
                            ?>
                        </strong>

                    </p>

                </div>


            </header>



            <!-- =================================================
                 SUMMARY
            ================================================== -->

            <div class="appointment-summary">

                <span>
                    Total Appointments:
                </span>

                <strong>
                    <?php
                    echo $appointment_count;
                    ?>
                </strong>

            </div>



            <!-- =================================================
                 APPOINTMENT TABLE
            ================================================== -->

            <div class="appointment-table-container">


                <?php if ($appointment_count > 0): ?>


                    <table class="appointment-table">


                        <thead>

                            <tr>

                                <th>
                                    Doctor
                                </th>

                                <th>
                                    Specialization
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Time
                                </th>

                                <th>
                                    Problem
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $appointment =
                            mysqli_fetch_assoc($result)
                        ): ?>


                            <tr>


                                <!-- DOCTOR -->

                                <td>

                                    <div
                                        class="doctor-name"
                                    >

                                        Dr.

                                        <?php

                                        echo htmlspecialchars(
                                            $appointment[
                                                'doctor_name'
                                            ]
                                        );

                                        ?>

                                    </div>

                                </td>



                                <!-- SPECIALIZATION -->

                                <td>

                                    <span
                                        class="specialization"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $appointment[
                                                'specialization'
                                            ]
                                        );

                                        ?>

                                    </span>

                                </td>



                                <!-- DATE -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $appointment[
                                                'appointment_date'
                                            ]
                                        )
                                    ) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $appointment[
                                                    'appointment_date'
                                                ]
                                            )
                                        );

                                    } else {

                                        echo "N/A";

                                    }

                                    ?>

                                </td>



                                <!-- TIME -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $appointment[
                                                'appointment_time'
                                            ]
                                        )
                                    ) {

                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $appointment[
                                                    'appointment_time'
                                                ]
                                            )
                                        );

                                    } else {

                                        echo "N/A";

                                    }

                                    ?>

                                </td>



                                <!-- PROBLEM -->

                                <td>

                                    <div
                                        class="problem-text"
                                    >

                                        <?php

                                        echo !empty(
                                            $appointment[
                                                'problem'
                                            ]
                                        )
                                            ? htmlspecialchars(
                                                $appointment[
                                                    'problem'
                                                ]
                                            )
                                            : "Not provided";

                                        ?>

                                    </div>

                                </td>



                                <!-- STATUS -->

                                <td>


                                    <?php

                                    $status = strtolower(
                                        trim(
                                            $appointment[
                                                'status'
                                            ] ?? 'pending'
                                        )
                                    );

                                    ?>


                                    <?php if (
                                        $status === 'approved'
                                    ): ?>


                                        <span
                                            class="
                                            appointment-status
                                            approved
                                            "
                                        >

                                            <i
                                                class="
                                                fa-solid
                                                fa-circle
                                                "
                                            ></i>

                                            Approved

                                        </span>


                                    <?php elseif (
                                        $status === 'rejected'
                                    ): ?>


                                        <span
                                            class="
                                            appointment-status
                                            rejected
                                            "
                                        >

                                            <i
                                                class="
                                                fa-solid
                                                fa-circle
                                                "
                                            ></i>

                                            Rejected

                                        </span>


                                    <?php elseif (
                                        $status === 'cancelled'
                                        ||
                                        $status === 'canceled'
                                    ): ?>


                                        <span
                                            class="
                                            appointment-status
                                            cancelled
                                            "
                                        >

                                            <i
                                                class="
                                                fa-solid
                                                fa-circle
                                                "
                                            ></i>

                                            Cancelled

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="
                                            appointment-status
                                            pending
                                            "
                                        >

                                            <i
                                                class="
                                                fa-solid
                                                fa-circle
                                                "
                                            ></i>

                                            Pending

                                        </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                <?php else: ?>


                    <!-- =================================================
                         EMPTY
                    ================================================== -->

                    <div class="empty-appointments">


                        <i
                            class="
                            fa-solid
                            fa-calendar-xmark
                            "
                        ></i>


                        <h3>
                            No Appointments Found
                        </h3>


                        <p>
                            You don't have any appointments yet.
                        </p>


                        <a
                            href="book-appointment.php"
                            class="book-btn"
                        >

                            <i
                                class="
                                fa-solid
                                fa-calendar-plus
                                "
                            ></i>

                            Book an Appointment

                        </a>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </main>


</div>


</body>

</html>


<?php

/* =====================================================
   CLOSE STATEMENT
===================================================== */

mysqli_stmt_close($stmt);

?>