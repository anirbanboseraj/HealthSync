<?php
session_start();
include("config/database.php");

$message = "";

if(isset($_POST['login']))
{
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($query)==1)
    {
        $user = mysqli_fetch_assoc($query);

        if(password_verify($password,$user['password']))
        {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if($user['role']=="admin")
            {
                header("Location: admin/dashboard.php");
            }
            elseif($user['role']=="doctor")
            {
                header("Location: doctor/dashboard.php");
            }
            else
            {
                header("Location: patient/dashboard.php");
            }

            exit();
        }
        else
        {
            $message="<div class='error'>Incorrect password!</div>";
        }
    }
    else
    {
        $message="<div class='error'>Email not found!</div>";
    }
}

include("includes/header.php");
include("includes/navbar.php");
?>

<section class="register-page">

<div class="register-container">

<div class="register-image">
<img src="assets/images/hero/doctor.png">
</div>

<div class="register-content">

<h1>Welcome Back</h1>

<p>Login to your HealthSync account.</p>

<?php echo $message; ?>

<form method="POST" class="register-form">

<div class="input-group">
<label>Email</label>
<input type="email" name="email" required>
</div>

<div class="input-group">
<label>Password</label>
<input type="password" name="password" required>
</div>

<button class="register-btn" name="login">
Login
</button>

</form>

<div class="login-link">

Don't have an account?

<a href="register.php">
Register
</a>

</div>

</div>

</div>

</section>

<?php
include("includes/footer.php");
?>