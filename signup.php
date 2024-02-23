<?php
$emailErrorMessage = '';
$phoneErrorMessage = '';
$passwordErrorMessage = '';
function isExistingBuyer($email, $phone, $pdo) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM buyer WHERE email = ? OR phone = ?');
        $stmt->execute([$email, $phone]);

        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Error in isExistingBuyer: ' . $e->getMessage());
        return false;
    }
}

// Function to check if email already exists
function isExistingEmail($email, $pdo) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM buyer WHERE email = ?');
        $stmt->execute([$email]);

        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Error in isExistingEmail: ' . $e->getMessage());
        return false;
    }
}

// Function to check if phone already exists
function isExistingPhone($phone, $pdo) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM buyer WHERE phone = ?');
        $stmt->execute([$phone]);

        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Error in isExistingPhone: ' . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    if (strlen($password) < 8) {
        $passwordErrorMessage = 'Password must be at least 8 characters long.';
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);
       // Hash the password using bcrypt
        try {
        $pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        header('Location: /ArtConnect/message/signup/signup_failed.html');
        exit();
    }

    if (isExistingBuyer($email, $phone, $pdo)) {
        if (isExistingEmail($email, $pdo)) {
            $emailErrorMessage = 'Email already exists. Please choose a different one.';
        }

        if (isExistingPhone($phone, $pdo)) {
            $phoneErrorMessage = 'Phone number already exists. Please choose a different one.';
        }
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO buyer (fullname, email, phone, password) VALUES (?, ?, ?, ?)');
            $stmt->execute([$fullname, $email, $phone, $password]);
            header('Location: /ArtConnect/message/signup/signup_success.html');
        } catch (PDOException $e) {
            error_log('Error in registration: ' . $e->getMessage());
            header('Location: /ArtConnect/message/signup/signup_failed.html');
        }
    }}
    $pdo = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account</title>
    <link rel="stylesheet" href="assets/css/signup.css">
</head>
<body>
    <div class="container">
        <div class="regis">
            <h2>Create Your Account</h2>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="input-container">
                <input type="text" id="fullname" name="fullname" required autocomplete="off">
                <label for="input" class="label">Enter Fullname</label>
                <div class="underline"></div>
            </div>

            <div class="err-msg">
                <span class="err-msg"><?php echo htmlspecialchars($emailErrorMessage); ?></span><br>
            </div>

            <div class="input-container-l">
                <input type="email" id="email" name="email" required autocomplete="off">
                <label for="input" class="label">Email</label>
                <div class="underline"></div>
            </div>
            
            <div class="err-msg">
                <span class="err-msg"><?php echo htmlspecialchars($phoneErrorMessage); ?></span><br>                
            </div>

            <div class="input-container-l">
                <input type="number" id="phone" name="phone" required autocomplete="off">
                <label for="input" class="label">Phone Number</label>
                <div class="underline"></div>
            </div>
            
            <div class="err-msg">
                <span class="err-msg"><?php echo htmlspecialchars($passwordErrorMessage); ?></span><br>
            </div>

            <div class="input-container-l">
                <input type="password" id="password" name="password" required autocomplete="off">
                <label for="input" class="label">Password</label>
                <div class="underline"></div>
            </div>
            <br>
            <div class="btn-sub">
                <button type="submit">Sign up</button>
            </div>
        </form>
        <div class="wl">
        already have an account? <a href="/ArtConnect/login.php" class="login-si">Login</a>
        </div>
        
    </div>
</body>
</html>

