<?php
// nitialisiere variablen
$input = '';
$hex = '';
$errorMessage = '';

// Inkludiere bech32 funktionen
require_once 'bech32.php';

// Funktion convert npub zu hex
function npubToHex($npub) {
    try {
        if (empty($npub)) {
            throw new Exception("Empty input");
        }

        if (!str_starts_with($npub, 'npub1')) {
            throw new Exception("Invalid npub format - must start with 'npub1'");
        }

        $hex = Bech32::decode($npub);
        if ($hex === null || strlen($hex) !== 64) {
            throw new Exception("Invalid bech32 encoding");
        }

        return $hex;
    } catch (Exception $e) {
        throw new Exception("Error converting npub: " . $e->getMessage());
    }
}

// Function to convert hex to npub
function hexToNpub($hex) {
    try {
        $hex = strtolower(trim($hex));
        if (empty($hex)) {
            throw new Exception("Empty input");
        }

        if (!ctype_xdigit($hex) || strlen($hex) !== 64) {
            throw new Exception("Invalid hex format - must be 64 characters");
        }

        $encoded = Bech32::encode($hex);
        if ($encoded === null) {
            throw new Exception("Encoding failed");
        }

        return 'npub1' . $encoded;
    } catch (Exception $e) {
        throw new Exception("Error converting hex: " . $e->getMessage());
    }
}

// Formular übermittlung
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['convertToHex'])) {
        $input = $_POST['input'] ?? '';
        try {
            if (!empty($input)) {
                $hex = npubToHex($input);
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
    } elseif (isset($_POST['convertToNpub'])) {
        $hex = $_POST['hex'] ?? '';
        try {
            if (!empty($hex)) {
                $input = hexToNpub($hex);
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
    } elseif (isset($_POST['reset'])) {
        $input = '';
        $hex = '';
        $errorMessage = '';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="width=device-width, initial-scale=1.0">
    <title>NPUB to HEX</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans min-h-screen">
    <div class="w-full max-w-4xl mx-auto p-6">
        <section class="mb-12 text-center">
            <h1 class="text-4xl font-bold mb-4 text-blue-600">NPUB to HEX</h1>
            <h2 class="text-xl font-medium mb-8 text-gray-700">Convert from NPUB to HEX</h2>
        </section>

        <section class="bg-white shadow-lg p-6 rounded-lg mb-4">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                <!-- npub Eingabe -->
                <div>
                    <label for="input" class="block text-blue-600 mb-2">Nostr npub</label>
                    <input type="text" 
                           id="input" 
                           name="input" 
                           value="<?php echo htmlspecialchars($input); ?>"
                           class="w-full bg-gray-50 border border-blue-300 text-gray-800 rounded-lg p-3 focus:outline-none focus:border-blue-500"
                           placeholder=" Paste npub">
                </div>

                <!-- Konvertieren Buttons -->
                <div class="flex justify-center space-x-4">
                    <button type="submit" 
                            name="convertToHex"
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg">
                        ↓ Convert to HEX ↓
                    </button>
                </div>

                <!-- Hex Eingabe -->
                <div>
                    <label for="hex" class="block text-blue-600 mb-2">Hexadecimal</label>
                    <input type="text" 
                           id="hex" 
                           name="hex" 
                           value="<?php echo htmlspecialchars($hex); ?>"
                           class="w-full bg-gray-50 border border-blue-300 text-gray-800 rounded-lg p-3 focus:outline-none focus:border-blue-500">
                </div>

                <!-- Reset Knopf -->
                <div class="text-center">
                    <button type="submit" 
                            name="reset"
                            class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg">
                        Empty fields
                    </button>
                </div>
            </form>
        </section>

        <!-- Error Messages -->
        <?php if ($errorMessage): ?>
        <div class="mt-6">
            <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded-lg">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Anleitung -->
        <section class="mt-8">
            <div class="bg-white shadow-lg p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-4 text-blue-600">Instructions</h3>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    <li>Paste your npub and hit "Convert to HEX"</li>
                </ul>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="w-full p-4 text-sm text-gray-600 text-center bg-gray-100 mt-12">
        <div class="max-w-7xl mx-auto">
		Made with love by <a href="https://relayted.de" class="hover:text-gray-800 text-blue-600">Relayted.de</a>
        </div>
    </footer>
</body>
</html>