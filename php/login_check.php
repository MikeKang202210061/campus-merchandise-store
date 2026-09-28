<?php
session_start();
include "connectDB.php";

$user = trim($_POST['username'] ?? '');
$pwd  = $_POST['password'] ?? '';

if ($user === '' || $pwd === '') {
    header("Location: login.php?login_err=" . urlencode("Username and password are required."));
    exit;
}

$userEsc = mysqli_real_escape_string($conn, $user);
$pwdEsc  = mysqli_real_escape_string($conn, $pwd);

$sql = "SELECT username FROM members
        WHERE username = '$userEsc' AND password = '$pwdEsc'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $_SESSION['username'] = $user;
    mysqli_close($conn);
    header("Location: login.php");
    exit;
} else {
    mysqli_close($conn);
    header("Location: login.php?login_err=" . urlencode("Incorrect username or password."));
    exit;
}
?>
