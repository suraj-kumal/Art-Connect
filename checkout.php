<?php
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(error_reporting() & ~E_WARNING);
session_start();
$district = '';
$location = '';
//i dont kow whats going on
if (isset($_GET['artId'])) {
    $artId = $_GET['artId'];
    $bidderId = $_SESSION['buyer_id'];

    // Assuming you have a PDO connection named $pdo
    $pdo = new PDO('mysql:host=localhost;dbname=artconnect', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the artId, bidderId, and sold condition match
    $stmt = $pdo->prepare('SELECT * FROM bids b 
                          INNER JOIN art a ON b.artId = a.artId 
                          INNER JOIN buyer bu ON b.bidderId = bu.id 
                          WHERE b.artId = ? AND b.bidderId = ? AND b.sold = 1');
    $stmt->execute([$artId, $bidderId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // ArtId, bidderId, and sold condition match, proceed with checkout logic
        $artId = $result['artId'];
        $artImage = $result['artImage'];
        $artName = $result['artName'];
        $artType = $result['artType'];
        $bidAmount = $result['bidAmount'];
        $artistName = $result['artistName'];
        $artistEmail = $result['artistEmail'];
        $bidderName = $result['fullname'];
        $bidderEmail = $result['email'];
        $bidderPhone = $result['phone'];

        // Check if district and location are already set
        $stmtDelivery = $pdo->prepare('SELECT district, location , paid FROM deliveryandpayment WHERE artId = ? AND buyerId = ?');
        $stmtDelivery->execute([$artId, $bidderId]);
        $deliveryResult = $stmtDelivery->fetch(PDO::FETCH_ASSOC);

        if ($deliveryResult) {
            $district = $deliveryResult['district'];
            $location = $deliveryResult['location'];
        }
    } else {
        // ArtId, bidderId, or sold condition doesn't match, set error message
        $error = "Error: Invalid request or artwork is not available for checkout.";
    }
} else {
    $error = "Error: Invalid request. Please provide a valid artId.";
}

// Handle delivery location update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update']) && empty($district) && empty($location)) {
    $district = $_POST['district'];
    $location = $_POST['location'];

    // Assuming you have a PDO connection named $pdo
    $stmtInsertDelivery = $pdo->prepare('INSERT INTO deliveryandpayment 
                                       (artId, artName, artType, amount, buyerId, name, email, phone, district, location, paid, deliveryStatus) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmtInsertDelivery->execute([$artId, $artName, $artType, $bidAmount, $bidderId, $bidderName, $bidderEmail, $bidderPhone, $district, $location, 0, 'Pending']);
}

$stmtPayment = $pdo->prepare('SELECT paid  FROM deliveryandpayment WHERE artId = ? AND buyerId = ?');
$stmtPayment->execute([$artId, $bidderId]);
$paymentResult = $stmtPayment->fetch(PDO::FETCH_ASSOC);

if ($paymentResult) {
    $paymentStatus = ($paymentResult['paid'] == 1) ? 'Paid' : 'Unpaid';
} else {
    $paymentStatus = 'Payment information not available';
}
$stmtDeliveryStatus = $pdo->prepare('SELECT deliveryStatus FROM deliveryandpayment WHERE artId = ? AND buyerId = ?');
$stmtDeliveryStatus->execute([$artId, $bidderId]);
$deliveryStatusResult = $stmtDeliveryStatus->fetch(PDO::FETCH_ASSOC);

if ($deliveryStatusResult) {
    $deliveryStatus = $deliveryStatusResult['deliveryStatus'];
} else {
    $deliveryStatus = 'Delivery status not available';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CheckOut</title>
    <link rel="stylesheet" href="assets/css/checkout.css"> <!-- Add your custom CSS file for styling -->
</head>

<body>
    <?php include 'includes/navbar/navbar.php'; ?>
    <?php if (isset($error)): ?>
        <p>
            <?php echo $error; ?>
        </p>
    <?php else: ?>
        <div class="checkout-container">
            <div class="art-dis">
                <div class="art-info">
                    <div class="artimg">
                        <img src="/ArtConnect/arts/<?php echo $artImage; ?>" alt="<?php echo $artName; ?>" class="artimage">
                    </div>
                    <p>Art Name:
                        <?php echo $artName; ?>
                    </p>
                    <p>Art Type:
                        <?php echo $artType; ?>
                    </p>
                    <p>Price: Rs.
                        <?php echo $bidAmount; ?>
                    </p>
                    <p>Artist Name:
                        <?php echo $artistName; ?>
                    </p>
                    <p>Artist Email:
                        <?php echo $artistEmail; ?>
                    </p>
                    <p>Name:
                        <?php echo $bidderName; ?>
                    </p>
                    <p>Email:
                        <?php echo $bidderEmail; ?>
                    </p>
                    <p>Phone:
                        <?php echo $bidderPhone; ?>
                    </p>
                    <p>District:
                        <?php echo $district; ?>
                    </p>
                    <p>Delivery Location:
                        <?php echo $location; ?>
                    </p>
                    <p>Payment Status:
                        <?php echo $paymentStatus; ?>
                    </p>
                    <p>Delivery Status:
                        <?php echo $deliveryStatus; ?>
                    </p>

                    <p class="note">"Please note that the artwork will be<br> dispatched for delivery through<br> <a
                            href="https://www.nepxpress.com/" target="_blank" class="nep">NepXpress Logistics</a><br> within two
                        days of successful payment."</p>
                    <?php if (empty($district) && empty($location)): ?>
                        <div class="delivery-form">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?artId=' . $artId); ?>"
                                method="post">
                                <div class="input-container">
                                    <input type="text" id="district" name="district" required>
                                    <label for="input" class="label">District</label>
                                    <div class="underline"></div>
                                </div>
                                <div class="input-container">
                                    <input type="text" id="location" name="location" required>
                                    <label for="input" class="label">location</label>
                                    <div class="underline"></div>
                                </div>
                                <input type="hidden" name="artId" value="<?php echo $artId; ?>">
                                <input type="hidden" name="buyerId" value="<?php echo $bidderId; ?>">
                                <button type="submit" name="update">Update Delivery Location</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($paymentStatus === 'Paid'): ?>
                <div class="thank-you">
                    <h3>Thank you for your purchase!</h3>
                </div>
            <?php else: ?>
                <div class="payment-wrap">
                    <div class="mp">
                        <h3>Wanna Make Payment ?</h3>
                    </div>
                    <div class="payment">
                        <div class="esewa">

                            <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.min.js"></script>
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/hmac-sha256.min.js"></script>
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/enc-base64.min.js"></script>
                            <form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST"
                                onsubmit="generateSignature()" target="_blank">

                                <br><br>
                                <table style="display:none">
                                    <tbody>
                                        <tr>
                                            <td> <strong>Parameter </strong> </td>
                                            <td><strong>Value</strong></td>
                                        </tr>

                                        <tr>
                                            <td>Amount:</td>
                                            <td> <input type="text" id="amount" name="amount" value="100" class="form"
                                                    required=""> <br>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Tax Amount:</td>
                                            <td><input type="text" id="tax_amount" name="tax_amount" value="0" class="form"
                                                    required="">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Total Amount:</td>
                                            <td><input type="text" id="total_amount" name="total_amount" value="100"
                                                    class="form" required="">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Transaction UUID:</td>
                                            <td><input type="text" id="transaction_uuid" name="transaction_uuid"
                                                    value="11-200-111sss1" class="form" required=""> </td>
                                        </tr>

                                        <tr>
                                            <td>Product Code:</td>
                                            <td><input type="text" id="product_code" name="product_code" value="EPAYTEST"
                                                    class="form" required=""> </td>
                                        </tr>

                                        <tr>
                                            <td>Product Service Charge:</td>
                                            <td><input type="text" id="product_service_charge" name="product_service_charge"
                                                    value="0" class="form" required=""> </td>
                                        </tr>

                                        <tr>
                                            <td>Product Delivery Charge:</td>
                                            <td><input type="text" id="product_delivery_charge" name="product_delivery_charge"
                                                    value="0" class="form" required=""> </td>
                                        </tr>

                                        <tr>
                                            <td>Success URL:</td>
                                            <td><input type="text" id="success_url" name="success_url"
                                                    value="http://localhost/ArtConnect/message/payment/success.php/?artId=<?php echo $artId; ?>"
                                                    class="form" required=""> </td>
                                        </tr>

                                        <tr>
                                            <td>Failure URL:</td>
                                            <td><input type="text" id="failure_url" name="failure_url"
                                                    value="https://developer.esewa.com.np/failure" class="form" required="">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>signed Field Names:</td>
                                            <td><input type="text" id="signed_field_names" name="signed_field_names"
                                                    value="total_amount,transaction_uuid,product_code" class="form" required="">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Signature:</td>
                                            <td><input type="text" id="signature" name="signature"
                                                    value="4Ov7pCI1zIOdwtV2BRMUNjz1upIlT/COTxfLhWvVurE=" class="form"
                                                    required=""> </td>
                                        </tr>
                                        <tr>
                                            <td>Secret Key:</td>
                                            <td><input type="text" id="secret" name="secret" value="8gBm/:&amp;EnhH.1/q"
                                                    class="form" required="">
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                                <input value=" Pay with eSewa " type="submit" class="button"
                                    style="display:block !important; background-color: #60bb46; cursor: pointer; color: #fff; border: none; padding: 5px 10px;'">
                            </form>

                            <script>
                                // Function to auto-generate signature
                                function generateSignature() {
                                    var currentTime = new Date();
                                    var formattedTime = currentTime.toISOString().slice(2, 10).replace(/-/g, '') + '-' + currentTime.getHours() +
                                        currentTime.getMinutes() + currentTime.getSeconds();
                                    document.getElementById("transaction_uuid").value = formattedTime;
                                    var total_amount = document.getElementById("total_amount").value;
                                    var transaction_uuid = document.getElementById("transaction_uuid").value;
                                    var product_code = document.getElementById("product_code").value;
                                    var secret = document.getElementById("secret").value;

                                    var hash = CryptoJS.HmacSHA256(
                                        `total_amount=${total_amount},transaction_uuid=${transaction_uuid},product_code=${product_code}`,
                                        `${secret}`);
                                    var hashInBase64 = CryptoJS.enc.Base64.stringify(hash);
                                    document.getElementById("signature").value = hashInBase64;
                                }

                                // Event listeners to call generateSignature() when inputs are changed
                                document.getElementById("total_amount").addEventListener("input", generateSignature);
                                document.getElementById("transaction_uuid").addEventListener("input", generateSignature);
                                document.getElementById("product_code").addEventListener("input", generateSignature);
                                document.getElementById("secret").addEventListener("input", generateSignature);
                            </script>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php include 'includes/footer/footer.php'; ?>
</body>

</html>