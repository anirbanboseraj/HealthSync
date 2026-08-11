<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include("../config/database.php");


// =====================================================
// CHECK DOCTOR LOGIN
// =====================================================

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] != 'doctor'
) {
    header("Location: ../login.php");
    exit();
}


$user_id = $_SESSION['user_id'];


// =====================================================
// GET LOGGED-IN DOCTOR
// =====================================================

$doctorQuery = mysqli_query(
    $conn,
    "
    SELECT *
    FROM doctors
    WHERE user_id='$user_id'
    LIMIT 1
    "
);


if (!$doctorQuery) {
    die("Doctor query error: " . mysqli_error($conn));
}


if (mysqli_num_rows($doctorQuery) == 0) {
    die("Doctor profile not found.");
}


$doctor = mysqli_fetch_assoc($doctorQuery);

$doctor_id = $doctor['id'];


// =====================================================
// MESSAGE
// =====================================================

$message = "";


// =====================================================
// SAVE PRESCRIPTION
// =====================================================

if (isset($_POST['save_prescription'])) {

    $appointment_id = intval($_POST['appointment_id']);

    $patient_id = intval($_POST['patient_id']);

    $diagnosis = mysqli_real_escape_string(
        $conn,
        $_POST['diagnosis']
    );

    $medicines = mysqli_real_escape_string(
        $conn,
        $_POST['medicines']
    );

    $advice = mysqli_real_escape_string(
        $conn,
        $_POST['advice']
    );


    // =================================================
    // VERIFY APPOINTMENT
    // =================================================

    $checkAppointment = mysqli_query(
        $conn,
        "
        SELECT id
        FROM appointments
        WHERE id='$appointment_id'
        AND doctor_id='$doctor_id'
        AND patient_id='$patient_id'
        AND status='approved'
        LIMIT 1
        "
    );


    if (!$checkAppointment) {

        die(
            "Appointment query error: "
            . mysqli_error($conn)
        );

    }


    if (mysqli_num_rows($checkAppointment) == 0) {

        $message = "
        <div class='error-message'>
            Invalid or unapproved appointment selected.
        </div>
        ";

    }

    else {

        // =============================================
        // INSERT PRESCRIPTION
        // =============================================

        $insert = mysqli_query(
            $conn,
            "
            INSERT INTO prescriptions
            (
                appointment_id,
                doctor_id,
                patient_id,
                diagnosis,
                medicines,
                advice
            )

            VALUES
            (
                '$appointment_id',
                '$doctor_id',
                '$patient_id',
                '$diagnosis',
                '$medicines',
                '$advice'
            )
            "
        );


        if ($insert) {

            $message = "
            <div class='success-message'>

                <i class='fa-solid fa-circle-check'></i>

                Prescription saved successfully!

            </div>
            ";

        }

        else {

            $message = "
            <div class='error-message'>

                Something went wrong while saving
                the prescription.

                <br><br>

                " . htmlspecialchars(
                    mysqli_error($conn)
                ) . "

            </div>
            ";

        }

    }

}


// =====================================================
// GET APPROVED APPOINTMENTS
// =====================================================

$appointmentsQuery = mysqli_query(
    $conn,
    "
    SELECT

        appointments.id,

        appointments.patient_id,

        appointments.appointment_date,

        appointments.appointment_time,

        users.fullname

    FROM appointments

    INNER JOIN users

        ON appointments.patient_id = users.id

    WHERE appointments.doctor_id='$doctor_id'

    AND appointments.status='approved'

    ORDER BY

        appointments.appointment_date DESC,

        appointments.appointment_time DESC
    "
);


if (!$appointmentsQuery) {

    die(
        "Appointments query error: "
        . mysqli_error($conn)
    );

}


// =====================================================
// GET DOCTOR'S PRESCRIPTIONS
// =====================================================

$prescriptionsQuery = mysqli_query(
    $conn,
    "
    SELECT

        prescriptions.id,

        prescriptions.appointment_id,

        prescriptions.patient_id,

        prescriptions.diagnosis,

        prescriptions.medicines,

        prescriptions.advice,

        prescriptions.created_at,

        users.fullname AS patient_name

    FROM prescriptions

    INNER JOIN users

        ON prescriptions.patient_id = users.id

    WHERE prescriptions.doctor_id='$doctor_id'

    ORDER BY prescriptions.created_at DESC
    "
);


if (!$prescriptionsQuery) {

    die(
        "Prescriptions query error: "
        . mysqli_error($conn)
    );

}

?>


<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Prescriptions | HealthSync
    </title>


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


body {

    font-family: Arial, sans-serif;

    background: #020617;

    color: #e2e8f0;

}


/* =====================================================
   LAYOUT
===================================================== */

.doctor-dashboard {

    display: flex;

    min-height: 100vh;

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


.logo {

    text-align: center;

    margin-bottom: 35px;

}


.logo h2 {

    color: white;

    font-size: 25px;

}


.logo h2 span {

    color: #3b82f6;

}


.logo p {

    color: #64748b;

    font-size: 12px;

    margin-top: 5px;

}


.sidebar ul {

    list-style: none;

}


.sidebar li {

    margin-bottom: 7px;

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    color: #94a3b8;

    text-decoration: none;

    border-radius: 8px;

    transition: .2s;

}


.sidebar a:hover {

    background: #1e3a8a;

    color: white;

}


.sidebar li.active a {

    background: #1d4ed8;

    color: white;

}


.sidebar a i {

    width: 20px;

}


.logout {

    margin-top: 35px;

}


/* =====================================================
   MAIN
===================================================== */

.main-content {

    flex: 1;

    padding: 35px 40px;

}


.page-header {

    margin-bottom: 25px;

}


.page-header h1 {

    color: white;

    font-size: 28px;

}


.page-header p {

    color: #64748b;

    margin-top: 6px;

}


/* =====================================================
   FORM CARD
===================================================== */

.form-card {

    max-width: 900px;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    padding: 30px;

}


.form-title {

    display: flex;

    align-items: center;

    gap: 12px;

    padding-bottom: 20px;

    margin-bottom: 25px;

    border-bottom: 1px solid #1e293b;

}


.form-title i {

    width: 45px;

    height: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background: #172554;

    color: #60a5fa;

    font-size: 20px;

}


.form-title h2 {

    color: white;

    font-size: 19px;

}


.form-title p {

    color: #64748b;

    font-size: 12px;

    margin-top: 4px;

}


/* =====================================================
   FORM
===================================================== */

.form-group {

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    color: #cbd5e1;

    font-size: 13px;

    font-weight: bold;

    margin-bottom: 8px;

}


.form-group label span {

    color: #60a5fa;

}


.form-group input,
.form-group select,
.form-group textarea {

    width: 100%;

    padding: 12px 14px;

    background: #020617;

    border: 1px solid #334155;

    border-radius: 8px;

    color: white;

    outline: none;

    font-family: Arial, sans-serif;

    font-size: 13px;

}


.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {

    border-color: #3b82f6;

    box-shadow:
        0 0 0 2px
        rgba(59,130,246,.15);

}


.form-group textarea {

    min-height: 130px;

    resize: vertical;

}


.form-group select option {

    background: #0f172a;

    color: white;

}


/* =====================================================
   BUTTONS
===================================================== */

.form-actions {

    display: flex;

    gap: 12px;

    margin-top: 10px;

}


.save-btn {

    border: none;

    background: #2563eb;

    color: white;

    padding: 13px 20px;

    border-radius: 8px;

    cursor: pointer;

    font-size: 13px;

    font-weight: bold;

}


.save-btn:hover {

    background: #1d4ed8;

}


.cancel-btn {

    display: inline-flex;

    align-items: center;

    padding: 13px 20px;

    border-radius: 8px;

    background: #1e293b;

    color: #cbd5e1;

    text-decoration: none;

    font-size: 13px;

    font-weight: bold;

}


.cancel-btn:hover {

    background: #334155;

}


/* =====================================================
   MESSAGES
===================================================== */

.success-message {

    background: #052e16;

    border: 1px solid #166534;

    color: #86efac;

    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 13px;

}


.success-message i {

    margin-right: 7px;

}


.error-message {

    background: #450a0a;

    border: 1px solid #991b1b;

    color: #fca5a5;

    padding: 13px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 13px;

}


/* =====================================================
   INFO BOX
===================================================== */

.info-box {

    margin-top: 20px;

    padding: 15px;

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 8px;

    color: #64748b;

    font-size: 12px;

    line-height: 1.6;

}


.info-box i {

    color: #60a5fa;

    margin-right: 6px;

}


/* =====================================================
   PRESCRIPTIONS SECTION
===================================================== */

.prescriptions-section {

    margin-top: 40px;

    max-width: 1100px;

}


.section-header {

    margin-bottom: 20px;

}


.section-header h2 {

    color: white;

    font-size: 22px;

}


.section-header p {

    color: #64748b;

    margin-top: 5px;

    font-size: 13px;

}


/* =====================================================
   PRESCRIPTION CARD
===================================================== */

.prescription-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 12px;

    margin-bottom: 18px;

    overflow: hidden;

}


.prescription-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 20px;

    background: #111827;

    border-bottom: 1px solid #1e293b;

}


.patient-info {

    display: flex;

    align-items: center;

    gap: 13px;

}


.patient-icon {

    width: 45px;

    height: 45px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

}


.patient-info h3 {

    color: white;

    font-size: 16px;

}


.patient-info p {

    color: #64748b;

    font-size: 12px;

    margin-top: 4px;

}


.prescription-date {

    color: #94a3b8;

    font-size: 12px;

}


.prescription-body {

    padding: 20px;

}


.detail-row {

    display: grid;

    grid-template-columns: 130px 1fr;

    gap: 15px;

    padding: 12px 0;

    border-bottom: 1px solid #1e293b;

}


.detail-row:last-child {

    border-bottom: none;

}


.detail-label {

    color: #60a5fa;

    font-size: 13px;

    font-weight: bold;

}


.detail-value {

    color: #cbd5e1;

    font-size: 13px;

    line-height: 1.6;

    white-space: pre-line;

}


/* =====================================================
   ACTION BUTTONS
===================================================== */

.prescription-actions {

    display: flex;

    gap: 8px;

    padding: 15px 20px;

    background: #0b1220;

    border-top: 1px solid #1e293b;

}


.action-btn {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 9px 14px;

    border-radius: 7px;

    text-decoration: none;

    font-size: 12px;

    font-weight: bold;

}


.view-btn {

    background: #172554;

    color: #60a5fa;

}


.view-btn:hover {

    background: #1e3a8a;

    color: white;

}


.edit-btn {

    background: #422006;

    color: #fbbf24;

}


.edit-btn:hover {

    background: #713f12;

    color: white;

}


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

.empty-prescriptions {

    padding: 45px;

    text-align: center;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 12px;

}


.empty-prescriptions i {

    font-size: 35px;

    color: #3b82f6;

    margin-bottom: 15px;

}


.empty-prescriptions h3 {

    color: white;

    margin-bottom: 7px;

}


.empty-prescriptions p {

    color: #64748b;

    font-size: 13px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:700px) {

    .doctor-dashboard {

        flex-direction: column;

    }


    .sidebar {

        width: 100%;

        min-height: auto;

    }


    .sidebar ul {

        display: flex;

        flex-wrap: wrap;

    }


    .logout {

        margin-top: 0;

    }


    .main-content {

        padding: 20px;

    }


    .form-card {

        padding: 20px;

    }


    .prescription-top {

        flex-direction: column;

        align-items: flex-start;

        gap: 12px;

    }


    .detail-row {

        grid-template-columns: 1fr;

        gap: 5px;

    }


    .prescription-actions {

        flex-wrap: wrap;

    }

}

</style>

</head>


<body>


<div class="doctor-dashboard">


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">


    <div class="logo">

        <h2>
            Health<span>Sync</span>
        </h2>

        <p>
            Doctor Portal
        </p>

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


        <li class="active">

            <a href="prescription.php">

                <i class="fa-solid fa-file-prescription"></i>

                Prescriptions

            </a>

        </li>


        <li>

            <a href="profile.php">

                <i class="fa-solid fa-user-doctor"></i>

                My Profile

            </a>

        </li>


        <li class="logout">

            <a href="logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </a>

        </li>


    </ul>


</aside>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="main-content">


    <!-- =================================================
         PAGE HEADER
    ================================================== -->

    <div class="page-header">

        <h1>
            Prescriptions
        </h1>

        <p>
            Create and manage patient prescriptions.
        </p>

    </div>



    <!-- =================================================
         CREATE PRESCRIPTION
    ================================================== -->

    <div class="form-card">


        <div class="form-title">

            <i class="fa-solid fa-file-prescription"></i>


            <div>

                <h2>
                    Create Prescription
                </h2>

                <p>
                    Create a new prescription for an approved patient.
                </p>

            </div>

        </div>



        <?php echo $message; ?>



        <form
            method="POST"
            action=""
        >


            <!-- APPOINTMENT -->

            <div class="form-group">

                <label>

                    Select Appointment

                    <span>*</span>

                </label>


                <select
                    name="appointment_id"
                    id="appointment"
                    required
                >


                    <option value="">

                        -- Select Patient Appointment --

                    </option>


                    <?php

                    while (
                        $appointment =
                        mysqli_fetch_assoc(
                            $appointmentsQuery
                        )
                    ) {

                    ?>


                    <option
                        value="<?php
                            echo $appointment['id'];
                        ?>"
                        data-patient="<?php
                            echo $appointment['patient_id'];
                        ?>"
                    >

                        <?php

                        echo htmlspecialchars(
                            $appointment['fullname']
                        );

                        ?>

                        —

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $appointment['appointment_date']
                            )
                        );

                        ?>

                        at

                        <?php

                        echo date(
                            "h:i A",
                            strtotime(
                                $appointment['appointment_time']
                            )
                        );

                        ?>

                    </option>


                    <?php

                    }

                    ?>


                </select>

            </div>



            <!-- HIDDEN PATIENT ID -->

            <input
                type="hidden"
                name="patient_id"
                id="patient_id"
                value=""
            >



            <!-- DIAGNOSIS -->

            <div class="form-group">

                <label>

                    Diagnosis

                    <span>*</span>

                </label>


                <input
                    type="text"
                    name="diagnosis"
                    placeholder="Example: Seasonal fever"
                    required
                >

            </div>



            <!-- MEDICINES -->

            <div class="form-group">

                <label>

                    Medicines

                    <span>*</span>

                </label>


                <textarea
                    name="medicines"
                    placeholder="Example:

1. Paracetamol 500mg — 1 tablet after meal, 3 times daily
2. Cetirizine 10mg — 1 tablet at night

"
                    required
                ></textarea>

            </div>



            <!-- ADVICE -->

            <div class="form-group">

                <label>
                    Advice / Instructions
                </label>


                <textarea
                    name="advice"
                    placeholder="Example:

Take sufficient rest.
Drink plenty of water.
Avoid oily food.
Follow up after 7 days.

"
                ></textarea>

            </div>



            <!-- BUTTONS -->

            <div class="form-actions">


                <button
                    type="submit"
                    name="save_prescription"
                    class="save-btn"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Prescription

                </button>


                <a
                    href="prescription.php"
                    class="cancel-btn"
                >

                    Cancel

                </a>


            </div>


        </form>



        <div class="info-box">

            <i class="fa-solid fa-circle-info"></i>

            Only approved appointments for your patients
            are shown here. The prescription is linked to
            the selected appointment and patient.

        </div>


    </div>



    <!-- =================================================
         MY PRESCRIPTIONS
    ================================================== -->

    <section class="prescriptions-section">


        <div class="section-header">

            <h2>
                My Prescriptions
            </h2>

            <p>
                Prescriptions you have created for your patients.
            </p>

        </div>



        <?php

        if (
            mysqli_num_rows(
                $prescriptionsQuery
            ) > 0
        ) {


            while (
                $prescription =
                mysqli_fetch_assoc(
                    $prescriptionsQuery
                )
            ) {

        ?>


        <!-- PRESCRIPTION CARD -->

        <div class="prescription-card">


            <!-- TOP -->

            <div class="prescription-top">


                <div class="patient-info">


                    <div class="patient-icon">

                        <i class="fa-solid fa-user"></i>

                    </div>


                    <div>

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $prescription['patient_name']
                            );

                            ?>

                        </h3>


                        <p>

                            Prescription #

                            <?php

                            echo $prescription['id'];

                            ?>

                        </p>

                    </div>


                </div>



                <div class="prescription-date">

                    <i class="fa-solid fa-calendar"></i>

                    <?php

                    echo date(
                        "d M Y, h:i A",
                        strtotime(
                            $prescription['created_at']
                        )
                    );

                    ?>

                </div>


            </div>



            <!-- BODY -->

            <div class="prescription-body">


                <!-- DIAGNOSIS -->

                <div class="detail-row">


                    <div class="detail-label">

                        <i class="fa-solid fa-stethoscope"></i>

                        Diagnosis

                    </div>


                    <div class="detail-value">

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $prescription['diagnosis']
                            )
                        );

                        ?>

                    </div>


                </div>



                <!-- MEDICINES -->

                <div class="detail-row">


                    <div class="detail-label">

                        <i class="fa-solid fa-pills"></i>

                        Medicines

                    </div>


                    <div class="detail-value">

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $prescription['medicines']
                            )
                        );

                        ?>

                    </div>


                </div>



                <!-- ADVICE -->

                <div class="detail-row">


                    <div class="detail-label">

                        <i class="fa-solid fa-notes-medical"></i>

                        Advice

                    </div>


                    <div class="detail-value">

                        <?php

                        if (
                            !empty(
                                $prescription['advice']
                            )
                        ) {

                            echo nl2br(
                                htmlspecialchars(
                                    $prescription['advice']
                                )
                            );

                        }

                        else {

                            echo "No additional advice.";

                        }

                        ?>

                    </div>


                </div>


            </div>



            <!-- ACTIONS -->

            <div class="prescription-actions">


                <a
                    href="view-prescription.php?id=<?php
                        echo $prescription['id'];
                    ?>"
                    class="action-btn view-btn"
                >

                    <i class="fa-solid fa-eye"></i>

                    View

                </a>


                <a
                    href="edit-prescription.php?id=<?php
                        echo $prescription['id'];
                    ?>"
                    class="action-btn edit-btn"
                >

                    <i class="fa-solid fa-pen"></i>

                    Edit

                </a>


                <a
                    href="delete-prescription.php?id=<?php
                        echo $prescription['id'];
                    ?>"
                    class="action-btn delete-btn"
                    onclick="
                        return confirm(
                            'Are you sure you want to delete this prescription?'
                        );
                    "
                >

                    <i class="fa-solid fa-trash"></i>

                    Delete

                </a>


            </div>


        </div>


        <?php

            }

        }

        else {

        ?>


        <!-- EMPTY -->

        <div class="empty-prescriptions">


            <i class="fa-solid fa-file-prescription"></i>


            <h3>
                No Prescriptions Yet
            </h3>


            <p>
                You have not created any prescriptions yet.
            </p>


        </div>


        <?php

        }

        ?>


    </section>


</main>


</div>



<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>


const appointment =
    document.getElementById(
        "appointment"
    );


const patientId =
    document.getElementById(
        "patient_id"
    );


appointment.addEventListener(
    "change",
    function () {


        const selected =
            this.options[
                this.selectedIndex
            ];


        if (
            selected.value !== ""
        ) {


            patientId.value =
                selected.dataset.patient;


        }

        else {


            patientId.value = "";


        }

    }
);


</script>


</body>

</html>