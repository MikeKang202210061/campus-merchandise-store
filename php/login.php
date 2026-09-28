<?php
session_start();

$loggedIn = isset($_SESSION['username']);

// Messages passed back via redirect from login_check.php / register.php
$loginError    = isset($_GET['login_err'])    ? $_GET['login_err']    : '';
$registerError = isset($_GET['register_err']) ? $_GET['register_err'] : '';
$registerOk    = isset($_GET['register_ok'])  ? $_GET['register_ok']  : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login / Register</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>


    <!-- title bar-->
    <div class="dgray">Login

    <!-- navigation bar-->
    <div class="blue">
        &nbsp;<a href="login.php">Login/Register</a> &nbsp;
        <a href="clothes.php">Clothes</a> &nbsp;
        <a href="neces.php">Necessities</a> &nbsp;
        <a href="orna.php">Ornaments</a>
    </div>

    <?php if ($loggedIn): ?>

        <!-- if already logged in, replace both panels with a welcome -->
        <div class="gray" style="width:100%;">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>You are already logged in.</p>
            <p>
                <a href="clothes.php">Clothes</a> |
                <a href="neces.php">Necessities</a> |
                <a href="orna.php">Ornaments</a> |
                <a href="logout.php">Logout</a>
            </p>
        </div>

    <?php else: ?>

        <!-- Login panel -->
        <div class="gray">
            <h2>Login Member</h2>

            <?php if ($loginError): ?>
                <p style="color:red;"><?php echo htmlspecialchars($loginError); ?></p>
            <?php endif; ?>
            <?php if ($registerOk): ?>
                <p style="color:green;"><?php echo htmlspecialchars($registerOk); ?></p>
            <?php endif; ?>

            <form action="login_check.php" method="POST">
                <p><input type="text"     name="username" placeholder="Username" required></p>
                <p><input type="password" name="password" placeholder="Password" required></p>
                <p><input type="submit" value="Submit"></p>
            </form>
        </div>

        <!-- Register panel -->
        <div class="brown">
            <div style="text-align:center; padding:20px 30px 0 30px;">
                <h2>Register New Member</h2>

                <?php if ($registerError): ?>
                    <p style="color:red;"><?php echo htmlspecialchars($registerError); ?></p>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <p><input type="text" name="username" placeholder="Username" required></p>
                    <p><input type="tel" name="phone" placeholder="phone" pattern="[0-9]+" inputmode="numeric" required></p>

                    <p>Password:<br>
                       <input type="password" name="password1" placeholder="****" required></p>

                    <p>Confirm password:<br>
                       <input type="password" name="password2" placeholder="****" required></p>

                    <p>
                        <input type="submit" value="Submit">
                        <input type="reset"  value="Reset">
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>


</body>
</html>
