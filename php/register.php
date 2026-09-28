<?php
session_start();
include "connectDB.php";

$user  = trim($_POST['username']  ?? '');
$phone = trim($_POST['phone']     ?? '');
$pwd1  = $_POST['password1'] ?? '';
$pwd2  = $_POST['password2'] ?? '';

if ($user === '' || $phone === '' || $pwd1 === '' || $pwd2 === '') {
    header("Location: login.php?register_err=" . urlencode("All fields are required."));
    exit;
}

if ($pwd1 !== $pwd2) {
    header("Location: login.php?register_err=" . urlencode("Passwords do not match."));
    exit;
}

if (strlen($user) > 10 || strlen($pwd1) > 10 || strlen($phone) > 10) {
    header("Location: login.php?register_err=" . urlencode("Username / password / phone must be 10 characters or fewer."));
    exit;
}

$userEsc  = mysqli_real_escape_string($conn, $user);
$phoneEsc = mysqli_real_escape_string($conn, $phone);
$pwdEsc   = mysqli_real_escape_string($conn, $pwd1);

$check = mysqli_query($conn, "SELECT username FROM members WHERE username = '$userEsc'");
if ($check && mysqli_num_rows($check) > 0) {
    mysqli_close($conn);
    header("Location: login.php?register_err=" . urlencode("Username '$user' already exists. Please choose another."));
    exit;
}

$sql = "INSERT INTO members (username, password, phone)
        VALUES ('$userEsc', '$pwdEsc', '$phoneEsc')";
if (mysqli_query($conn, $sql)) {
    mysqli_close($conn);
    header("Location: login.php?register_ok=" . urlencode("Registration successful! Please log in."));
    exit;
} else {
    $err = mysqli_error($conn);
    mysqli_close($conn);
    header("Location: login.php?register_err=" . urlencode("Registration failed: " . $err));
    exit;
}
?>
