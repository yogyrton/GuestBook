<?php
session_start();


function debug($data)
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}

/**
 * @return bool
 */
function registration(): bool
{
    global $pdo;

    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Не все поля заполнены';
        return false;
    }


    $res = $pdo->prepare("SELECT COUNT(*) FROM my_db.users WHERE login = ?");
    $res->execute([$login]);

    if ($res->fetchColumn()) {
        $_SESSION['errors'] = 'Данное имя уже используется';
        return false;
    }

    $pass = password_hash($pass, PASSWORD_DEFAULT);
    $res = $pdo->prepare("INSERT INTO my_db.users (login, password) VALUE (?, ?)");
    if ($res->execute([$login, $pass])) {
        $_SESSION['success'] = 'Регистрация прошла успешно';
        return true;
    } else {
        $_SESSION['errors'] = 'Ошибка регистрации';
        return false;
    }
}


