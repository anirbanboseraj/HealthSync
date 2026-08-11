<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../config/database.php");

$patient = $_SESSION['user_id'];


/* =========================
   GET MEDICAL RECORDS
========================= */

$query = mysqli_query(
    $conn,
    "SELECT *
     FROM medical_records
     WHERE patient_id = '$patient'
     ORDER BY uploaded_at DESC"
);

$records = [];

while ($row = mysqli_fetch_assoc($query)) {
    $records[] = $row;
}


/* =========================
   RECORD COUNTS
========================= */

$totalRecords = count($records);

$bloodReports = 0;
$xrayReports = 0;
$mriReports = 0;
$prescriptions = 0;

foreach ($records as $record) {

    $type = strtolower(trim($record['record_type']));

    if ($type === 'blood report') {
        $bloodReports++;
    }

    if ($type === 'x-ray') {
        $xrayReports++;
    }

    if ($type === 'mri') {
        $mriReports++;
    }

    if ($type === 'prescription') {
        $prescriptions++;
    }
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

    <title>Medical Records | HealthSync</title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        /* =========================
           RESET
        ========================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #0f172a;

            color: #f8fafc;

            min-height: 100vh;
        }


        /* =========================
           LAYOUT
        ========================= */

        .dashboard {

            display: flex;

            min-height: 100vh;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            width: 250px;

            background: #111827;

            border-right: 1px solid #243044;

            position: fixed;

            left: 0;

            top: 0;

            bottom: 0;

            padding: 28px 18px;

            z-index: 100;
        }


        .logo {

            padding: 0 14px 28px;

            border-bottom: 1px solid #273449;

            margin-bottom: 24px;
        }


        .logo h2 {

            font-size: 27px;

            color: #f8fafc;

            letter-spacing: -0.5px;
        }


        .logo span {

            color: #3b82f6;
        }


        .logo p {

            color: #94a3b8;

            font-size: 12px;

            margin-top: 5px;
        }


        .sidebar ul {

            list-style: none;
        }


        .sidebar li {

            margin-bottom: 8px;
        }


        .sidebar a {

            display: flex;

            align-items: center;

            gap: 13px;

            text-decoration: none;

            color: #cbd5e1;

            padding: 13px 15px;

            border-radius: 10px;

            font-size: 15px;

            transition: 0.25s;
        }


        .sidebar a i {

            width: 20px;

            text-align: center;
        }


        .sidebar a:hover {

            background: #1e293b;

            color: white;
        }


        .sidebar li.active a {

            background: #2563eb;

            color: white;

            box-shadow:
                0 8px 20px
                rgba(37, 99, 235, 0.25);
        }


        /* =========================
           MAIN CONTENT
        ========================= */

        .main-content {

            margin-left: 250px;

            width: calc(100% - 250px);

            padding: 40px 45px 60px;
        }


        /* =========================
           PAGE HEADER
        ========================= */

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            margin-bottom: 30px;
        }


        .page-header h1 {

            font-size: 34px;

            margin-bottom: 7px;

            letter-spacing: -0.7px;
        }


        .page-header p {

            color: #94a3b8;

            font-size: 15px;
        }


        .secure-badge {

            display: flex;

            align-items: center;

            gap: 8px;

            background: #13243b;

            border: 1px solid #23416b;

            color: #60a5fa;

            padding: 10px 14px;

            border-radius: 9px;

            font-size: 13px;
        }


        /* =========================
           STAT CARDS
        ========================= */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 30px;
        }


        .stat-card {

            background: #1b263b;

            border: 1px solid #293750;

            border-radius: 14px;

            padding: 20px;

            display: flex;

            align-items: center;

            gap: 15px;

            transition: 0.25s;
        }


        .stat-card:hover {

            transform: translateY(-3px);

            border-color: #3566a8;
        }


        .stat-icon {

            width: 48px;

            height: 48px;

            border-radius: 12px;

            background: #172e53;

            color: #60a5fa;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 19px;
        }


        .stat-card h3 {

            font-size: 24px;

            margin-bottom: 3px;
        }


        .stat-card p {

            color: #94a3b8;

            font-size: 13px;
        }


        /* =========================
           UPLOAD SECTION
        ========================= */

        .upload-section {

            background: #1b263b;

            border: 1px solid #293750;

            border-radius: 16px;

            padding: 25px;

            margin-bottom: 32px;
        }


        .section-title {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 22px;
        }


        .section-title-icon {

            width: 42px;

            height: 42px;

            background: #172e53;

            color: #60a5fa;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .section-title h2 {

            font-size: 20px;

            margin-bottom: 3px;
        }


        .section-title p {

            color: #94a3b8;

            font-size: 13px;
        }


        .upload-form {

            display: grid;

            grid-template-columns:
                1fr
                1fr
                1.4fr
                auto;

            gap: 15px;

            align-items: end;
        }


        .form-group label {

            display: block;

            color: #cbd5e1;

            font-size: 13px;

            margin-bottom: 8px;
        }


        .form-group input,
        .form-group select {

            width: 100%;

            height: 46px;

            padding: 0 13px;

            border-radius: 9px;

            border: 1px solid #334155;

            background: #111827;

            color: #f8fafc;

            outline: none;

            font-size: 14px;
        }


        .form-group input:focus,
        .form-group select:focus {

            border-color: #3b82f6;

            box-shadow:
                0 0 0 3px
                rgba(59, 130, 246, 0.1);
        }


        .form-group input[type="file"] {

            padding-top: 12px;

            cursor: pointer;
        }


        .upload-btn {

            height: 46px;

            padding: 0 22px;

            border: none;

            border-radius: 9px;

            background: #2563eb;

            color: white;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.25s;

            white-space: nowrap;
        }


        .upload-btn:hover {

            background: #1d4ed8;

            transform: translateY(-1px);
        }


        /* =========================
           RECORDS HEADER
        ========================= */

        .records-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 18px;
        }


        .records-header h2 {

            font-size: 22px;
        }


        .records-count {

            background: #1e293b;

            border: 1px solid #334155;

            color: #94a3b8;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;
        }


        /* =========================
           RECORD GRID
        ========================= */

        .records-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;
        }


        .record-card {

            background: #1b263b;

            border: 1px solid #293750;

            border-radius: 15px;

            padding: 20px;

            transition: 0.25s;

            min-width: 0;
        }


        .record-card:hover {

            transform: translateY(-3px);

            border-color: #3b5f91;

            box-shadow:
                0 12px 30px
                rgba(0, 0, 0, 0.18);
        }


        .record-top {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 12px;

            margin-bottom: 18px;
        }


        .record-icon {

            width: 48px;

            height: 48px;

            background: #172e53;

            color: #60a5fa;

            border-radius: 11px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;

            flex-shrink: 0;
        }


        .record-type {

            background: #162b4a;

            color: #60a5fa;

            border: 1px solid #23416b;

            padding: 6px 9px;

            border-radius: 7px;

            font-size: 11px;

            white-space: nowrap;
        }


        .record-card h3 {

            font-size: 17px;

            margin-bottom: 8px;

            word-break: break-word;
        }


        .record-date {

            display: flex;

            align-items: center;

            gap: 7px;

            color: #94a3b8;

            font-size: 12px;

            margin-bottom: 18px;
        }


        .record-file {

            color: #64748b;

            font-size: 12px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;

            margin-bottom: 17px;
        }


        /* =========================
           ACTION BUTTONS
        ========================= */

        .record-actions {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 8px;
        }


        .record-actions a {

            height: 39px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            border-radius: 8px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 600;

            transition: 0.2s;
        }


        .view-btn {

            background: #2563eb;

            color: white;
        }


        .view-btn:hover {

            background: #1d4ed8;
        }


        .download-btn {

            border: 1px solid #315da1;

            color: #60a5fa;

            background: transparent;
        }


        .download-btn:hover {

            background: #162b4a;
        }


        .delete-btn {

            grid-column: 1 / -1;

            color: #f87171;

            border: 1px solid #4b2930;

            background: #21171b;
        }


        .delete-btn:hover {

            background: #351a20;

            border-color: #7f2935;
        }


        /* =========================
           EMPTY STATE
        ========================= */

        .empty-state {

            background: #1b263b;

            border: 1px dashed #334155;

            border-radius: 16px;

            padding: 55px 25px;

            text-align: center;
        }


        .empty-icon {

            width: 70px;

            height: 70px;

            border-radius: 50%;

            background: #172e53;

            color: #60a5fa;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 27px;

            margin: 0 auto 18px;
        }


        .empty-state h3 {

            font-size: 19px;

            margin-bottom: 8px;
        }


        .empty-state p {

            color: #94a3b8;

            font-size: 14px;

            max-width: 420px;

            margin: auto;

            line-height: 1.6;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1200px) {

            .stats {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .records-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .upload-form {

                grid-template-columns:
                    1fr 1fr;
            }


            .upload-btn {

                width: 100%;
            }
        }


        @media (max-width: 800px) {

            .sidebar {

                width: 210px;
            }


            .main-content {

                margin-left: 210px;

                width: calc(100% - 210px);

                padding: 30px 20px;
            }


            .records-grid {

                grid-template-columns: 1fr;
            }


            .page-header {

                flex-direction: column;

                gap: 15px;
            }
        }


        @media (max-width: 600px) {

            .sidebar {

                position: relative;

                width: 100%;

                min-height: auto;
            }


            .dashboard {

                display: block;
            }


            .main-content {

                margin-left: 0;

                width: 100%;

                padding: 25px 15px;
            }


            .stats {

                grid-template-columns: 1fr;
            }


            .upload-form {

                grid-template-columns: 1fr;
            }


            .page-header h1 {

                font-size: 28px;
            }
        }

    </style>

</head>


<body>


<div class="dashboard">


    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="sidebar">

        <div class="logo">

            <h2>
                Health<span>Sync</span>
            </h2>

            <p>Digital Healthcare Portal</p>

        </div>


        <ul>

            <li>

                <a href="dashboard.php">

                    <i class="fa-solid fa-house"></i>

                    Dashboard

                </a>

            </li>


            <li class="active">

                <a href="medical-records.php">

                    <i class="fa-solid fa-file-medical"></i>

                    Medical Records

                </a>

            </li>


            <li>

                <a href="profile.php">

                    <i class="fa-solid fa-user"></i>

                    Profile

                </a>

            </li>


            <li>

                <a href="../logout.php">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Logout

                </a>

            </li>

        </ul>

    </aside>



    <!-- =========================
         MAIN
    ========================== -->

    <main class="main-content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h1>
                    Medical Records
                </h1>

                <p>
                    Securely manage and access your medical documents.
                </p>

            </div>


            <div class="secure-badge">

                <i class="fa-solid fa-shield-halved"></i>

                Secure & Private

            </div>

        </div>



        <!-- =========================
             STATISTICS
        ========================== -->

        <div class="stats">


            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-file-medical"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $totalRecords; ?>
                    </h3>

                    <p>
                        Total Records
                    </p>

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-vial"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $bloodReports; ?>
                    </h3>

                    <p>
                        Blood Reports
                    </p>

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-file-prescription"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $prescriptions; ?>
                    </h3>

                    <p>
                        Prescriptions
                    </p>

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-x-ray"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $xrayReports + $mriReports; ?>
                    </h3>

                    <p>
                        Imaging Reports
                    </p>

                </div>

            </div>

        </div>



        <!-- =========================
             UPLOAD RECORD
        ========================== -->

        <section class="upload-section">


            <div class="section-title">

                <div class="section-title-icon">

                    <i class="fa-solid fa-cloud-arrow-up"></i>

                </div>

                <div>

                    <h2>
                        Upload Medical Record
                    </h2>

                    <p>
                        Add a new medical document to your secure records.
                    </p>

                </div>

            </div>



            <form

                action="upload-record.php"

                method="POST"

                enctype="multipart/form-data"

                class="upload-form"
            >


                <div class="form-group">

                    <label>
                        Record Title
                    </label>

                    <input

                        type="text"

                        name="title"

                        placeholder="e.g. Blood Test Report"

                        required
                    >

                </div>



                <div class="form-group">

                    <label>
                        Record Type
                    </label>

                    <select name="type" required>

                        <option value="Blood Report">
                            Blood Report
                        </option>

                        <option value="X-Ray">
                            X-Ray
                        </option>

                        <option value="MRI">
                            MRI
                        </option>

                        <option value="Prescription">
                            Prescription
                        </option>

                        <option value="Other">
                            Other
                        </option>

                    </select>

                </div>



                <div class="form-group">

                    <label>
                        Medical File
                    </label>

                    <input

                        type="file"

                        name="record"

                        accept=".pdf,.jpg,.jpeg,.png"

                        required
                    >

                </div>



                <button

                    type="submit"

                    class="upload-btn"
                >

                    <i class="fa-solid fa-upload"></i>

                    Upload Record

                </button>


            </form>

        </section>



        <!-- =========================
             RECORDS
        ========================== -->

        <div class="records-header">

            <h2>
                My Medical Records
            </h2>


            <span class="records-count">

                <?php echo $totalRecords; ?>

                Record<?php echo ($totalRecords != 1) ? 's' : ''; ?>

            </span>

        </div>



        <?php if ($totalRecords > 0): ?>


            <div class="records-grid">


                <?php foreach ($records as $row): ?>


                    <?php

                    $file =
                        "../assets/uploads/medical/"
                        . $row['file_name'];

                    $extension =
                        strtolower(
                            pathinfo(
                                $row['file_name'],
                                PATHINFO_EXTENSION
                            )
                        );


                    /* Select icon */

                    if ($extension === 'pdf') {

                        $fileIcon = "fa-file-pdf";

                    } elseif (
                        $extension === 'jpg' ||
                        $extension === 'jpeg' ||
                        $extension === 'png'
                    ) {

                        $fileIcon = "fa-file-image";

                    } else {

                        $fileIcon = "fa-file";

                    }

                    ?>


                    <div class="record-card">


                        <div class="record-top">

                            <div class="record-icon">

                                <i
                                    class="fa-solid
                                    <?php echo $fileIcon; ?>"
                                ></i>

                            </div>


                            <span class="record-type">

                                <?php

                                echo htmlspecialchars(
                                    $row['record_type']
                                );

                                ?>

                            </span>

                        </div>



                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $row['title']
                            );

                            ?>

                        </h3>



                        <div class="record-date">

                            <i class="fa-regular fa-calendar"></i>

                            <?php

                            echo date(
                                "d M Y",
                                strtotime(
                                    $row['uploaded_at']
                                )
                            );

                            ?>

                        </div>



                        <div class="record-file">

                            <i class="fa-solid fa-paperclip"></i>

                            <?php

                            echo htmlspecialchars(
                                $row['file_name']
                            );

                            ?>

                        </div>



                        <div class="record-actions">


                            <!-- VIEW -->

                            <a

                                href="<?php echo $file; ?>"

                                target="_blank"

                                class="view-btn"
                            >

                                <i class="fa-solid fa-eye"></i>

                                View

                            </a>



                            <!-- DOWNLOAD -->

                            <a

                                href="<?php echo $file; ?>"

                                download

                                class="download-btn"
                            >

                                <i class="fa-solid fa-download"></i>

                                Download

                            </a>



                            <!-- DELETE -->

                            <a

                                href="delete-record.php?id=<?php echo $row['id']; ?>"

                                class="delete-btn"

                                onclick="return confirm(
                                    'Are you sure you want to delete this medical record?'
                                );"
                            >

                                <i class="fa-solid fa-trash"></i>

                                Delete Record

                            </a>


                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <!-- EMPTY STATE -->

            <div class="empty-state">


                <div class="empty-icon">

                    <i class="fa-solid fa-folder-open"></i>

                </div>


                <h3>
                    No Medical Records Yet
                </h3>


                <p>

                    Your medical reports, prescriptions,
                    X-rays and other documents will appear
                    here after you upload them.

                </p>


            </div>


        <?php endif; ?>


    </main>


</div>


</body>

</html>