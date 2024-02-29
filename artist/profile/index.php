<?php
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(error_reporting() & ~E_WARNING);
session_start();
// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // echo 'Error connecting to the database: ' . $e->getMessage();
    die();
}
if (!isset($_SESSION['buyer_id'])) {
    $loginMessage = 'Please log in to write a review.';
} else {
    $loginMessage = '';
}
// Get artist ID from the GET method
if (isset($_GET['id'])) {
    $artistId = $_GET['id'];


    // Retrieve artist information
    $stmtArtist = $pdo->prepare('SELECT fullname, email, phone, bio, rating FROM artist WHERE id = ?');
    $stmtArtist->execute([$artistId]);
    $artistInfo = $stmtArtist->fetch(PDO::FETCH_ASSOC);

    // Retrieve current_photo from verification_check
    $stmtVerification = $pdo->prepare('SELECT current_photo FROM verification_check WHERE phone = (SELECT phone FROM artist WHERE id = ?)');
    $stmtVerification->execute([$artistId]);
    $verificationInfo = $stmtVerification->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment']) && isset($_SESSION['buyer_id'])) {
    // Assuming you have a session variable for buyer_id
    $buyerId = $_SESSION['buyer_id'];
    $artistId = $_GET['id'];
    $commentText = $_POST['comment_text'];

    // Retrieve buyer's name from the database
    $stmtBuyerName = $pdo->prepare('SELECT fullname FROM buyer WHERE id = ?');
    $stmtBuyerName->execute([$buyerId]);
    $buyerNameResult = $stmtBuyerName->fetch(PDO::FETCH_ASSOC);

    if ($buyerNameResult) {
        $buyerName = $buyerNameResult['fullname'];
        echo $buyerName;

        // Insert the comment into the database
        $stmtInsertComment = $pdo->prepare('INSERT INTO comments (artist_id, buyer_id, buyer_name, comment_text) VALUES (?, ?, ?, ?)');
        $stmtInsertComment->execute([$artistId, $buyerId, $buyerName, $commentText]);

        // Redirect to the same page to prevent form resubmission
        header("Location: {$_SERVER['PHP_SELF']}?id={$artistId}");
        exit();
    }
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    // Assuming you have a comment_id in the form to identify the comment to delete
    $commentIdToDelete = $_POST['comment_id'];

    // Perform the deletion from the database
    $stmtDeleteComment = $pdo->prepare('DELETE FROM comments WHERE id = ?');
    $stmtDeleteComment->execute([$commentIdToDelete]);

    // Redirect to the same page after deletion
    header("Location: {$_SERVER['PHP_SELF']}?id={$artistId}");
    exit();
}
$stmtComments = $pdo->prepare('SELECT * FROM comments WHERE artist_id = ?');
$stmtComments->execute([$artistId]);
$comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    // Assuming you have a session variable for buyer_id
    $buyerId = $_SESSION['buyer_id'];
    $artistId = $_GET['id'];
    $userRating = $_POST['rating'];

    // Check if the user has already rated
    $stmtCheckRated = $pdo->prepare('SELECT * FROM rated WHERE artist_id = ? AND buyer_id = ?');
    $stmtCheckRated->execute([$artistId, $buyerId]);

    if ($stmtCheckRated->rowCount() === 0) {
        // User hasn't rated yet, get the current artist rating
        $stmtGetArtistRating = $pdo->prepare('SELECT rating FROM artist WHERE id = ?');
        $stmtGetArtistRating->execute([$artistId]);
        $currentRating = $stmtGetArtistRating->fetch(PDO::FETCH_ASSOC)['rating'];

        // Calculate the new average rating
        $newRating = ($currentRating + $userRating) / 2;

        // Update the artist table with the new average rating
        $stmtUpdateArtistRating = $pdo->prepare('UPDATE artist SET rating = ? WHERE id = ?');
        $stmtUpdateArtistRating->execute([$newRating, $artistId]);

        // Mark the user as rated in the 'rated' table
        $stmtMarkRated = $pdo->prepare('INSERT INTO rated (artist_id, buyer_id) VALUES (?, ?)');
        $stmtMarkRated->execute([$artistId, $buyerId]);

        // Redirect to the same page to prevent form resubmission
        header("Location: {$_SERVER['PHP_SELF']}?id={$artistId}");
        exit();
    } else {
        echo "";
    }
}
$buyerId = $_SESSION['buyer_id'];
$CheckRated = $pdo->prepare('SELECT * FROM rated WHERE artist_id = ? AND buyer_id = ?');
$CheckRated->execute([$artistId, $buyerId]);


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($artistInfo['fullname']); ?>
    </title>
    <link rel="stylesheet" href="/ArtConnect/assets/css/ad.css">
</head>

<body>
    <?php include 'includes/navbar/navbar.php'; ?>
    <div class="wrap">
        <?php if (isset($artistInfo) && isset($verificationInfo)): ?>
            <div class="img-w">
                <div class="img">
                    <img src="/ArtConnect/verifyartist/uploads/<?php echo htmlspecialchars($verificationInfo['current_photo']); ?>"
                        alt="<?php echo htmlspecialchars($verificationInfo['current_photo']); ?>" class="aimg">
                </div>
            </div>

            <div class="detail-a">
                <div class="nam">

                    <h2>
                        <?php echo htmlspecialchars($artistInfo['fullname']); ?>
                    </h2>
                </div>
                <div class="nam">
                    <h4>Email:
                        <?php echo htmlspecialchars($artistInfo['email']); ?>
                    </h4>
                </div>
                <div class="nam">
                    <h4>Phone:
                        <?php echo htmlspecialchars($artistInfo['phone']); ?>
                    </h4>
                </div>

                <div class="bio">
                    <p class="bio">
                        <?php echo htmlspecialchars($artistInfo['bio']); ?>
                    </p>
                </div>

            </div>

        <?php else: ?>
            <p>Artist not found or data not available.</p>
        <?php endif; ?>
        <div class="a-rating">

            <div class="rating-w">
                <div class="rating">
                    <p>Artist Rating:</p>
                    <?php if ($artistInfo['rating'] == 0): ?>
                        <p>
                            No one has given ratings yet
                        </p>
                    <?php else: ?>
                        <p>
                            <?php echo $artistInfo['rating']; ?>/5
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (!isset($_SESSION['buyer_id'])): ?>
                    <div class="err">
                        <p></p>
                    </div>
                    <? echo $CheckRated; ?>
                <?php elseif ($CheckRated->rowCount() === 1): ?>
                    <div class="err">
                        <p></p>
                    </div>
                <?php elseif ($CheckRated->rowCount() === 0): ?>
                    <div class="form">
                        <form method='post' action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $artistId; ?>"
                            id='ratingForm'>
                            <div class='rat'>
                                Rate the Artist
                            </div>
                            <div class='rating-stars'>
                                <div class='rating'>
                                    <input type='radio' id='star5' name='rating' value='5'>
                                    <label for='star5'></label>
                                    <input type='radio' id='star4' name='rating' value='4'>
                                    <label for='star4'></label>
                                    <input type='radio' id='star3' name='rating' value='3'>
                                    <label for='star3'></label>
                                    <input type='radio' id='star2' name='rating' value='2'>
                                    <label for='star2'></label>
                                    <input type='radio' id='star1' name='rating' value='1'>
                                    <label for='star1'></label>
                                </div>
                            </div>
                            <div class="bt">
                                <button type='submit' name='submit_rating'>Submit Rating</button>
                            </div>

                        </form>
                    </div>

                <?php endif; ?>





            </div>
        </div>
        <div class="review">
            <div class="r-t">
                <h3>Reviews</h3>
            </div>
            <div class="r-w">
                <div class="comments-section">
                    <?php
                    // Check if $comments is not null before looping through it
                    if ($comments !== null && !empty($comments)) {
                        // Store Review in variables for easier display
                        $commentsHtml = '';
                        foreach ($comments as $comment) {
                            $commentsHtml .= "<div class='comment'>";
                            $commentsHtml .= "<p>{$comment['buyer_name']} says:<span class='rev'> {$comment['comment_text']}</span></p>";

                            // Check if the logged-in buyer is the author of the Review
                            if (isset($_SESSION['buyer_id']) && $_SESSION['buyer_id'] == $comment['buyer_id']) {
                                $commentsHtml .= "<form method='post' action='{$_SERVER['PHP_SELF']}?id={$artistId}'>";
                                $commentsHtml .= "<input type='hidden' name='comment_id' value='{$comment['id']}'>";
                                $commentsHtml .= "<button type='submit' name='delete_comment' class='dc'><img src='./bin.png' class='bin' alt='bin'></button></form>";
                            }

                            $commentsHtml .= "</div>";
                        }

                        // Display Review
                        echo $commentsHtml;
                    } else {
                        echo '<p>No reviews yet.</p>';
                    }
                    ?>
                </div>


                <!-- Review Form -->
                <?php if (isset($_SESSION['buyer_id'])): ?>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $artistId; ?>">
                        <textarea name="comment_text" rows="4" cols="50" placeholder="Write a review..."></textarea>
                        <button type="submit" name="submit_comment">Submit Review</button>
                    </form>
                <?php else: ?>
                    <div class="login-message">
                        <?php echo $loginMessage; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php include 'includes/footer/footer.php'; ?>
</body>

</html>