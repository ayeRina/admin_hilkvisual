<?php

function safe_count(PDO $pdo, string $sql): int
{
    try {
        return (int) $pdo->query($sql)->fetchColumn();
    } catch (Throwable $exception) {
        return 0;
    }
}

function safe_value(PDO $pdo, string $sql, string $default = '0'): string
{
    try {
        $value = $pdo->query($sql)->fetchColumn();
        return $value === false || $value === null ? $default : (string) $value;
    } catch (Throwable $exception) {
        return $default;
    }
}
