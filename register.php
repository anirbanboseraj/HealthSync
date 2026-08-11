<?php

session_start();

require_once "config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $role = $_POST["role"] ?? "patient";

    // Doctor-specific fields
    $specialization = trim($_POST["specialization"] ?? "");
    $experience = !empty($_POST["experience"])
        ? intval($_POST["experience"])
        : null;

    $qualification = trim($_POST["qualification"] ?? "");
    $about = trim($_POST["about"] ?? "");

    /*
    |--------------------------------------------------------------------------
    | BASIC VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($fullname) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all required fields.";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    }

    elseif (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    }

    elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    }

    elseif (!in_array($role, ["patient", "doctor"])) {

        $error = "Invalid account type.";

    }

    elseif (
        $role === "doctor" &&
        empty($specialization)
    ) {

        $error = "Please enter your specialization.";

    }

    else {

        /*
        |--------------------------------------------------------------------------
        | CHECK DUPLICATE EMAIL
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        $check->bind_param("s", $email);

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "An account with this email already exists.";

        }

        $check->close();
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE ACCOUNT
    |--------------------------------------------------------------------------
    */

    if (empty($error)) {

        try {

            /*
            |--------------------------------------------------------------------------
            | START TRANSACTION
            |--------------------------------------------------------------------------
            */

            $conn->begin_transaction();


            /*
            |--------------------------------------------------------------------------
            | HASH PASSWORD
            |--------------------------------------------------------------------------
            */

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /*
            |--------------------------------------------------------------------------
            | INSERT INTO USERS
            |--------------------------------------------------------------------------
            */

            $user_sql = "
                INSERT INTO users
                (
                    fullname,
                    email,
                    password,
                    role,
                    status,
                    phone
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    'active',
                    ?
                )
            ";

            $user_stmt = $conn->prepare($user_sql);

            $user_stmt->bind_param(
                "sssss",
                $fullname,
                $email,
                $hashed_password,
                $role,
                $phone
            );

            if (!$user_stmt->execute()) {

                throw new Exception(
                    "Failed to create user account."
                );

            }


            /*
            |--------------------------------------------------------------------------
            | GET NEW USER ID
            |--------------------------------------------------------------------------
            */

            $user_id = $conn->insert_id;


            /*
            |--------------------------------------------------------------------------
            | IF DOCTOR → CREATE DOCTOR PROFILE
            |--------------------------------------------------------------------------
            */

            if ($role === "doctor") {

                $doctor_sql = "
                    INSERT INTO doctors
                    (
                        fullname,
                        specialization,
                        email,
                        phone,
                        experience,
                        status,
                        password,
                        qualification,
                        about,
                        user_id
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'Available',
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ";

                $doctor_stmt = $conn->prepare(
                    $doctor_sql
                );

                $doctor_stmt->bind_param(
                    "ssssisssi",
                    $fullname,
                    $specialization,
                    $email,
                    $phone,
                    $experience,
                    $hashed_password,
                    $qualification,
                    $about,
                    $user_id
                );

                if (!$doctor_stmt->execute()) {

                    throw new Exception(
                        "Failed to create doctor profile."
                    );

                }

                $doctor_stmt->close();
            }


            /*
            |--------------------------------------------------------------------------
            | EVERYTHING SUCCESSFUL
            |--------------------------------------------------------------------------
            */

            $conn->commit();

            $user_stmt->close();

            $success =
                "Registration successful! You can now login.";

        }

        catch (Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | ROLLBACK IF SOMETHING FAILS
            |--------------------------------------------------------------------------
            */

            $conn->rollback();

            $error =
                "Registration failed. Please try again.";

            // For development only:
            // $error = $e->getMessage();
        }

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

    <title>
        Register - HealthSync
    </title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family: Arial, sans-serif;

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #050914,
                    #0b1730
                );

            display: flex;

            justify-content: center;

            align-items: center;

            color: #fff;

            padding: 30px;

        }


        .register-container {

            width: 100%;

            max-width: 850px;

            background: #111a2d;

            border: 1px solid #263553;

            border-radius: 18px;

            padding: 35px;

            box-shadow:
                0 20px 60px
                rgba(0,0,0,0.45);

        }


        .logo {

            text-align: center;

            margin-bottom: 25px;

        }


        .logo h1 {

            font-size: 32px;

            color: #fff;

        }


        .logo h1 span {

            color: #3b82f6;

        }


        .logo p {

            color: #7f8da8;

            margin-top: 6px;

        }


        .form-title {

            font-size: 24px;

            margin-bottom: 25px;

        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }


        .form-group {

            display: flex;

            flex-direction: column;

        }


        .form-group.full {

            grid-column: 1 / -1;

        }


        label {

            margin-bottom: 8px;

            color: #aebbd2;

            font-size: 14px;

            font-weight: 600;

        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 13px 14px;

            border-radius: 9px;

            border: 1px solid #2b3a59;

            background: #070d1c;

            color: #fff;

            outline: none;

            font-size: 14px;

        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color: #3b82f6;

        }


        textarea {

            min-height: 100px;

            resize: vertical;

        }


        .role-box {

            display: flex;

            gap: 15px;

        }


        .role-option {

            flex: 1;

            padding: 15px;

            border: 1px solid #2b3a59;

            border-radius: 10px;

            cursor: pointer;

            text-align: center;

            background: #080f20;

            transition: 0.2s;

        }


        .role-option:hover {

            border-color: #3b82f6;

        }


        .role-option input {

            display: none;

        }


        .role-option.active {

            border-color: #3b82f6;

            background: #172b57;

        }


        .doctor-fields {

            display: none;

            grid-column: 1 / -1;

            padding: 20px;

            border-radius: 12px;

            border: 1px solid #263553;

            background: #0b1325;

        }


        .doctor-fields.show {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }


        .doctor-fields h3 {

            grid-column: 1 / -1;

            color: #60a5fa;

            margin-bottom: 5px;

        }


        .error {

            background: rgba(239,68,68,0.12);

            border: 1px solid #ef4444;

            color: #fca5a5;

            padding: 13px;

            border-radius: 9px;

            margin-bottom: 20px;

        }


        .success {

            background: rgba(34,197,94,0.12);

            border: 1px solid #22c55e;

            color: #86efac;

            padding: 13px;

            border-radius: 9px;

            margin-bottom: 20px;

        }


        .register-btn {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 9px;

            background: #2563eb;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 25px;

        }


        .register-btn:hover {

            background: #1d4ed8;

        }


        .login-link {

            text-align: center;

            margin-top: 20px;

            color: #8795ad;

        }


        .login-link a {

            color: #60a5fa;

            text-decoration: none;

        }


        @media(max-width: 650px) {

            .form-grid {

                grid-template-columns: 1fr;

            }


            .form-group.full {

                grid-column: auto;

            }


            .doctor-fields.show {

                grid-template-columns: 1fr;

            }


            .role-box {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<div class="register-container">


    <div class="logo">

        <h1>
            Health<span>Sync</span>
        </h1>

        <p>
            Advanced Digital Healthcare Portal
        </p>

    </div>


    <h2 class="form-title">
        Create Your Account
    </h2>


    <?php if (!empty($error)): ?>

        <div class="error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($success)): ?>

        <div class="success">

            <i class="fa-solid fa-circle-check"></i>

            <?php echo htmlspecialchars($success); ?>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        action=""
    >


        <div class="form-grid">


            <!-- FULL NAME -->

            <div class="form-group">

                <label>
                    Full Name *
                </label>

                <input
                    type="text"
                    name="fullname"
                    placeholder="Enter your full name"
                    value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label>
                    Email *
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="example@gmail.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                >

            </div>


            <!-- PHONE -->

            <div class="form-group">

                <label>
                    Phone
                </label>

                <input
                    type="text"
                    name="phone"
                    placeholder="01XXXXXXXXX"
                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                >

            </div>


            <!-- ACCOUNT TYPE -->

            <div class="form-group">

                <label>
                    Account Type *
                </label>


                <div class="role-box">


                    <label class="role-option active">

                        <input
                            type="radio"
                            name="role"
                            value="patient"
                            checked
                        >

                        <i class="fa-solid fa-user"></i>

                        Patient

                    </label>


                    <label class="role-option">

                        <input
                            type="radio"
                            name="role"
                            value="doctor"
                        >

                        <i class="fa-solid fa-user-doctor"></i>

                        Doctor

                    </label>


                </div>

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>
                    Password *
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                >

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label>
                    Confirm Password *
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Repeat password"
                    required
                >

            </div>


            <!-- DOCTOR FIELDS -->

            <div
                class="doctor-fields"
                id="doctorFields"
            >


                <h3>

                    <i class="fa-solid fa-user-doctor"></i>

                    Doctor Information

                </h3>


                <!-- SPECIALIZATION -->

                <div class="form-group">

                    <label>
                        Specialization *
                    </label>

                    <input
                        type="text"
                        name="specialization"
                        placeholder="e.g. Cardiologist"
                    >

                </div>


                <!-- EXPERIENCE -->

                <div class="form-group">

                    <label>
                        Experience (Years)
                    </label>

                    <input
                        type="number"
                        name="experience"
                        min="0"
                        placeholder="e.g. 5"
                    >

                </div>


                <!-- QUALIFICATION -->

                <div class="form-group">

                    <label>
                        Qualification
                    </label>

                    <input
                        type="text"
                        name="qualification"
                        placeholder="e.g. MBBS, FCPS"
                    >

                </div>


                <!-- ABOUT -->

                <div class="form-group">

                    <label>
                        About Doctor
                    </label>

                    <textarea
                        name="about"
                        placeholder="Write a short professional description..."
                    ></textarea>

                </div>


            </div>


        </div>


        <button
            type="submit"
            class="register-btn"
        >

            <i class="fa-solid fa-user-plus"></i>

            Create Account

        </button>


    </form>


    <div class="login-link">

        Already have an account?

        <a href="login.php">
            Login here
        </a>

    </div>


</div>


<script>


const roleOptions =
    document.querySelectorAll(
        ".role-option"
    );


const doctorFields =
    document.getElementById(
        "doctorFields"
    );


function updateRole() {

    const selected =
        document.querySelector(
            'input[name="role"]:checked'
        );


    roleOptions.forEach(
        option => {

            const input =
                option.querySelector(
                    "input"
                );

            option.classList.toggle(
                "active",
                input.checked
            );

        }
    );


    if (
        selected &&
        selected.value === "doctor"
    ) {

        doctorFields.classList.add(
            "show"
        );

    }
    else {

        doctorFields.classList.remove(
            "show"
        );

    }

}


roleOptions.forEach(
    option => {

        option.addEventListener(
            "click",
            updateRole
        );

    }
);


updateRole();

</script>


</body>

</html>