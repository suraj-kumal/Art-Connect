<?php
$err_msg = "";
session_start();
if (isset($_SESSION['username'])) {
    header('Location: /ArtConnect/admin/dashboard/dashboard.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === "naina" && $password === "nainashrestha") {
        $_SESSION['username'] = $username;
        header('Location: /ArtConnect/admin/dashboard/dashboard.php');
        exit();
    } else {
        $err_msg = "Invalid login credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/ArtConnect/assets/css/admin.css">
</head>

<body>

    <div class="form-container">
        <div class="err-msg">
            <span class="err-msg">
                <?php echo htmlspecialchars($err_msg); ?>
            </span><br>
        </div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h1>Admin</h1>
            <div class="input-container">
                <input type="text" id="usernamename" name="username" onkeypress="clear()" required autocomplete="off">
                <label for="input" class="label">username</label>
                <div class="underline"></div>
            </div>
            <div class="input-container">
                <input type="password" id="password" name="password" required autocomplete="off">
                <label for="input" class="label">password</label>
                <div class="underline"></div>
            </div>
            <div class="btn">
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
</body>

</html>