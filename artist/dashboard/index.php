<?php
session_start(); // Start the session
$artmsg = "";
$artmsgsuccess = "";
// Redirect to the login page if not logged in
if (!isset($_SESSION['artist_email'])) {
    header('Location: /ArtConnect/login.php?error=unauthorized');
    exit();
}
//logout 
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: /ArtConnect/login.php');
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check artist status
$artistStatus = getArtistStatus($pdo, $_SESSION['artist_email']);

// Handle form submission for posting art
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postArt'])) {
    if ($artistStatus == 1) {
        // Artist is verified or under verification, proceed with posting art
        if (isset($_POST['artName'], $_POST['artType'], $_POST['artDescription'], $_POST['frameSize'], $_POST['artPrice'], $_FILES['artImage'])) {
            postArt($pdo);
        } else {
            $artmsg = "Please fill out all required fields.";
        }
    } else {
        // Artist is not verified, display a message or take appropriate action
        $artmsg = "You are not verified or under verification. Cannot post art.";
    }
}

// Function to get artist status
function getArtistStatus($pdo, $email)
{
    try {
        $stmt = $pdo->prepare('SELECT verified FROM artist WHERE email = ?');
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['verified'];
        } else {
            return -1; // Return -1 for invalid or non-existent email
        }
    } catch (PDOException $e) {
        $artmsg = 'Error: ' . $e->getMessage();
        return -1;
    }
}

// Function to post art
function postArt($pdo)
{
    global $artmsg, $artmsgsuccess;
    // Retrieve form data
    $artName = $_POST['artName'];
    $artType = $_POST['artType'];
    $artDescription = $_POST['artDescription'];
    $frameSize = $_POST['frameSize'];
    $artPrice = $_POST['artPrice'];
    $sellingType = $_POST['sellingType'];

    function getSellTypeValue($sellingType)
    {
        // Assuming 'normal' is 1 and 'auction' is 2
        return ($sellingType === 'auction') ? 2 : 1;
    }

    // Retrieve artist information from session


    $artistEmail = $_SESSION['artist_email'];
    try {
        $stmt = $pdo->prepare('SELECT fullname FROM artist WHERE email = ?');
        $stmt->execute([$artistEmail]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $artistName = $result['fullname'];
        } else {
            // Handle the case where no matching artist is found
            $artmsg = "try login again";
        }
    } catch (PDOException $e) {
        $artmsg = 'Error: ' . $e->getMessage();
    }

    // Handle file upload for art image
    $targetDirectory = __DIR__ . "/../../arts/";
    // Change this to your desired upload directory
    $targetFile = $targetDirectory . basename($_FILES["artImage"]["name"]);
    $fileName = basename($_FILES["artImage"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if the image file is a actual image or fake image
    $check = getimagesize($_FILES["artImage"]["tmp_name"]);
    if ($check !== false) {
        // echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        $artmsg = "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        $artmsg = "Sorry, file already exists.";
        $uploadOk = 0;
    }


    // Allow certain file formats
    if (
        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif"
    ) {
        $artmsg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $artmsg = "Sorry, your file was not uploaded. invalid file name or format";
    } else {
        // If everything is ok, try to upload file
        if (move_uploaded_file($_FILES["artImage"]["tmp_name"], $targetFile)) {
            // Insert art data into the database
            $artistId = $_SESSION['artist_id'];
            try {
                $stmt = $pdo->prepare('INSERT INTO art (artName, artType, artDescription, frameSize, artPrice, artistId, artistName, artistEmail, artImage, sellType) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$artName, $artType, $artDescription, $frameSize, $artPrice, $artistId, $artistName, $artistEmail, $fileName, getSellTypeValue($sellingType)]);
                $artmsgsuccess = "Art Post Successful!";
            } catch (PDOException $e) {
                $artmsg = 'Error inserting art data: ' . $e->getMessage();
            }
        } else {
            $artmsg = "Sorry, there was an error uploading your file.";
        }
    }
}
// Handle form submission for updating artist bio
if (isset($_POST['updateBio'])) {
    // Get the artist's bio from the form
    $artistBio = $_POST['artistBio'];

    // Update the artist's bio and set bioStatus to 1
    updateArtistBio($pdo, $_SESSION['artist_email'], $artistBio);

    // Redirect to the same page to avoid resubmission on refr   esh
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Function to update artist bio and set bioStatus to 1
function updateArtistBio($pdo, $email, $bio)
{
    try {
        // Update artist's bio and set bioStatus to 1
        $stmt = $pdo->prepare('UPDATE artist SET bio = ?, bioStatus = 1 WHERE email = ?');
        $stmt->execute([$bio, $email]);
        echo "Artist bio updated successfully.";
    } catch (PDOException $e) {
        echo 'Error updating artist bio: ' . $e->getMessage();
    }
}
function getArtistBio($pdo, $email)
{
    try {
        $stmt = $pdo->prepare('SELECT bio FROM artist WHERE email = ?');
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['bio'] : '';
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
        return '';
    }
}

$stmtArtsWithBids = $pdo->prepare('
    SELECT a.*, b.bidId, b.bidAmount AS highestBid, b.bidderName, b.sold
    FROM art a
    LEFT JOIN (
        SELECT bidId, artId, bidAmount, bidderName, sold
        FROM bids
        WHERE (artId, bidAmount) IN (
            SELECT artId, MAX(bidAmount) AS bidAmount
            FROM bids
            GROUP BY artId
        )
    ) b ON a.artId = b.artId
    WHERE a.artistEmail = ?
    ORDER BY a.artId
');

$stmtArtsWithBids->execute([$_SESSION['artist_email']]);
$artsWithBids = $stmtArtsWithBids->fetchAll(PDO::FETCH_ASSOC);



if (isset($_POST['sell'])) {
    // Handle the sell button click
    $bidId = $_POST['bidId'];
    // $bidAmount = $_POST["bidAmount"];

    // Check if the 'sold' column is 0 before updating
    $stmtCheckSold = $pdo->prepare('SELECT sold FROM bids WHERE bidId = ? AND sold = 0');
    $stmtCheckSold->execute([$bidId]);
    $resultSold = $stmtCheckSold->fetch(PDO::FETCH_ASSOC);

    if ($resultSold) {
        // 'sold' is 0, update the 'sold' column to 1
        $stmtUpdateSold = $pdo->prepare('UPDATE bids SET sold = 1 WHERE bidId = ?');
        $stmtUpdateSold->execute([$bidId]);
        $artmsgsuccess = "Art has been sold!";
    } else {
        $artmsg = "Art has already been sold or there is an issue.";
    }
}



if (isset($_POST['delete'])) {
    $artIdToDelete = $_POST['artId'];

    // Fetch art details from the 'art' table
    $stmtFetchArt = $pdo->prepare('SELECT artImage FROM art WHERE artId = ?');
    $stmtFetchArt->execute([$artIdToDelete]);
    $artToDelete = $stmtFetchArt->fetch(PDO::FETCH_ASSOC);

    if ($artToDelete) {
        // Delete image file from the 'arts' folder
        $targetFileToDelete = __DIR__ . "/../../arts/" . basename($artToDelete['artImage']);
        if (file_exists($targetFileToDelete)) {
            unlink($targetFileToDelete);
        }

        // Delete art bidding from the 'bids' table
        $stmtDeleteBids = $pdo->prepare('DELETE FROM bids WHERE artId = ?');
        $stmtDeleteBids->execute([$artIdToDelete]);

        // Delete the art row from the 'art' table
        $stmtDeleteArt = $pdo->prepare('DELETE FROM art WHERE artId = ?');
        $stmtDeleteArt->execute([$artIdToDelete]);

        // Redirect to the same page to refresh the display
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Handle the case where no matching art is found
        $artmsg = "Art not found for deletion.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Dashboard</title>
    <link rel="stylesheet" href="/ArtConnect/assets/css/dashboard.css">
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
    <div class="ad">
        <h1 class="ad">Artist Dashboard</h1>
    </div>
    <?php if ($artistStatus == 0): ?>
        <p>Your account is not verified. Cannot post art.</p>
    <?php elseif ($artistStatus == 2): ?>
        <p>Your account is under verification. Cannot post art until verified.</p>
    <?php elseif ($artistStatus == 3): ?>
        <p>please fill the verification form</p>
        <a href="/ArtConnect/verifyartist/verifyArtist.php">verification link</a>
    <?php else: ?>
        <div class="wrap-pa">
            <div class="post-art">
                <h2>Post Art</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                    enctype="multipart/form-data">
                    <label for="artName">Art Name:</label>
                    <input type="text" name="artName" id="artName" required autocomplete="off"><br>

                    <label for="artType">Art Type:</label>
                    <select name="artType" id="artType" required>
                        <option value="thangka art">Thangka art</option>
                        <option value="watercolor painting">Watercolor Painting</option>
                        <option value="canvas painting">Canvas Painting</option>
                        <option value="pottery">Pottery</option>
                        <option value="sculpture">Sculpture</option>
                        <option value="clay art">Clay art</option>
                        <option value="pencil art">Pencil art</option>
                    </select><br>

                    <label for="artDescription">Art Description:</label>
                    <textarea name="artDescription" id="artDescription" required autocomplete="off"></textarea><br>

                    <label for="frameSize">Frame Size:</label>
                    <input type="text" name="frameSize" id="frameSize" required autocomplete="off"><br>

                    <label for="artPrice">Art Price:</label>
                    <input type="text" name="artPrice" id="artPrice" required autocomplete="off"><br>

                    <label for="artImage">Art Image:</label>
                    <input type="file" name="artImage" id="artImage" required autocomplete="off"><br>

                    <label for="sellingType">Selling Type:</label>
                    <select name="sellingType" id="sellingType">
                        <option value="normal">Normal</option>
                        <option value="auction">Auction</option>
                    </select><br>

                    <button type="submit" name="postArt">Post Art</button>
                </form>
            </div>
        </div>

    <?php endif; ?>
    <?php if ($artistStatus == 1): ?>
        <div class="update-bio">
            <h2>Update Bio</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="updatebio">
                <label for="artistBio">Artist Bio:</label>
                <textarea name="artistBio" id="artistBio"
                    required><?php echo getArtistBio($pdo, $_SESSION['artist_email']); ?></textarea><br>
                <button type="submit" name="updateBio">Update Bio</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="arts-with-bids">
        <div class="ya">
            <h2 class="ya">Your Arts</h2>
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

                    <?php if ($art['highestBid'] !== null): ?>
                        <p>Highest Bid: Rs.
                            <?php echo htmlspecialchars($art['highestBid']); ?>
                        </p>

                        <!-- Check if bidderName is set -->
                        <?php if (isset($art['bidderName'])): ?>
                            <p>Bidder:
                                <?php echo htmlspecialchars($art['bidderName']); ?>
                            </p>
                        <?php else: ?>
                            <p>No bidder information available.</p>
                        <?php endif; ?>

                    <?php else: ?>
                        <p>No bids yet.</p>
                    <?php endif; ?>

                    <div class="sell-del">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="artId" value="<?php echo htmlspecialchars($art['artId']); ?>">
                            <input type="hidden" name="bidId" value="<?php echo htmlspecialchars($art['bidId']); ?>">
                            <div class="buttons">
                                <?php if ($art['highestBid'] !== null && $art['sold'] == 0): ?>
                                    <!-- Show the "Sell" button if sold is 0 -->
                                    <button type="submit" name="sell" class="sell">Sell</button>
                                <?php elseif ($art['sold'] == 1): ?>
                                    <!-- Display a message if sold is 1 -->
                                    <span class="sold-msg">Sold</span>
                                <?php endif; ?>
                                <button type="submit" name="delete" class="delete">Delete Art</button>
                            </div>
                        </form>


                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <button type="submit" name="logout">Logout</button>
    </form>
</body>

</html>