<?php
session_start(); // Start the session

$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buyer'])) {
    handleBuyerLogin($pdo);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['artist'])) {
    handleArtistLogin($pdo);
}

function handleArtistLogin($pdo)
{
    global $msg;
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, email, password FROM artist WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            unset($_SESSION['buyer_id']);
            unset($_SESSION['buyer_email']);
            // Password is correct, start the session and store artist information
            $_SESSION['artist_id'] = $user['id'];
            $_SESSION['artist_email'] = $user['email'];

            // Redirect to artist dashboard
            header('Location: /ArtConnect/artist/dashboard/index.php');
            exit();
        } else {
            // Invalid credentials, you can handle this according to your requirements
            $msg = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $msg = 'Error: ' . $e->getMessage();
    }
}

function handleBuyerLogin($pdo)
{
    global $msg;
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, email, password FROM buyer WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, start the session and store buyer information
            unset($_SESSION['artist_id']);
            unset($_SESSION['artist_email']);
            $_SESSION['buyer_id'] = $user['id'];
            $_SESSION['buyer_email'] = $user['email'];

            // Redirect to the last visited page or index.php if not set
            // $lastVisitedPage = isset($_SESSION['last_visited_page']) ? $_SESSION['last_visited_page'] : '/ArtConnect/index.php';
            // header("Location: $lastVisitedPage");
            header("Location:index.php");
            exit();
        } else {
            // Invalid credentials, you can handle this according to your requirements
            $msg = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $msg = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <div class="warp">
        <div class="msg">
            <p class="fail">
                <?php echo $msg; ?>
            </p>
        </div>
        <div class="container">
            <h2>Login</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
                <br>

                <input type="hidden" name="user_type" id="user_type" value="artist">

                <button type="submit" name="artist">Login as Artist</button>
                <button type="submit" name="buyer">Login as Buyer</button>
                <div class="fp">
                    <a href="/ArtConnect/forgotpassword.php" class="f">forgotpassword</a>
                    <div class="home">
                        <a href="/ArtConnect" class="h">Go to Home Page</a>
                    </div>

                </div>

            </form>
        </div>
        <div class="sign-up">
            Don't have an account? Create one <a href="whoareyou.html">signup</a>
        </div>
    </div>

</body>

</html>