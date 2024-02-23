<?php
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(error_reporting() & ~E_WARNING);

$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

session_start();
// Fetch artworks where sold = 1 and buyerId = session buyer_id
$buyerId = $_SESSION['buyer_id'];
$stmt = $pdo->prepare('SELECT a.artId ,a.artImage, a.artName, a.artType, a.artistName, a.artistEmail, b.bidAmount
                            FROM art a
                            JOIN bids b ON a.artId = b.artId
                            WHERE b.sold = 1 AND b.bidderId = ?');
$stmt->execute([$buyerId]);
$artworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cart</title>
    <link rel="stylesheet" href="/ArtConnect/assets/css/cart.css">
</head>

<body>
    <?php include 'includes/navbar/navbar.php'; ?>
    <div class="page-wrap">


        <?php if (!isset($_SESSION['buyer_id'])): ?>
            <div class="err">
                <div class="err-msg">
                    <p>Please login as buyer or refresh the page if you have logged in</p>
                </div>
            </div>
        <?php else: ?>
            <div class="my-cart">
                <h2>My Cart</h2>
            </div>
            <div class="gallery">
                <?php foreach ($artworks as $artwork): ?>
                    <a href="/ArtConnect/checkout.php?artId=<?php echo htmlspecialchars($artwork['artId']) ?>">
                        <div class="art-piece">
                            <div class="artimg">
                                <img src="/ArtConnect/arts/<?php echo htmlspecialchars($artwork['artImage']); ?>"
                                    alt="<?php echo htmlspecialchars($artwork['artName']); ?>" class="artimage">
                            </div>
                            <h3>
                                <?php echo htmlspecialchars($artwork['artName']); ?>
                            </h3>
                            <p>Type:
                                <?php echo htmlspecialchars($artwork['artType']); ?>
                            </p>
                            <p>Artist:
                                <?php echo htmlspecialchars($artwork['artistName']); ?>
                            </p>
                            <p>Email:
                                <?php echo htmlspecialchars($artwork['artistEmail']); ?>
                            </p>
                            <p>Your bid: Rs.
                                <?php echo htmlspecialchars($artwork['bidAmount']); ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php include 'includes/footer/footer.php'; ?>
    </div>
</body>

</html>