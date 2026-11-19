<?php
/**
 * Punto de entrada del Single Sign-On con Autenticacion Integrada de Windows.
 * IIS exige autenticacion de Windows unicamente para este archivo (acceso anonimo
 * deshabilitado en su configuracion); aqui solo se traduce esa identidad en una
 * sesion del portal y se calculan sus roles desde Active Directory.
 */
require_once __DIR__ . '/includes/auth.php';

$next = safeNext($_GET['next'] ?? '');

if (isAuthenticated()) {
    header('Location: ' . $next);
    exit;
}

$r = ssoLogin();
if ($r['ok']) {
    header('Location: ' . $next);
    exit;
}
header('Location: login.php?sso=fail');
exit;
