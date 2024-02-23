<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: /ArtConnect/admin');
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify'])) {
        // Handle verification
        handleVerification($pdo, 1);
    } elseif (isset($_POST['donotverify'])) {
        // Handle not verifying
        handleVerification($pdo, 0);
    }
}

function handleVerification($pdo, $status)
{
    if (isset($_POST['verify']) || isset($_POST['donotverify'])) {
        // Ensure either "Verify" or "Do Not Verify" button is clicked
        $phone = $_POST['phone']; // Assuming phone is a unique identifier
        try {
            $stmt = $pdo->prepare('UPDATE artist SET verified = ? WHERE phone = ?');
            $stmt->execute([$status, $phone]);
        } catch (PDOException $e) {
            echo 'Error updating artist verification status: ' . $e->getMessage();
        }
    }
}

// Fetch data from verification_check table only for artists with a verification status of 2
$stmt = $pdo->prepare('SELECT * FROM verification_check vc
                      JOIN artist a ON vc.phone = a.phone
                      WHERE a.verified = 2');
$stmt->execute();
$verificationData = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        button.btn.btn-success.btn-sm {
            margin: 1rem;
        }

        .bread-crum {
            height: 5rem;
        }

        img.back {
            margin: 3rem;
        }
    </style>
</head>

<body>
    <div class="bread-crum">
        <a href="/ArtConnect/admin/dashboard/dashboard.php"><img src="./back-button.png" alt="back button"
                class="back"></a>
    </div>

    <div class="container mt-5">
        <h2>Verification Dashboard</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Citizenship Front</th>
                        <th>Citizenship Back</th>
                        <th>Current Photo</th>
                        <th>CV</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($verificationData as $data): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($data['name']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['email']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['phone']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['address']); ?>
                            </td>
                            <td><a href="/ArtConnect/verifyartist/uploads/<?php echo htmlspecialchars($data['citizenship_front']); ?>"
                                    target="_blank"><img
                                        src="/ArtConnect/verifyartist/uploads/<?php echo htmlspecialchars($data['citizenship_front']); ?>"
                                        alt="Citizenship Front" class="img-thumbnail"></a></td>
                            <td><a href="/ArtConnect/verifyartist/uploads/<?php echo htmlspecialchars($data['citizenship_back']); ?>"
                                    target="_blank"><img
                                        src="/ArtConnect/verifyartist/uploads/<?php echo htmlspecialchars($data['citizenship_back']); ?>"
                                        alt="Citizenship Back" class="img-thumbnail"></td>
                            <td><a href="/ArtConnect/verifyartist/uploads/<?php echo htmlspecialchars($data['current_photo']); ?>"
                                    target="_blank"><img
                                        src="/ArtConnect/verifyartist/uploads/<?php echo htmlspecialchars($data['current_photo']); ?>"
                                        alt="Current Photo" class="img-thumbnail"></a></td>
                            <td><a href="/ArtConnect/verifyartist/uploads/<?php echo htmlspecialchars($data['cv']); ?>"
                                    target="_blank">View CV</a></td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="phone"
                                        value="<?php echo htmlspecialchars($data['phone']); ?>">
                                    <button type="submit" name="verify" class="btn btn-success btn-sm">Verify</button>
                                </form>

                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="phone"
                                        value="<?php echo htmlspecialchars($data['phone']); ?>">
                                    <button type="submit" name="donotverify" class="btn btn-danger btn-sm">Do Not
                                        Verify</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>