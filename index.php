<?php
$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch art data from the database where sellType is equal to 1
$stmt = $pdo->query('SELECT * FROM art WHERE sellType = 1 ORDER BY RAND()');
$arts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = ['all', 'thangka art', 'watercolor painting', 'canvas painting', 'pottery', 'sculpture', 'clay art', 'pencil art'];

if (isset($_GET['category']) && in_array($_GET['category'], $categories)) {
    $selectedCategory = $_GET['category'];
    if ($selectedCategory !== 'all') {
        // Fetch art data based on category and sellType
        $stmt = $pdo->prepare('SELECT * FROM art WHERE artType = ? AND sellType = 1 ORDER BY RAND()');
        $stmt->execute([$selectedCategory]);
        $arts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (isset($_GET['search'])) {
    $searchTerm = '%' . $_GET['search'] . '%';
    // Fetch art data based on search term and sellType
    $stmt = $pdo->prepare('SELECT * FROM art WHERE (artName LIKE ? OR artType LIKE ? OR frameSize LIKE ? OR artistName LIKE ?) AND sellType = 1 ORDER BY RAND()');
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $arts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>artconnect</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>

<body>
    <?php include 'includes/navbar/navbar.php'; ?>
    <form method="get">
        <div class="search">
            <div class="container-input">
                <input type="text" placeholder="Search" name="search" class="input">
                <svg fill="#000000" width="20px" height="20px" viewBox="0 0 1920 1920"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M790.588 1468.235c-373.722 0-677.647-303.924-677.647-677.647 0-373.722 303.925-677.647 677.647-677.647 373.723 0 677.647 303.925 677.647 677.647 0 373.723-303.924 677.647-677.647 677.647Zm596.781-160.715c120.396-138.692 193.807-319.285 193.807-516.932C1581.176 354.748 1226.428 0 790.588 0S0 354.748 0 790.588s354.748 790.588 790.588 790.588c197.647 0 378.24-73.411 516.932-193.807l516.028 516.142 79.963-79.963-516.142-516.028Z"
                        fill-rule="evenodd"></path>
                </svg>
                <button type="submit" class="btn">
                </button>
            </div>
        </div>
    </form>

    <div class="filter">
        <?php foreach ($categories as $category): ?>
            <form action="" method="get" style="display: inline-block; margin-right: 10px;">
                <input type="hidden" name="category" value="<?php echo $category; ?>">
                <button type="submit" style="cursor: pointer;" class="category">
                    <?php echo ucfirst(str_replace('_', ' ', $category)); ?>
                </button>
            </form>
        <?php endforeach; ?>
    </div>
    <div class="arts">
        <div class="card">
            <div class="art-gallery">
                <?php foreach ($arts as $art): ?>
                    <a href="./ArtDetails.php?id=<?php echo htmlspecialchars($art['artId']); ?>" class="art-link">
                        <div class="art-piece">
                            <div class="artimg">
                                <img src="/ArtConnect/arts/<?php echo htmlspecialchars($art['artImage']); ?>"
                                    alt="<?php echo htmlspecialchars($art['artName']); ?>" class="artimage">
                            </div>
                            <div class="det">
                                <h2 class="artname">
                                    <?php echo htmlspecialchars($art['artName']); ?>
                                </h2>
                                <p class="arttype">
                                    <?php echo htmlspecialchars($art['artType']); ?>
                                </p>
                    </a>
                    <a href="/ArtConnect/artist/profile?id=<?php echo htmlspecialchars($art['artistId']) ?>">
                        <p class="artArtist">
                            <?php echo htmlspecialchars($art['artistName']); ?>
                        </p>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
    </div>
    <?php include 'includes/footer/footer.php'; ?>
</body>

</html>