<?php
// CmdSharp Download Page with License Validation
$license_key = $_GET['license'] ?? '';
$license_valid = false;
$license_data = null;
$error_message = '';

if (!empty($license_key)) {
    try {
        // Verify license via new API endpoint
        $api_url = "https://ai.proviska.com/api/license-validate-v2.php";
        
        $post_data = json_encode([
            'action' => 'validate',
            'license_key' => $license_key,
            'app_id' => 'cmdsharp'
        ]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($post_data)
                ],
                'content' => $post_data
            ]
        ]);
        
        $response = file_get_contents($api_url, false, $context);
        
        if ($response !== false) {
            $license_data = json_decode($response, true);
            $license_valid = $license_data['valid'] ?? false;
        } else {
            $error_message = 'Unable to verify license. Please try again later.';
        }
    } catch (Exception $e) {
        $error_message = 'License verification failed. Please try again.';
    }
}

// Get latest APK download URL
$latest_apk_url = "https://ai.proviska.com/api/app-update.php?action=download&app_id=cmdsharp&version=latest";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $license_valid ? 'Download CmdSharp' : 'License Required'; ?> - CmdSharp</title>
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

        .download-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            max-width: 700px;
            margin: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .download-section {
            background: rgba(0, 0, 0, 0.3);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
            border-left: 4px solid #ffd700;
        }

        .download-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #ffd700;
        }

        .download-link {
            display: inline-block;
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #333;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 0.5rem;
        }

        .download-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3);
        }

        .license-info {
            background: rgba(0, 0, 0, 0.4);
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .error-section {
            background: rgba(255, 0, 0, 0.1);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
            border-left: 4px solid #ff6b6b;
        }

        .license-form {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
        }

        .license-input {
            width: 100%;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .license-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .verify-btn {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #333;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .verify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3);
        }

        .instructions {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 10px;
            margin: 2rem 0;
            text-align: left;
        }

        .step {
            margin-bottom: 1rem;
            padding-left: 2rem;
            position: relative;
        }

        .step::before {
            content: counter(step-counter);
            counter-increment: step-counter;
            position: absolute;
            left: 0;
            top: 0;
            background: #ffd700;
            color: #333;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .instructions ol {
            counter-reset: step-counter;
        }

        .support-info {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .support-info h3 {
            color: #ffd700;
            margin-bottom: 1rem;
        }

        .support-info a {
            color: #ffd700;
            text-decoration: none;
        }

        .support-info a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .download-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .title {
                font-size: 2rem;
            }
            
            .download-link {
                width: 100%;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <div class="download-container">
        <?php if ($license_valid): ?>
            <!-- Valid License - Show Download -->
            <div class="icon">📱</div>
            <h1 class="title">Download CmdSharp</h1>
            <p class="message">
                Your license has been verified! Download the CmdSharp app and start commanding your invoicing.
            </p>

            <div class="license-info">
                <strong>Verified License:</strong> <?php echo htmlspecialchars($license_key); ?><br>
                <strong>License Type:</strong> <?php echo htmlspecialchars($license_data['license']['type'] ?? 'Lifetime'); ?><br>
                <strong>Status:</strong> <?php echo htmlspecialchars($license_data['license']['status'] ?? 'Active'); ?>
            </div>

            <div class="download-section">
                <h2 class="download-title">📱 Download Your App</h2>
                <p style="margin-bottom: 1.5rem;">Click below to download the CmdSharp APK file:</p>
                
                <a href="<?php echo $latest_apk_url; ?>" class="download-link" download>
                    📱 Download CmdSharp APK
                </a>
                
                <p style="font-size: 0.9rem; margin-top: 1rem; opacity: 0.8;">
                    File size: ~15MB • Compatible with Android 6.0+
                </p>
            </div>

            <div class="instructions">
                <h3 style="color: #ffd700; margin-bottom: 1rem;">📋 Installation Instructions</h3>
                <ol>
                    <li class="step">Download the APK file to your Android device</li>
                    <li class="step">Enable "Install from unknown sources" in your device settings</li>
                    <li class="step">Open the downloaded APK file and install CmdSharp</li>
                    <li class="step">Launch the app and enter your license key: <strong><?php echo htmlspecialchars($license_key); ?></strong></li>
                    <li class="step">Start creating invoices with voice commands!</li>
                </ol>
            </div>

        <?php elseif (!empty($license_key)): ?>
            <!-- Invalid License -->
            <div class="icon">❌</div>
            <h1 class="title">Invalid License</h1>
            <p class="message">
                The license key you provided is not valid or has expired.
            </p>

            <div class="error-section">
                <h3 style="color: #ff6b6b; margin-bottom: 1rem;">License Verification Failed</h3>
                <p><strong>License Key:</strong> <?php echo htmlspecialchars($license_key); ?></p>
                <?php if (!empty($error_message)): ?>
                    <p><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
            </div>

            <div class="license-form">
                <h3 style="color: #ffd700; margin-bottom: 1rem;">Try Another License Key</h3>
                <form method="GET" action="">
                    <input type="text" name="license" class="license-input" 
                           placeholder="Enter your license key (CMDSHARP-XXXX-XXXX-XXXX)" 
                           value="" required>
                    <button type="submit" class="verify-btn">Verify License</button>
                </form>
            </div>

        <?php else: ?>
            <!-- No License Provided -->
            <div class="icon">🔐</div>
            <h1 class="title">License Required</h1>
            <p class="message">
                Please enter your CmdSharp license key to download the app.
            </p>

            <div class="license-form">
                <h3 style="color: #ffd700; margin-bottom: 1rem;">Enter Your License Key</h3>
                <form method="GET" action="">
                    <input type="text" name="license" class="license-input" 
                           placeholder="Enter your license key (CMDSHARP-XXXX-XXXX-XXXX)" 
                           required>
                    <button type="submit" class="verify-btn">Verify & Download</button>
                </form>
            </div>

            <div class="instructions">
                <h3 style="color: #ffd700; margin-bottom: 1rem;">💡 Where to Find Your License Key</h3>
                <ul style="text-align: left; list-style: none; padding: 0;">
                    <li class="step">Check your purchase confirmation email</li>
                    <li class="step">Look in your AppSumo account dashboard</li>
                    <li class="step">Visit the success page after purchase</li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="support-info">
            <h3>📞 Need Help?</h3>
            <p>Having trouble with your download or license?</p>
            <p>Email us at: <a href="mailto:support@proviska.com">support@proviska.com</a></p>
            <p>We typically respond within 24 hours</p>
        </div>
    </div>

    <script>
        // Auto-fill license from URL fragment if present
        if (window.location.hash) {
            const license = window.location.hash.substring(1);
            const input = document.querySelector('input[name="license"]');
            if (input && license.match(/^(CMDSHARP|APPSUMO|MANUAL)-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/)) {
                input.value = license;
            }
        }

        // Format license key input
        document.addEventListener('DOMContentLoaded', function() {
            const licenseInput = document.querySelector('input[name="license"]');
            if (licenseInput) {
                licenseInput.addEventListener('input', function(e) {
                    // Auto-format and uppercase license key
                    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
                    
                    // Auto-add dashes if needed
                    if (value.length > 8 && value.indexOf('-', 8) === -1) {
                        value = value.substring(0, 8) + '-' + value.substring(8);
                    }
                    if (value.length > 13 && value.indexOf('-', 13) === -1) {
                        value = value.substring(0, 13) + '-' + value.substring(13);
                    }
                    if (value.length > 18 && value.indexOf('-', 18) === -1) {
                        value = value.substring(0, 18) + '-' + value.substring(18);
                    }
                    
                    e.target.value = value;
                });
            }
        });
    </script>
</body>
</html>