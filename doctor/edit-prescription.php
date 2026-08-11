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
// CHECK PRESCRIPTION ID
// =====================================================

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    die("Invalid prescription ID.");
}

$prescription_id = intval($_GET['id']);


// =====================================================
// GET PRESCRIPTION
// IMPORTANT: ONLY THIS DOCTOR'S PRESCRIPTION
// =====================================================

$prescriptionQuery = mysqli_query(
    $conn,
    "
    SELECT
        prescriptions.*,
        users.fullname AS patient_name

    FROM prescriptions

    INNER JOIN users
        ON prescriptions.patient_id = users.id

    WHERE prescriptions.id='$prescription_id'

    AND prescriptions.doctor_id='$doctor_id'

    LIMIT 1
    "
);


if (!$prescriptionQuery) {
    die(
        "Prescription query error: "
        . mysqli_error($conn)
    );
}


if (mysqli_num_rows($prescriptionQuery) == 0) {
    die("Prescription not found.");
}


$prescription = mysqli_fetch_assoc(
    $prescriptionQuery
);


// =====================================================
// MESSAGE
// =====================================================

$message = "";


// =====================================================
// UPDATE PRESCRIPTION
// =====================================================

if (isset($_POST['update_prescription'])) {

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


    $update = mysqli_query(
        $conn,
        "
        UPDATE prescriptions

        SET
            diagnosis='$diagnosis',
            medicines='$medicines',
            advice='$advice'

        WHERE id='$prescription_id'

        AND doctor_id='$doctor_id'
        "
    );


    if ($update) {

        header(
            "Location: view-prescription.php?id="
            . $prescription_id
        );

        exit();

    }

    else {

        $message = "
        <div class='error-message'>

            <i class='fa-solid fa-circle-exclamation'></i>

            Failed to update prescription.

            <br><br>

            " .
            htmlspecialchars(
                mysqli_error($conn)
            )
            . "

        </div>
        ";

    }

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
        Edit Prescription | HealthSync
    </title>


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

    min-height: 100vh;

}


/* =====================================================
   PAGE
===================================================== */

.page {

    max-width: 900px;

    margin: 40px auto;

    padding: 0 20px;

}


/* =====================================================
   TOP BAR
===================================================== */

.top-bar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.back-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 10px 16px;

    background: #1e293b;

    color: #cbd5e1;

    text-decoration: none;

    border-radius: 8px;

    font-size: 13px;

}


.back-btn:hover {

    background: #334155;

    color: white;

}


/* =====================================================
   CARD
===================================================== */

.card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    overflow: hidden;

}


/* =====================================================
   HEADER
===================================================== */

.card-header {

    padding: 28px 30px;

    background: #111827;

    border-bottom: 1px solid #1e293b;

}


.card-header-content {

    display: flex;

    align-items: center;

    gap: 15px;

}


.header-icon {

    width: 48px;

    height: 48px;

    border-radius: 10px;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;

}


.card-header h1 {

    color: white;

    font-size: 22px;

}


.card-header p {

    color: #64748b;

    font-size: 12px;

    margin-top: 5px;

}


/* =====================================================
   PATIENT BAR
===================================================== */

.patient-bar {

    padding: 18px 30px;

    background: #0b1220;

    border-bottom: 1px solid #1e293b;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.patient-info {

    display: flex;

    align-items: center;

    gap: 10px;

}


.patient-icon {

    width: 38px;

    height: 38px;

    border-radius: 50%;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

}


.patient-info strong {

    display: block;

    color: white;

    font-size: 14px;

}


.patient-info span {

    color: #64748b;

    font-size: 11px;

}


/* =====================================================
   FORM
===================================================== */

.form {

    padding: 30px;

}


.form-group {

    margin-bottom: 22px;

}


.form-group label {

    display: block;

    color: #cbd5e1;

    font-size: 13px;

    font-weight: bold;

    margin-bottom: 8px;

}


.form-group label i {

    color: #60a5fa;

    margin-right: 5px;

}


.form-group input,
.form-group textarea {

    width: 100%;

    padding: 13px 14px;

    border-radius: 8px;

    border: 1px solid #334155;

    background: #020617;

    color: white;

    outline: none;

    font-family: Arial, sans-serif;

    font-size: 13px;

}


.form-group input:focus,
.form-group textarea:focus {

    border-color: #3b82f6;

    box-shadow:
        0 0 0 2px
        rgba(59,130,246,.15);

}


.form-group textarea {

    min-height: 150px;

    resize: vertical;

}


/* =====================================================
   BUTTONS
===================================================== */

.form-actions {

    display: flex;

    gap: 10px;

    padding-top: 5px;

}


.update-btn {

    border: none;

    cursor: pointer;

    padding: 13px 20px;

    border-radius: 8px;

    background: #2563eb;

    color: white;

    font-size: 13px;

    font-weight: bold;

}


.update-btn:hover {

    background: #1d4ed8;

}


.cancel-btn {

    display: inline-flex;

    align-items: center;

    gap: 7px;

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

    color: white;

}


/* =====================================================
   ERROR
===================================================== */

.error-message {

    margin: 20px 30px 0;

    padding: 13px 15px;

    border-radius: 8px;

    background: #450a0a;

    border: 1px solid #991b1b;

    color: #fca5a5;

    font-size: 13px;

}


/* =====================================================
   NOTE
===================================================== */

.note {

    margin-top: 20px;

    padding: 14px;

    border-radius: 8px;

    background: #111827;

    border: 1px solid #1e293b;

    color: #64748b;

    font-size: 11px;

    line-height: 1.6;

}


.note i {

    color: #60a5fa;

    margin-right: 5px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:600px) {

    .page {

        margin: 20px auto;

    }


    .top-bar {

        align-items: stretch;

    }


    .patient-bar {

        flex-direction: column;

        align-items: flex-start;

        gap: 10px;

    }


    .form {

        padding: 22px;

    }


    .card-header {

        padding: 22px;

    }


    .patient-bar {

        padding: 16px 22px;

    }


    .form-actions {

        flex-direction: column;

    }


    .update-btn,
    .cancel-btn {

        justify-content: center;

    }

}

</style>

</head>


<body>


<div class="page">


    <!-- =================================================
         TOP BAR
    ================================================== -->

    <div class="top-bar">


        <a
            href="prescription.php"
            class="back-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Prescriptions

        </a>


    </div>



    <!-- =================================================
         CARD
    ================================================== -->

    <div class="card">


        <!-- HEADER -->

        <div class="card-header">


            <div class="card-header-content">


                <div class="header-icon">

                    <i class="fa-solid fa-pen-to-square"></i>

                </div>


                <div>

                    <h1>
                        Edit Prescription
                    </h1>

                    <p>
                        Update the patient's prescription details.
                    </p>

                </div>


            </div>


        </div>



        <!-- PATIENT -->

        <div class="patient-bar">


            <div class="patient-info">


                <div class="patient-icon">

                    <i class="fa-solid fa-user"></i>

                </div>


                <div>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $prescription['patient_name']
                        );

                        ?>

                    </strong>


                    <span>
                        Prescription #
                        <?php
                        echo $prescription_id;
                        ?>
                    </span>

                </div>


            </div>


        </div>



        <!-- ERROR -->

        <?php echo $message; ?>



        <!-- FORM -->

        <form
            method="POST"
            class="form"
        >


            <!-- DIAGNOSIS -->

            <div class="form-group">


                <label>

                    <i class="fa-solid fa-stethoscope"></i>

                    Diagnosis

                </label>


                <input
                    type="text"
                    name="diagnosis"
                    value="<?php

                        echo htmlspecialchars(
                            $prescription['diagnosis']
                        );

                    ?>"
                    required
                >


            </div>



            <!-- MEDICINES -->

            <div class="form-group">


                <label>

                    <i class="fa-solid fa-pills"></i>

                    Medicines & Dosage

                </label>


                <textarea
                    name="medicines"
                    required
                ><?php

                    echo htmlspecialchars(
                        $prescription['medicines']
                    );

                ?></textarea>


            </div>



            <!-- ADVICE -->

            <div class="form-group">


                <label>

                    <i class="fa-solid fa-notes-medical"></i>

                    Doctor's Advice

                </label>


                <textarea
                    name="advice"
                ><?php

                    echo htmlspecialchars(
                        $prescription['advice']
                    );

                ?></textarea>


            </div>



            <!-- BUTTONS -->

            <div class="form-actions">


                <button
                    type="submit"
                    name="update_prescription"
                    class="update-btn"
                >

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Changes

                </button>


                <a
                    href="view-prescription.php?id=<?php
                        echo $prescription_id;
                    ?>"
                    class="cancel-btn"
                >

                    <i class="fa-solid fa-xmark"></i>

                    Cancel

                </a>


            </div>



            <div class="note">

                <i class="fa-solid fa-circle-info"></i>

                Only prescriptions created by your own
                doctor account can be edited.

            </div>


        </form>


    </div>


</div>


</body>

</html>