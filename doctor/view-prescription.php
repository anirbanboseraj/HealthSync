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
// GET DOCTOR
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
// GET PRESCRIPTION ID
// =====================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid prescription ID.");
}

$prescription_id = intval($_GET['id']);


// =====================================================
// GET PRESCRIPTION
// =====================================================

$query = mysqli_query(
    $conn,
    "
    SELECT
        prescriptions.*,

        users.fullname AS patient_name,
        users.email AS patient_email,

        doctors.fullname AS doctor_name,
        doctors.specialization,
        doctors.qualification,
        doctors.phone AS doctor_phone

    FROM prescriptions

    INNER JOIN users
        ON prescriptions.patient_id = users.id

    INNER JOIN doctors
        ON prescriptions.doctor_id = doctors.id

    WHERE prescriptions.id='$prescription_id'

    AND prescriptions.doctor_id='$doctor_id'

    LIMIT 1
    "
);


if (!$query) {
    die(
        "Prescription query error: "
        . mysqli_error($conn)
    );
}


if (mysqli_num_rows($query) == 0) {
    die("Prescription not found.");
}


$prescription = mysqli_fetch_assoc($query);

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
        Prescription | HealthSync
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

    max-width: 950px;

    margin: 40px auto;

    padding: 0 20px;

}


/* =====================================================
   TOP BUTTONS
===================================================== */

.top-actions {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

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


.print-btn {

    border: none;

    cursor: pointer;

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 10px 17px;

    background: #2563eb;

    color: white;

    border-radius: 8px;

    font-size: 13px;

    font-weight: bold;

}


.print-btn:hover {

    background: #1d4ed8;

}


/* =====================================================
   PRESCRIPTION
===================================================== */

.prescription {

    background: white;

    color: #0f172a;

    border-radius: 12px;

    overflow: hidden;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,.35);

}


/* =====================================================
   HEADER
===================================================== */

.prescription-header {

    background: #0f172a;

    color: white;

    padding: 30px 35px;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.brand h1 {

    font-size: 30px;

    letter-spacing: .5px;

}


.brand h1 span {

    color: #3b82f6;

}


.brand p {

    color: #94a3b8;

    font-size: 12px;

    margin-top: 5px;

}


.doctor-title {

    text-align: right;

}


.doctor-title h2 {

    font-size: 18px;

}


.doctor-title p {

    color: #93c5fd;

    font-size: 12px;

    margin-top: 5px;

}


/* =====================================================
   BLUE LINE
===================================================== */

.blue-line {

    height: 5px;

    background: #2563eb;

}


/* =====================================================
   BODY
===================================================== */

.prescription-body {

    padding: 35px;

}


/* =====================================================
   PATIENT INFORMATION
===================================================== */

.patient-section {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 30px;

    padding-bottom: 25px;

    border-bottom: 1px solid #e2e8f0;

}


.info-title {

    font-size: 11px;

    color: #64748b;

    text-transform: uppercase;

    letter-spacing: .8px;

    margin-bottom: 7px;

}


.info-value {

    font-size: 15px;

    font-weight: bold;

    color: #0f172a;

}


.info-small {

    font-size: 12px;

    color: #64748b;

    margin-top: 4px;

}


/* =====================================================
   DATE
===================================================== */

.date-box {

    text-align: right;

}


.date-box .info-value {

    font-size: 13px;

}


/* =====================================================
   PRESCRIPTION SYMBOL
===================================================== */

.rx {

    font-family: Georgia, serif;

    font-size: 34px;

    font-weight: bold;

    color: #1d4ed8;

    margin: 25px 0 15px;

}


/* =====================================================
   MEDICAL SECTION
===================================================== */

.medical-section {

    margin-bottom: 25px;

}


.section-heading {

    display: flex;

    align-items: center;

    gap: 10px;

    padding-bottom: 10px;

    margin-bottom: 12px;

    border-bottom: 2px solid #dbeafe;

}


.section-heading i {

    color: #2563eb;

}


.section-heading h3 {

    font-size: 14px;

    color: #0f172a;

}


.content-box {

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 8px;

    padding: 16px;

    line-height: 1.7;

    font-size: 13px;

    color: #334155;

    white-space: pre-line;

}


/* =====================================================
   FOOTER
===================================================== */

.prescription-footer {

    padding: 25px 35px;

    background: #f8fafc;

    border-top: 1px solid #e2e8f0;

    display: flex;

    justify-content: space-between;

    gap: 30px;

}


.doctor-details h3 {

    font-size: 14px;

    color: #0f172a;

}


.doctor-details p {

    font-size: 11px;

    color: #64748b;

    margin-top: 5px;

}


.signature {

    min-width: 180px;

    text-align: center;

}


.signature-line {

    border-top: 1px solid #94a3b8;

    margin-top: 30px;

    padding-top: 7px;

    font-size: 11px;

    color: #64748b;

}


/* =====================================================
   NOTICE
===================================================== */

.notice {

    margin-top: 20px;

    text-align: center;

    color: #64748b;

    font-size: 11px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:650px) {

    .page {

        margin: 20px auto;

    }


    .top-actions {

        flex-direction: column;

        align-items: stretch;

        gap: 10px;

    }


    .back-btn,
    .print-btn {

        justify-content: center;

    }


    .prescription-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 20px;

    }


    .doctor-title {

        text-align: left;

    }


    .prescription-body {

        padding: 22px;

    }


    .patient-section {

        grid-template-columns: 1fr;

        gap: 20px;

    }


    .date-box {

        text-align: left;

    }


    .prescription-footer {

        flex-direction: column;

    }


    .signature {

        text-align: left;

    }

}


/* =====================================================
   PRINT
===================================================== */

@media print {

    body {

        background: white;

    }


    .page {

        margin: 0;

        max-width: none;

        padding: 0;

    }


    .top-actions {

        display: none;

    }


    .prescription {

        box-shadow: none;

        border-radius: 0;

    }


    .notice {

        display: none;

    }

}

</style>

</head>


<body>


<div class="page">


    <!-- =================================================
         TOP ACTIONS
    ================================================== -->

    <div class="top-actions">


        <a
            href="prescription.php"
            class="back-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Prescriptions

        </a>


        <button
            onclick="window.print()"
            class="print-btn"
        >

            <i class="fa-solid fa-print"></i>

            Print Prescription

        </button>


    </div>



    <!-- =================================================
         PRESCRIPTION
    ================================================== -->

    <div class="prescription">


        <!-- HEADER -->

        <div class="prescription-header">


            <div class="brand">

                <h1>
                    Health<span>Sync</span>
                </h1>

                <p>
                    Advanced Digital Healthcare Portal
                </p>

            </div>


            <div class="doctor-title">

                <h2>

                    <?php

                    echo htmlspecialchars(
                        $prescription['doctor_name']
                    );

                    ?>

                </h2>


                <p>

                    <?php

                    echo htmlspecialchars(
                        $prescription['specialization']
                    );

                    ?>

                </p>

            </div>


        </div>


        <div class="blue-line"></div>



        <!-- BODY -->

        <div class="prescription-body">


            <!-- PATIENT -->

            <div class="patient-section">


                <div>

                    <div class="info-title">
                        Patient
                    </div>


                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $prescription['patient_name']
                        );

                        ?>

                    </div>


                    <?php

                    if (
                        !empty(
                            $prescription['patient_email']
                        )
                    ) {

                    ?>

                    <div class="info-small">

                        <?php

                        echo htmlspecialchars(
                            $prescription['patient_email']
                        );

                        ?>

                    </div>

                    <?php

                    }

                    ?>

                </div>



                <div class="date-box">

                    <div class="info-title">
                        Prescription Date
                    </div>


                    <div class="info-value">

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


            </div>



            <!-- RX -->

            <div class="rx">
                ℞
            </div>



            <!-- DIAGNOSIS -->

            <div class="medical-section">


                <div class="section-heading">

                    <i class="fa-solid fa-stethoscope"></i>

                    <h3>
                        Diagnosis
                    </h3>

                </div>


                <div class="content-box">

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

            <div class="medical-section">


                <div class="section-heading">

                    <i class="fa-solid fa-pills"></i>

                    <h3>
                        Medicines & Dosage
                    </h3>

                </div>


                <div class="content-box">

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

            <div class="medical-section">


                <div class="section-heading">

                    <i class="fa-solid fa-notes-medical"></i>

                    <h3>
                        Doctor's Advice
                    </h3>

                </div>


                <div class="content-box">

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



        <!-- FOOTER -->

        <div class="prescription-footer">


            <div class="doctor-details">


                <h3>

                    <?php

                    echo htmlspecialchars(
                        $prescription['doctor_name']
                    );

                    ?>

                </h3>


                <?php

                if (
                    !empty(
                        $prescription['qualification']
                    )
                ) {

                ?>

                <p>

                    <?php

                    echo htmlspecialchars(
                        $prescription['qualification']
                    );

                    ?>

                </p>

                <?php

                }


                if (
                    !empty(
                        $prescription['doctor_phone']
                    )
                ) {

                ?>

                <p>

                    Phone:

                    <?php

                    echo htmlspecialchars(
                        $prescription['doctor_phone']
                    );

                    ?>

                </p>

                <?php

                }

                ?>


            </div>



            <div class="signature">

                <div class="signature-line">

                    Doctor's Signature

                </div>

            </div>


        </div>


    </div>



    <div class="notice">

        This prescription was generated through
        HealthSync Digital Healthcare Portal.

    </div>


</div>


</body>

</html>