<link rel="stylesheet" href="/ArtConnect/assets/css/navbar.css">

<?php
session_start(); // Start the session

function isArtistLoggedIn()
{
    return isset($_SESSION['artist_id']);
}

function isBuyerLoggedIn()
{
    return isset($_SESSION['buyer_id']);
}
?>
<div class="nav">
    <div class="nav-bar">
        <div class="logo">
            <img src="/ArtConnect/assets/images/Logo/Logo.png" alt="ArtConnect" class="logo">
        </div>
        <nav class="navigation">
            <ul class="links">
                <li class="links"><a href="/ArtConnect">Home</a></li>
                <li class="links"><a href="#footermain">About Us</a></li>
                <li class="links"><a href="/ArtConnect/cart.php">My Cart</a></li>
                <li class="links"><a href="/ArtConnect/auction.php">Auction</a></li>
            </ul>
        </nav>

        <?php if (isArtistLoggedIn()): ?>
            <div class="login">
                <a href="/ArtConnect/artist/dashboard/index.php" target="_blank">
                    <div class="lg">
                        Dashboard
                    </div>
                </a>
            </div>
        <?php elseif (isBuyerLoggedIn()): ?>
            <div class="login">
                <a href="/ArtConnect/profile.php" target="_blank">
                    <div class="lg">
                        My Profile
                    </div>
                </a>
            </div>

        <?php else: ?>
            <div class="login">
                <a href="/ArtConnect/whoareyou.html" target="_blank">
                    <div class="lg">
                        Sign Up | Login
                    </div>
                </a>
            </div>
        <?php endif; ?>
        <div class="hamburger">
            <label for="burger" class="burger">
                <input id="burger" type="checkbox">
                <span></span>
                <span></span>
                <span></span>
            </label>
        </div>
    </div>
    <div class="m-nav">
        <?php if (isArtistLoggedIn()): ?>
            <div class="login-m">
                <a href="/ArtConnect/artist/dashboard/index.php" target="_blank">
                    <div class="lg">
                        Dashboard
                    </div>
                </a>
            </div>
        <?php elseif (isBuyerLoggedIn()): ?>
            <div class="login-m">
                <a href="/ArtConnect/profile.php" target="_blank">
                    <div class="lg">
                        My Profile
                    </div>
                </a>
            </div>

        <?php else: ?>
            <div class="login-m">
                <a href="/ArtConnect/whoareyou.html" target="_blank">
                    <div class="lg">
                        Sign Up | Login
                    </div>
                </a>
            </div>
        <?php endif; ?>
        <ul class="links-m">
            <li class="links"><a href="/ArtConnect">Home</a></li>
            <li class="links"><a href="#footermain">About Us</a></li>
            <li class="links"><a href="/ArtConnect/cart.php">My Cart</a></li>
            <li class="links"><a href="/ArtConnect/auction.php">Auction</a></li>
        </ul>
    </div>
</div>
<script>
    // Assuming you want the first element with the class "m-nav"
    let mbl = document.getElementsByClassName("m-nav")[0];
    let hmCheckbox = document.getElementById("burger");

    // Check if the elements are found before adding the event listener
    if (mbl && hmCheckbox) {
        hmCheckbox.addEventListener('click', () => {
            mbl.style.display = (hmCheckbox.checked) ? "block" : "none";
        });
    }
</script>