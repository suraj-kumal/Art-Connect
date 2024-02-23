<?php

$msg = "";
$success = "";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset-request-submit'])) {
    $email = $_POST['email'];
    $userType = $_POST['userType'];

    // Validate the email (you can add more validation)
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

        // Check if the email exists in either the "artist" or "buyer" table
        $pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Choose the table based on the user type
        $tableName = ($userType === 'artist') ? 'artist' : 'buyer';

        try {
            $stmt = $pdo->prepare("SELECT id, fullname FROM $tableName WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Generate a unique token (you may use a library for better security)
                $token = bin2hex(random_bytes(32));

                // Store the token directly in the artist or buyer table
                $updateTokenStmt = $pdo->prepare("UPDATE $tableName SET reset_token = ?, reset_token_created_at = NOW() WHERE id = ?");
                $updateTokenStmt->execute([$token, $user['id']]);

                // Send an email to the user with a link to reset their password
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'mizuwara333@gmail.com';
                $mail->Password = 'fsyw nwcg rioz hdas';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('mizuwara333@gmail.com', 'ArtConnect');
                $mail->addAddress($email, $user['fullname']);
                $mail->Subject = 'Reset password';
                $mail->Body = "Click the link to reset password http://localhost/ArtConnect/resetpassword.php?token=$token&type=$userType";

                $mail->send();
                $success = 'Check your email.A reset password link has been sent';
            } else {
                $msg = "Email not found. Please enter a valid email address.";
            }
        } catch (PDOException $e) {
            $msg = 'Error: ' . $e->getMessage();
        }
    } else {
        $msg = "Invalid email address";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forgot Password</title>
    <style>
        body {
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            background-color: #f0f0f0;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 8px 8px 16px #d3d3d3, -8px -8px 16px #ffffff;
            text-align: center;
        }

        h2 {
            color: #333;
        }

        label {
            display: block;
            margin-top: 10px;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: none;
            border-radius: 8px;
            background-color: #f0f0f0;
            box-shadow: inset 4px 4px 8px #d3d3d3, inset -4px -4px 8px #ffffff;
            color: #333;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: none;
            border-radius: 8px;
            background-color: grey;
            color: #fff;
            cursor: pointer;
            box-shadow: 4px 4px 8px #d3d3d3, -4px -4px 8px #ffffff;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: black;
        }

        p.success {
            color: lightgreen;
        }

        p.fail {
            color: red;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="message">
            <p class="success">
                <?php echo $success ?>
            </p>
            <p class="fail">
                <?php echo $msg ?>
            </p>
        </div>
        <div class="container">
            <h2>Forgot Password</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                <br>
                <label for="userType">User Type:</label>
                <select name="userType" id="userType">
                    <option value="buyer">Buyer</option>
                    <option value="artist">Artist</option>
                </select>
                <button type="submit" name="reset-request-submit">Reset Password</button>
            </form>
        </div>
    </div>

</body>

</html>