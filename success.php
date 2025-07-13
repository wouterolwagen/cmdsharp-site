<?php
// Success page that generates license if payment was successful
// This prevents direct access abuse

$payment_id = $_GET['payment_id'] ?? '';
$generated = false;
$license_key = '';
$error = '';

if ($payment_id) {
    // Call the generate license API
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://ai.proviska.com/api/generate-license.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'payment_id' => $payment_id,
            'product' => 'cmdsharp-lifetime'
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($response) {
        $result = json_decode($response, true);
        if ($result['success']) {
            $license_key = $result['license_key'];
            $generated = true;
        } else {
            $error = $result['message'] ?? 'Failed to generate license';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Complete - CmdSharp</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #304C67 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .success-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            max-width: 600px;
            margin: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .success-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .license-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
            border: 2px solid #ffd700;
        }

        .license-key {
            font-family: monospace;
            font-size: 1.4rem;
            font-weight: bold;
            color: #ffd700;
            margin: 0.5rem 0;
            letter-spacing: 1px;
        }

        .button {
            display: inline-block;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            margin: 0.5rem;
            transition: transform 0.2s;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
        }

        .copy-button {
            background: #ffd700;
            color: #304C67;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-left: 1rem;
        }

        .copy-button:hover {
            background: #ffed4e;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if ($generated && $license_key): ?>
            <div class="success-icon">✅</div>
            <h1 class="success-title">Payment Successful!</h1>
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">Thank you for your purchase!</p>
            
            <div class="license-box">
                <h3 style="margin-bottom: 1rem;">Your License Key</h3>
                <div class="license-key" id="license-key"><?php echo htmlspecialchars($license_key); ?></div>
                <button class="copy-button" onclick="copyLicense()">Copy License Key</button>
                <p style="font-size: 0.9rem; margin-top: 1rem; opacity: 0.8;">
                    Save this key - you'll need it to activate the app
                </p>
            </div>

            <p style="margin-bottom: 2rem;">
                📧 An email with your license and download instructions has been sent to your email address.
            </p>

            <div style="margin-top: 2rem;">
                <a href="https://cmdsharp.com" class="button">Return to Website</a>
            </div>
        <?php elseif ($error): ?>
            <div class="success-icon">⚠️</div>
            <h1 class="success-title">Almost There!</h1>
            <div class="error">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
            <p style="margin: 2rem 0;">
                Your payment was successful, but we're still processing your license. 
                Please check your email in a few minutes or contact support.
            </p>
            <div>
                <a href="mailto:support@cmdsharp.com" class="button">Contact Support</a>
                <a href="https://cmdsharp.com" class="button">Return to Website</a>
            </div>
        <?php else: ?>
            <div class="success-icon">🔄</div>
            <h1 class="success-title">Processing...</h1>
            <p style="margin: 2rem 0;">
                We're processing your order. If you're not redirected here from a payment, 
                please return to our website.
            </p>
            <div>
                <a href="https://cmdsharp.com" class="button">Return to Website</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function copyLicense() {
            const licenseElement = document.getElementById('license-key');
            const licenseText = licenseElement.textContent;
            
            navigator.clipboard.writeText(licenseText).then(() => {
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            });
        }
    </script>
</body>
</html>