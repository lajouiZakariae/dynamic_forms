<?php

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function view(string $path, ?array $data = null): string {
    ob_start();
    extract($data);
    require APP_DIR . "/views/$path.php";
    return ob_get_clean();
}
