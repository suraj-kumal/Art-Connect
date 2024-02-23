<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: /ArtConnect/admin');
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['markAsPaid'])) {

        markAsPaid($pdo, 1);
    } elseif (isset($_POST['markAsUnpaid'])) {

        markAsPaid($pdo, 0);
    } elseif (isset($_POST['updateDeliveryStatus'])) {

        updateDeliveryStatus($pdo);
    }
}

function markAsPaid($pdo, $status)
{
    if (isset($_POST['markAsPaid']) || isset($_POST['markAsUnpaid'])) {

        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare('UPDATE deliveryandpayment SET paid = ? WHERE id = ?');
            $stmt->execute([$status, $id]);
        } catch (PDOException $e) {
            echo 'Error updating payment status: ' . $e->getMessage();
        }
    }
}

function updateDeliveryStatus($pdo)
{
    if (isset($_POST['updateDeliveryStatus'])) {

        $id = $_POST['id'];
        $deliveryStatus = $_POST['deliveryStatus'];
        try {
            $stmt = $pdo->prepare('UPDATE deliveryandpayment SET deliveryStatus = ? WHERE id = ?');
            $stmt->execute([$deliveryStatus, $id]);
        } catch (PDOException $e) {
            echo 'Error updating delivery status: ' . $e->getMessage();
        }
    }
}


$stmt = $pdo->prepare('SELECT * FROM deliveryandpayment');
$stmt->execute();
$deliveryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
    <div class="bread-crum">
        <a href="/ArtConnect/admin/dashboard/dashboard.php"><img src="./back-button.png" alt="back button"
                class="back"></a>
    </div>

    <div class="container mt-5">
        <h2>Delivery Dashboard</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Art Name</th>
                        <th>Art Type</th>
                        <th>Amount</th>
                        <th>Buyer Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>District</th>
                        <th>Location</th>
                        <th>Paid</th>
                        <th>Delivery Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliveryData as $data): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($data['artName']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['artType']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['amount']); ?>
                            </td>
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
                                <?php echo htmlspecialchars($data['district']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['location']); ?>
                            </td>
                            <td>
                                <?php echo ($data['paid'] == 1) ? 'Paid' : 'Unpaid'; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['deliveryStatus']); ?>
                            </td>
                            <td>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>">
                                    <button type="submit" name="markAsPaid" class="btn btn-success btn-sm">Mark as
                                        Paid</button>
                                </form>

                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>">
                                    <button type="submit" name="markAsUnpaid" class="btn btn-danger btn-sm">Mark as
                                        Unpaid</button>
                                </form>

                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>">
                                    <label for="deliveryStatus">Update Status:</label>
                                    <select name="deliveryStatus" id="deliveryStatus" required>
                                        <option value="Pending">Pending</option>
                                        <option value="Shipped">Shipped</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                    <button type="submit" name="updateDeliveryStatus" class="btn btn-primary btn-sm">Update
                                        Delivery Status</button>
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