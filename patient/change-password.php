<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

include("../config/database.php");

$message="";

$id=$_SESSION['user_id'];

if(isset($_POST['change']))
{

$current=$_POST['current_password'];

$new=$_POST['new_password'];

$confirm=$_POST['confirm_password'];

$user=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT *
FROM users
WHERE id='$id'
"));

if(!password_verify($current,$user['password']))
{

$message="<div class='error'>Current password is incorrect.</div>";

}

elseif($new!=$confirm)
{

$message="<div class='error'>New passwords do not match.</div>";

}

else
{

$hash=password_hash($new,PASSWORD_DEFAULT);

mysqli_query($conn,"
UPDATE users
SET password='$hash'
WHERE id='$id'
");

$message="<div class='success'>Password changed successfully.</div>";

}

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Change Password</title>

<link rel="stylesheet"
href="../assets/css/dashboard.css">

</head>

<body>

<div class="profile-container">

<div class="profile-card">

<h1>🔒 Change Password</h1>

<?php echo $message; ?>

<form method="POST">

<label>Current Password</label>

<input
type="password"
name="current_password"
required>

<label>New Password</label>

<input
type="password"
name="new_password"
required>

<label>Confirm Password</label>

<input
type="password"
name="confirm_password"
required>

<br>

<button
class="register-btn"
name="change">

Change Password

</button>

<br><br>

<a href="profile.php">

← Back to Profile

</a>

</form>

</div>

</div>

</body>

</html>