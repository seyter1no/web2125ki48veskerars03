<?php
// Параметри підключення до бази даних
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "web2425";

// Підключення до MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Перевірка підключення
if ($conn->connect_error) {
    die("Помилка підключення: " . $conn->connect_error);
}

// Повідомлення для користувача
$message = "";

// Якщо форма відправлена методом POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    // Перевіряємо, чи заповнені поля
    if (empty($login) || empty($password)) {
        $message = "Будь ласка, заповніть всі поля!";
    } else {
        // Перевіряємо, чи користувач вже існує
        $check_sql = "SELECT * FROM login_password WHERE Login = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $login);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Цей логін вже зайнятий!";
        } else {
            // Хешування пароля
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // SQL-запит для додавання користувача
            $sql = "INSERT INTO login_password (Login, Password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("SQL Error: " . $conn->error);
            }

            $stmt->bind_param("ss", $login, $hashed_password);

            if ($stmt->execute()) {
                // Створюємо сесію для користувача після успішної реєстрації
                session_start();
                $_SESSION['login'] = $login;

                // Перенаправляємо на головну сторінку
                header("Location: index.php");
                exit();
            } else {
                $message = "Помилка: " . $conn->error;
            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}

// Закриваємо з'єднання
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
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            width: 300px;
            text-align: center;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
        }
        button {
            width: 100%;
            padding: 10px;
            background: blue;
            color: white;
            border: none;
            cursor: pointer;
        }
        .message {
            margin-top: 10px;
            color: red;
        }
        .link {
            margin-top: 10px;
            display: block;
            text-decoration: none;
            color: blue;
        }
        .buttons {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Реєстрація</h2>
    <form method="POST">
        <input type="text" name="login" placeholder="Введіть логін" required><br>
        <input type="password" name="password" placeholder="Введіть пароль" required><br>
        <button type="submit">Зареєструватися</button>
    </form>
    <div class="message"><?php echo $message; ?></div>
    
    <div class="buttons">
        <!-- Кнопка для переходу на сторінку входу -->
        <a href="login.php"><button type="button">Увійти</button></a>
    </div>
</div>

</body>
</html>