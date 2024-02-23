<?php
session_start();

if (!isset($_SESSION['buyer_id'])) {
    header('Location: /ArtConnect/index.php?error=unauthorized');
    exit();
}
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: /ArtConnect/index.php');
    exit();
}
$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$buyerId = $_SESSION['buyer_id'];
$stmtBuyerInfo = $pdo->prepare('SELECT * FROM buyer WHERE id = ?');
$stmtBuyerInfo->execute([$buyerId]);
$buyerInfo = $stmtBuyerInfo->fetch(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_fullname'])) {
    $newFullname = $_POST['new_fullname'];

    $stmtUpdateFullname = $pdo->prepare('UPDATE buyer SET fullname = ? WHERE id = ?');
    $stmtUpdateFullname->execute([$newFullname, $buyerId]);


    $_SESSION['fullname'] = $newFullname;


    header('Location: /ArtConnect/profile.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_email'])) {
    $newEmail = $_POST['new_email'];


    $stmtCheckEmail = $pdo->prepare('SELECT id FROM buyer WHERE email = ? AND id != ?');
    $stmtCheckEmail->execute([$newEmail, $buyerId]);
    $existingEmail = $stmtCheckEmail->fetch(PDO::FETCH_ASSOC);

    if (!$existingEmail) {

        $stmtUpdateEmail = $pdo->prepare('UPDATE buyer SET email = ? WHERE id = ?');
        $stmtUpdateEmail->execute([$newEmail, $buyerId]);


        $_SESSION['email'] = $newEmail;


        header('Location: /ArtConnect/profile.php');
        exit();
    } else {
        $emailError = 'Email is already registered by another user.';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_phone'])) {
    $newPhone = $_POST['new_phone'];


    $stmtCheckPhone = $pdo->prepare('SELECT id FROM buyer WHERE phone = ? AND id != ?');
    $stmtCheckPhone->execute([$newPhone, $buyerId]);
    $existingPhone = $stmtCheckPhone->fetch(PDO::FETCH_ASSOC);

    if (!$existingPhone) {

        $stmtUpdatePhone = $pdo->prepare('UPDATE buyer SET phone = ? WHERE id = ?');
        $stmtUpdatePhone->execute([$newPhone, $buyerId]);


        $_SESSION['phone'] = $newPhone;


        header('Location: /ArtConnect/profile.php');
        exit();
    } else {
        $phoneError = 'Phone number is already registered by another user.';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];


    $stmtCurrentPassword = $pdo->prepare('SELECT password FROM buyer WHERE id = ?');
    $stmtCurrentPassword->execute([$buyerId]);
    $currentPasswordHash = $stmtCurrentPassword->fetchColumn();


    if (password_verify($currentPassword, $currentPasswordHash)) {

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmtUpdatePassword = $pdo->prepare('UPDATE buyer SET password = ? WHERE id = ?');
        $stmtUpdatePassword->execute([$newPasswordHash, $buyerId]);


        header('Location: /ArtConnect/profile.php');
        exit();
    } else {
        $passwordError = 'Current password is incorrect.';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {

    $stmtDeleteAccount = $pdo->prepare('DELETE FROM buyer WHERE id = ?');
    $stmtDeleteAccount->execute([$buyerId]);


    session_destroy();


    header('Location: /ArtConnect/index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h1,
        p,
        label,
        input,
        button {
            color: #333;
        }

        form {
            margin-bottom: 20px;
        }

        input {
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
            padding: 12px;
            margin: 1rem;
            margin-left: 0;
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
    </style>

</head>

<body>
    <div class="warpper">
        <h1>Welcome,
            <?php echo htmlspecialchars($buyerInfo['fullname']); ?>!
        </h1>


        <p>Email:
            <?php echo htmlspecialchars($buyerInfo['email']); ?>
        </p>
        <p>Phone:
            <?php echo htmlspecialchars($buyerInfo['phone']); ?>
        </p>


        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="new_fullname">Change Fullname:</label>
            <input type="text" id="new_fullname" name="new_fullname" required>
            <button type="submit" name="change_fullname">Save</button>
        </form>


        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="new_email">Change Email:</label>
            <input type="email" id="new_email" name="new_email" required>
            <?php if (isset($emailError)): ?>
                <p style="color: red;">
                    <?php echo $emailError; ?>
                </p>
            <?php endif; ?>
            <button type="submit" name="change_email">Save</button>
        </form>


        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="new_phone">Change Phone:</label>
            <input type="tel" id="new_phone" name="new_phone" required>
            <?php if (isset($phoneError)): ?>
                <p style="color: red;">
                    <?php echo $phoneError; ?>
                </p>
            <?php endif; ?>
            <button type="submit" name="change_phone">Save</button>
        </form>


        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            <?php if (isset($passwordError)): ?>
                <p style="color: red;">
                    <?php echo $passwordError; ?>
                </p>
            <?php endif; ?>
            <button type="submit" name="change_password">Change Password</button>
        </form>


        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
            onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
            <button type="submit" name="delete_account" style="color: red;">Delete Account</button>
        </form>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <button type="submit" name="logout">Logout</button>
        </form>

    </div>
</body>

</html>