<?php

$success = "";
$fail = " ";
// Assuming you have a database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "artconnect";

$conn = new mysqli($servername, $username, $password, $dbname);

$result = null; // Initialize $result variable

if (isset($_GET['token']) && isset($_GET['type'])) {
    $token = $_GET['token'];
    $userType = $_GET['type'];

    // Validate $userType to prevent SQL injection
    $userType = ($userType == 'artist' || $userType == 'buyer') ? $userType : '';

    // Fetch user information from the appropriate table based on $userType and $token
    $tableName = ($userType == 'artist') ? 'artist' : 'buyer';
    $sql = "SELECT * FROM $tableName WHERE reset_token = '$token'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (isset($_POST['reset'])) {
            $new_password = $_POST['new_password'];
            $hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Assuming you have user information from the query result
            $userId = $row['id'];
            $userEmail = $row['email'];

            // Update the password in the appropriate table
            $updateSql = "UPDATE $tableName SET password = '$hash', reset_token = NULL WHERE id = $userId AND email = '$userEmail'";
            $updateResult = $conn->query($updateSql);

            if ($updateResult) {
                $success = "Password updated successfully. You can now <a href='login.php' class='login'>login</a> with your new password.";
            } else {
                $fail = "Error updating password: " . $conn->error;
            }
        }
    } else {
        $fail = "";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/rp.css">
</head>
<body>
    <div class="wrap">
        <div class="msg">
            <p class="success"><?php echo $success; ?></p>
            <p class="fail"><?php echo $fail; ?></p>
        </div>
    <?php if ($result && $result->num_rows > 0) : ?>
        <div class="ryp">
        <h2>Reset Your Password</h2>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?token=<?php echo $token; ?>&type=<?php echo $userType; ?>" method="post">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>
            <button type="submit" name="reset">Reset Password</button>
        </form>
    <?php else :?>
        <h2 style="color:red;">Invalid token or user type. Please try again.</h2>
    <?php endif; ?>
    </div>
   
</body>
</html>
