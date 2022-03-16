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

/**
 * @return bool
 */
function authorisation(): bool
{
    global $pdo;

    $login = !empty($_POST['login']) ? trim($_POST['login']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Не все поля заполнены';
        return false;
    }
    
    $res = $pdo->prepare("SELECT * FROM my_db.users WHERE login = ?");
    $res->execute([$login]);
    $user = $res->fetch();


    if (!$user) {
        $_SESSION['errors'] = 'Введен неверный логин';
        return false;
    }

    if (password_verify($pass, $user['password'])) {
        $_SESSION['success'] = 'Вы успешно вошли';
        $_SESSION['user']['name'] = $user['login'];
        return true;
    } else {
        $_SESSION['errors'] = 'Введен неверный пароль';
        return false;
    }
}

function logout(): bool
{
    unset($_SESSION['user']);
    return true;
}

function addMessage(): bool
{
    global $pdo;

    $message = !empty($_POST['message']) ? trim($_POST['message']) : '';


    if ($_SESSION['user']['name']) {
        if (empty($message)) {
            $_SESSION['errors'] = 'Вы не ввели сообщение';
            return true;
        }
    } else {
        $_SESSION['errors'] = 'Вы не авторизованы';
    }

    $res = $pdo->prepare("INSERT INTO messages (name, message) VALUE (?, ?)");
    if ($res->execute([$_SESSION['user']['name'], $message])){
        $_SESSION['success'] = 'Сообщение было добавлено';
        return true;
    }else {
        $_SESSION['errors'] = 'Ошибка';
        return false;
    }
}


function getMessages(): array
{
    global $pdo;

    $res = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC ");
    return $res->fetchAll();
}
