<?php

require_once 'vendor/autoload.php';
session_start();

$google_client = new Google_Client();
$google_client->setClientId('341535977204-6vsum5n0rtv1rcdq545da5og7cd5cu46.apps.googleusercontent.com');
$google_client->setClientSecret('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');
$google_client->setRedirectUri('http://localhost/web2125ki48veskerars03/googlelogin.php');
$google_client->addScope('email');
$google_client->addScope('profile');

// Створюємо URL для Google Login
$google_login_url = $google_client->createAuthUrl();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web2425";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Помилка підключення: " . $conn->connect_error);
}


$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);


    if (empty($login) || empty($password)) {
        $message = "Please fill in all fields!";
    } else {

        $check_sql = "SELECT * FROM login_password WHERE login = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $login);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This login is already taken!";
        } else {

            $open_password = $password;

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $encryption_key = "your_encryption_key";
            $iv = substr(hash('sha256', "your_iv"), 0, 16);
            $encrypted_password = openssl_encrypt($password, "aes-256-cbc", $encryption_key, 0, $iv);

            $sql = "INSERT INTO login_password (login, password, openssl_encrypt, password_hash) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }

            $stmt->bind_param("ssss", $login, $open_password, $encrypted_password, $hashed_password);

            if ($stmt->execute()) {
                // Надсилання повідомлення через WhatsApp
                $userPhone = $_POST['phone'] ?? '';
                $userPhone = preg_replace('/\D/', '', $userPhone); // очищення від нецифрових символів
            
                $token = 'EAAULFJ1GBDsBO0aPGQaTHUajp24ZBGimmSPrljh4jM6uqpHUpY5ZAJHBN8VdgscDMG5VdxxzJ1QREEHRXdrhy9qkfzclPGzXtChNCQL5PrqthwTycwkNEVOsSLYgkst2gQqq0wxwpZC6HB8CbYoYjQG8GkKsZBiFbjDMxbtQsMfqKmZBsB4poCyOFz9lnKJt37siBh9V1zxqRz9o3ez0WpMtrkTMZD';
                $phone_number_id = '550546854818368';
                $template_name = 'confirmation';
                $language_code = 'en';
            
                $url = "https://graph.facebook.com/v19.0/{$phone_number_id}/messages";
            
                $data = [
                    'messaging_product' => 'whatsapp',
                    'to' => $userPhone,
                    'type' => 'template',
                    'template' => [
                        'name' => $template_name,
                        'language' => ['code' => $language_code]
                    ]
                ];
            
                $options = [
                    'http' => [
                        'header'  => "Authorization: Bearer $token\r\n" .
                                     "Content-Type: application/json\r\n",
                        'method'  => 'POST',
                        'content' => json_encode($data),
                        'ignore_errors' => true
                    ]
                ];
            
                $context  = stream_context_create($options);
                $result = file_get_contents($url, false, $context);
            
                // Лог повідомлення (опційно, можеш видалити)
                if ($result === FALSE) {
                    $error = error_get_last();
                    error_log("Помилка WhatsApp API: " . print_r($error, true));
                } else {
                    error_log("Відповідь WhatsApp API: " . $result);
                }
            
                session_start();
                $_SESSION['login'] = $login;
            
                header("Location: home.php");
                exit();
            } else {
                $message = "Error: " . $conn->error;
            }

            $stmt->close();
        }

        $check_stmt->close();
    }
    
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            width: 320px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 400px; 
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #d9534f;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #d9534f;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
        }
        button:hover {
            background-color: #c9302c;
        }
        .message {
            margin-top: 15px;
            color: #f2a6a6;
        }
        .link {
            margin-top: 20px;
            display: block;
            text-decoration: none;
            color: #d9534f;
            font-size: 14px;
        }
        .link:hover {
            text-decoration: underline;
        }
        .google-signin {
            margin-top: 20px;
            margin-bottom: 10px;
            align-self: center; 
        }
        .google-signin img {
            width: 100%;
            max-width: 250px; 
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Реєстрація</h2>
    <form method="POST">
        <input type="text" name="login" placeholder="Введіть логін" required><br>
        <input type="password" name="password" placeholder="Введіть пароль" required><br>
        <label for="phone">Введіть номер телефону:</label><br>
        <input type="text" id="phone" name="phone" placeholder="380XXXXXXXXX" required><br><br>
        <button type="submit">Зареєструватися</button>
    </form>
    <div>
        <a href="login.php" class="link">Увійти</a>
    </div>
 <div class="google-signin">
    <a href="<?php echo $google_login_url; ?>" class="link">
        <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png" alt="Google Sign-In Button">
    </a>
</div>
</div>

</body>
</html>