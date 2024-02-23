<?php
session_start(); // Start the session

// Redirect to the login page if not logged in or not an admin
if (!isset($_SESSION['username'])) {
    header('Location: /ArtConnect/admin');
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$artmsg = "";
$artmsgsuccess = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $artIdToDelete = $_POST['artId'];
    global $artmsg;
    global $artmsgsuccess;

    // Fetch art details from the 'art' table
    $stmtFetchArt = $pdo->prepare('SELECT artImage FROM art WHERE artId = ?');
    $stmtFetchArt->execute([$artIdToDelete]);
    $artToDelete = $stmtFetchArt->fetch(PDO::FETCH_ASSOC);

    if ($artToDelete) {
        // Delete image file from the 'arts' folder
        $targetDirectory = __DIR__ . "/../../arts/";
        $fileName = $artToDelete['artImage'];
        $targetFileToDelete = $targetDirectory . $fileName;

        //Delete File
        if (file_exists($targetFileToDelete)) {
            unlink($targetFileToDelete);
        }


        // Delete art bidding from the 'bids' table
        $stmtDeleteBids = $pdo->prepare('DELETE FROM bids WHERE artId = ?');
        $stmtDeleteBids->execute([$artIdToDelete]);

        // Delete the art row from the 'art' table
        $stmtDeleteArt = $pdo->prepare('DELETE FROM art WHERE artId = ?');
        $stmtDeleteArt->execute([$artIdToDelete]);

        $artmsgsuccess = "Art has been deleted successfully!";
    } else {
        $artmsg = "Art not found for deletion.";
    }
}

$stmtArtsWithBids = $pdo->prepare('
    SELECT a.*, b.sold
    FROM art a
    LEFT JOIN (
        SELECT artId, MAX(bidAmount) AS highestBid, MAX(sold) AS sold
        FROM bids
        GROUP BY artId
    ) b ON a.artId = b.artId
    ORDER BY a.artId
');

$stmtArtsWithBids->execute();
$artsWithBids = $stmtArtsWithBids->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Arts</title>
    <link rel="stylesheet" href="/ArtConnect/assets/css/dashboard.css">
    <style>
        button.btn.btn-success.btn-sm,
        button.btn.btn-danger.btn-sm,
        button.btn.btn-primary.btn-sm {
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
    <div class="msg">
        <p class="success">
            <?php echo htmlspecialchars($artmsgsuccess) ?>
        </p>
        <p class="fail">
            <?php echo htmlspecialchars($artmsg) ?>
        </p>
    </div>
    <div class="bread-crum">
        <a href="/ArtConnect/admin/dashboard/dashboard.php"><img src="./back-button.png" alt="back button"
                class="back"></a>
    </div>
    <div class="ad">
        <h1 class="ad">Admin - Manage Arts</h1>
    </div>

    <div class="arts-with-bids">
        <div class="ya">
            <h2 class="ya">Arts List</h2>
        </div>

        <div class="artgallery">
            <?php foreach ($artsWithBids as $art): ?>
                <div class="art-item">
                    <div class="artimg">
                        <img src="/ArtConnect/arts/<?php echo htmlspecialchars($art['artImage']) ?>"
                            alt="<?php echo htmlspecialchars($art['artName']) ?>" class="artimage">
                    </div>
                    <h3>
                        <?php echo htmlspecialchars($art['artName']); ?>
                    </h3>
                    <p>Art Type:
                        <?php echo htmlspecialchars($art['artType']); ?>
                    </p>
                    <p>Art Price:
                        <?php echo htmlspecialchars($art['artPrice']) ?>
                    </p>

                    <?php if ($art['sold'] == 1): ?>
                        <p>Status: Sold</p>
                    <?php else: ?>
                        <p>Status: Not Sold</p>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="artId" value="<?php echo htmlspecialchars($art['artId']); ?>">
                        <div class="buttons">
                            <button type="submit" name="delete" class="delete">Delete Art</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>