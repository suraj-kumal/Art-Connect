<?php
session_start();
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: /ArtConnect/admin');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        h1 {
            background-color: #333;
            color: #fff;
            padding: 20px;
            margin: 0;
            text-align: center;
        }

        a {
            display: block;
            padding: 20px;
            background-color: #fff;
            margin: 10px;
            text-decoration: none;
            color: #333;
            border-radius: 8px;
            box-shadow: 4px 4px 8px #d3d3d3, -4px -4px 8px #ffffff;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        a:hover {
            background-color: #f0f0f0;
        }

        form {
            text-align: center;
        }

        button {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #4CAF50;
            color: #fff;
            cursor: pointer;
            box-shadow: 4px 4px 8px #d3d3d3, -4px -4px 8px #ffffff;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <h1>Dashboard</h1>
    <a href="/ArtConnect/admin/dashboard/verify-artist.php">Verify Artist</a>
    <a href="/ArtConnect/admin/dashboard/manage-delivery.php">Manage Delivery</a>
    <a href="/ArtConnect/admin/dashboard/manage-arts.php">Manage Arts</a>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <button type="submit" name="logout">Logout</button>
    </form>
</body>

</html>