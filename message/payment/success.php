<?php
session_start();

// Assuming you have a database connection already established
$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the buyer is logged in
if (isset($_SESSION['buyer_id'])) {
    // Get artId from the GET method
    if (isset($_GET['artId'])) {
        $artId = $_GET['artId'];
        
        try {
            // Update the 'deliveryandpayment' table to set 'paid' column to 1 based on artId and buyerId
            $stmt = $pdo->prepare('UPDATE deliveryandpayment SET paid = 1 WHERE buyerId = ? AND artId = ?');
            $stmt->execute([$_SESSION['buyer_id'], $artId]);

            $msg = 'Payment successfully processed. Thank you!<br>Go to <a href="/ArtConnect">home</a> page';
        } catch (PDOException $e) {
            $msg = 'Error updating database: ' . $e->getMessage() . '';
        }
    } else {
        $msg = 'Invalid request. ArtId not provided.';
    }
} else {
    $msg = 'Invalid request. Payment was not successful.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    
<style>
    body {
        background-color: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .msg {
        background-color: #f0f0f0;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 8px 8px 16px #d3d3d3, -8px -8px 16px #ffffff;
        text-align: center;
    }

    h1 {
        color: #333;
    }

    a {
        color: #4CAF50;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="msg">
    <h1><?php echo $msg; ?></h1>
</div>
</body>
</html>
