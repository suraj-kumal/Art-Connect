<?php
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(error_reporting() & ~E_WARNING);
$msg = "";
$success = "";
$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$buyerId = null;
if (isset($_GET['id'])) {
    session_start();
    $artID = $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM art WHERE artId = ?');
    $stmt->execute([$artID]);
    $artDetail = $stmt->fetch(PDO::FETCH_ASSOC);


    $stmtCheckSold = $pdo->prepare('SELECT COUNT(*) AS soldCount FROM bids WHERE artId = ? AND sold = 1');
    $stmtCheckSold->execute([$artID]);
    $soldCount = $stmtCheckSold->fetchColumn();



    if (isset($_SESSION['buyer_id'])) {
        $buyerId = $_SESSION['buyer_id'];
        $stmtBuyer = $pdo->prepare('SELECT * FROM buyer WHERE id = ?');
        $stmtBuyer->execute([$buyerId]);
        $buyerInfo = $stmtBuyer->fetch(PDO::FETCH_ASSOC);

        $bidderId = $buyerInfo['id'];
        $bidderName = $buyerInfo['fullname'];
        $bidderEmail = $buyerInfo['email'];
        $bidderPhone = $buyerInfo['phone'];
    } else {
        $msg = "Please login as buyer or refresh the page if you have logged in";
    }
}
if ($artDetail['sellType'] == 1) {
    // If sellType is 1, display "Add to Cart" form
    if (isset($_POST['addToCart'])) {
        if (!isset($_SESSION['buyer_id'])) {
            $msg = "Please login as buyer or refresh the page if you have logged in";
        } else {
            // Insert the art into the cart with artPrice and set sold = 1
            $stmt = $pdo->prepare('INSERT INTO bids (artId, artName, artistName, artistEmail, bidderId, bidderName, bidderEmail, bidderPhone, bidAmount, sold) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $artID,
                $artDetail['artName'],
                $artDetail['artistName'],
                $artDetail['artistEmail'],
                $buyerId,
                $bidderName,
                $bidderEmail,
                $bidderPhone,
                $artDetail['artPrice'],
                1  // Set sold = 1
            ]);
            $success = "Art added to cart successfully!";
        }
    }
} else {
    if (isset($_POST['bid'])) {
        $bidAmount = $_POST['bid'];
        if (!isset($_SESSION['buyer_id'])) {
            $msg = "Please login as buyer or refresh the page if you have logged in";
        } else {
            if ($bidAmount <= 0) {
                $msg = "Invalid bid amount!";
            } elseif ($bidAmount < $artDetail['artPrice']) {
                $msg = "Bid amount must be greater than the opening bid amount!";
            } else {
                $stmt = $pdo->prepare('SELECT MAX(bidAmount) AS maxBid FROM bids WHERE artId = ?');
                $stmt->execute([$artID]);
                $maxBid = $stmt->fetchColumn();

                if ($maxBid !== null && $bidAmount <= $maxBid) {
                    $msg = "Please bid a higher amount than the current highest bid of $maxBid";
                } else {
                    $stmt = $pdo->prepare('INSERT INTO bids (artId, artName, artistName, artistEmail, bidderId, bidderName, bidderEmail, bidderPhone, bidAmount, sold) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $artID,
                        $artDetail['artName'],
                        $artDetail['artistName'],
                        $artDetail['artistEmail'],
                        $bidderId,
                        $bidderName,
                        $bidderEmail,
                        $bidderPhone,
                        $bidAmount,
                        0
                    ]);
                    $success = "Bid placed successfully!";
                }
            }
        }
    }
}
if ($soldCount > 0) {
    // Check if the current buyer won the bid
    $stmtCheckWinner = $pdo->prepare('SELECT COUNT(*) AS winnerCount FROM bids WHERE artId = ? AND bidderId = ? AND sold = 1');
    $stmtCheckWinner->execute([$artID, $buyerId]);
    $winnerCount = $stmtCheckWinner->fetchColumn();
}
$stmtHighestBid = $pdo->prepare('SELECT MAX(bidAmount) AS highestBid FROM bids WHERE artId = ?');
$stmtHighestBid->execute([$artID]);
$highestBid = $stmtHighestBid->fetch(PDO::FETCH_ASSOC);

$stmtBidderHighestBid = $pdo->prepare('SELECT MAX(bidAmount) AS bidderHighestBid FROM bids WHERE artId = ? AND bidderId = ?');
$stmtBidderHighestBid->execute([$artID, $bidderId]);
$bidderHighestBid = $stmtBidderHighestBid->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<head>
    <title>
        <?php echo htmlspecialchars($artDetail['artName']); ?>
    </title>
    <link rel="stylesheet" href="assets/css/detail.css">
</head>

<body>
    <?php include 'includes/navbar/navbar.php'; ?>
    <div class="art-main">
        <div class="artimg">
            <a href="/ArtConnect/arts/<?php echo htmlspecialchars($artDetail['artImage']); ?>" target="_blank"><img
                    src="/ArtConnect/arts/<?php echo htmlspecialchars($artDetail['artImage']); ?>"
                    alt="<?php echo htmlspecialchars($artDetail['artName']); ?>" class="artimage"></a>
        </div>
        <div class="artdetail">
            <h2 class="artname">
                <?php echo htmlspecialchars($artDetail['artName']); ?>
            </h2>
            <p class="arttype">
                <?php echo htmlspecialchars($artDetail['artType']); ?>
            </p>
            <div class="des">
                Description:<p class="artDescription">
                    <?php echo htmlspecialchars($artDetail['artDescription']); ?>
                </p>
            </div>

            <p class="frameSize">Frame Size: <span class="framesize">
                    <?php echo htmlspecialchars($artDetail['frameSize']); ?>
                </span></p>

            <p class="artArtist">Artist:
                <?php echo htmlspecialchars($artDetail['artistName']); ?>
            </p>
            <p class="artArtist">Email:
                <?php echo htmlspecialchars($artDetail['artistEmail']); ?>
            </p>
        </div>
    </div>


    <?php if ($soldCount > 0 && $winnerCount > 0): ?>
        <?php if ($artDetail['sellType'] == 2): ?>
            <div class="win">
                <p class="sold-msg">Congratulations! You won the bid. <br>
                    This art is added to your cart.<br>
                    Visit your cart for further process
                </p>
            </div>
        <?php elseif ($artDetail['sellType'] == 1): ?>
            <div class="win">
                <p class="sold-msg">
                    This art is added to your cart.<br>
                    Visit your cart for further process
                </p>
            </div>
        <?php endif; ?>

    <?php elseif ($soldCount > 0): ?>
        <div class="sold">
            <div class="sold-i">
                SOLD OUT !
            </div>
        </div>
    <?php else: ?>
        <?php if ($artDetail['sellType'] == 1): ?>
            <div class="pr">
                <h3 class="pr">Price :
                    <?php echo htmlspecialchars($artDetail['artPrice']); ?>
                </h3>
            </div>

            <div class="containercart">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $artID); ?>">
                    <button type="submit" name="addToCart">Add to Cart</button>
                </form>
            </div>
        <?php else: ?>
            <div class="containerbid">
                <div class="wrapbid">
                    <div class="bid">
                        <p class="artPrice">Opening bid: Rs.
                            <?php echo htmlspecialchars($artDetail['artPrice']); ?>
                        </p>
                        <?php if ($highestBid['highestBid'] !== null): ?>
                            <p class="currentBid">Current bid: Rs.
                                <?php echo htmlspecialchars($highestBid['highestBid']) ?>
                            </p>
                        <?php else: ?>
                            <p class="currentBid">Be the first one to bid!</p>
                        <?php endif; ?>

                        <?php if ($bidderHighestBid['bidderHighestBid'] !== null): ?>
                            <p class="your_bid">Your bid: Rs.
                                <?php echo htmlspecialchars($bidderHighestBid['bidderHighestBid']) ?>
                            </p>
                        <?php else: ?>
                            <p class="your_bid">Your bid: Rs. 0</p>
                        <?php endif; ?>

                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $artID); ?>">
                            <div class="input-container">
                                <input type="number" step="0.01" id="bid" name="bid" required autocomplete="off">
                                <label for="input" class="label">Place your bid</label>
                                <div class="underline"></div>
                            </div>
                            <button type="submit">bid</button>
                        </form>

                    </div>

                </div>
            </div>
        <?php endif; ?>
        <div class="err">
            <div class="err-msg">
                <p class="p-c">
                    <?php echo htmlspecialchars($msg) ?>
                </p>
                <p class="success">
                    <?php echo htmlspecialchars($success) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
    <?php include 'includes/footer/footer.php'; ?>
</body>

</html>