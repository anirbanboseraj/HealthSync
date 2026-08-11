<?php

session_start();

include("../config/database.php");

$error = "";

if (isset($_POST['login']))
{

    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $password = $_POST['password'];


    $query = mysqli_query(
        $conn,
        "
        SELECT *
        FROM doctors
        WHERE email='$email'
        "
    );


    if (mysqli_num_rows($query) > 0)
    {

        $doctor = mysqli_fetch_assoc($query);


        /*
            OLD LOGIN SYSTEM

            Password is checked directly
            against the doctors table.
        */

        if ($password == $doctor['password'])
        {

            $_SESSION['doctor_id'] =
                $doctor['id'];

            $_SESSION['doctor_name'] =
                $doctor['fullname'];


            header(
                "Location: dashboard.php"
            );

            exit();

        }
        else
        {

            $error = "Incorrect Password.";

        }

    }
    else
    {

        $error = "Doctor account not found.";

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
        Doctor Login
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


<div class="login-page">


    <div class="login-left">

        <img
            src="../assets/images/hero/doctor.png"
            alt="Doctor"
        >


        <h1>

            Health<span>Sync</span>

        </h1>


        <p>

            Doctor Portal

        </p>

    </div>



    <div class="login-right">


        <form
            method="POST"
            class="login-form"
        >


            <h2>

                <i class="fa-solid fa-user-doctor"></i>

                Doctor Login

            </h2>


            <?php

            if ($error != "")
            {

                echo "
                <div class='error'>
                    $error
                </div>
                ";

            }

            ?>


            <label>

                Email

            </label>


            <input
                type="email"
                name="email"
                placeholder="Enter Doctor Email"
                required
            >


            <br>
            <br>


            <label>

                Password

            </label>


            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >


            <br>
            <br>


            <button
                type="submit"
                name="login"
                class="register-btn"
            >

                Login

            </button>


            <br>
            <br>


            <a href="../index.php">

                ← Back to Home

            </a>


        </form>

    </div>


</div>


</body>

</html>