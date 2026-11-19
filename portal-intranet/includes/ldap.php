<?php
/**
 * Integracion con el servicio de identidad (Active Directory / LDAP).
 * En entorno local macOS, soporta identity_backend = 'local' leyendo de directory.php
 * para permitir demostracion completa sin requerir el controlador de dominio Windows en vivo.
 */

function getLocalDirectory(): array
{
    static $dir = null;
    if ($dir !== null) {
        return $dir;
    }
    $file = glcPath('config', 'directory.php');
    if (is_file($file)) {
        $dir = require $file;
    } else {
        $dir = [
            'clopez' => [
                'username'     => 'clopez',
                'password'     => 'Corporativo2026!',
                'nombre'       => 'Carlos Daniel López Hernández',
                'email'        => 'clopez@glcentral.local',
                'cargo'        => 'Jefe de Infraestructura y Ciberseguridad',
                'departamento' => 'Tecnologías de la Información',
                'extension'    => '1004',
                'grupos'       => ['GRP-TI-Administradores', 'GRP-VPN-Users', 'Domain Users'],
                'disabled'     => false,
            ],
            'mmorales' => [
                'username'     => 'mmorales',
                'password'     => 'Corporativo2026!',
                'nombre'       => 'María Morales Solís',
                'email'        => 'mmorales@glcentral.local',
                'cargo'        => 'Coordinadora de Talento Humano',
                'departamento' => 'Recursos Humanos',
                'extension'    => '1002',
                'grupos'       => ['GRP-RRHH', 'GRP-VPN-Users', 'Domain Users'],
                'disabled'     => false,
            ],
            'jperez' => [
                'username'     => 'jperez',
                'password'     => 'Corporativo2026!',
                'nombre'       => 'Juan Pérez Arrivillaga',
                'email'        => 'jperez@glcentral.local',
                'cargo'        => 'Analista Senior de Finanzas y Presupuesto',
                'departamento' => 'Administración y Finanzas',
                'extension'    => '1003',
                'grupos'       => ['GRP-Administracion', 'Domain Users'],
                'disabled'     => false,
            ],
            'rgomez' => [
                'username'     => 'rgomez',
                'password'     => 'Corporativo2026!',
                'nombre'       => 'Roberto Gómez Carrillo',
                'email'        => 'rgomez@glcentral.local',
                'cargo'        => 'Supervisor de Despacho y Flota',
                'departamento' => 'Operaciones y Logística',
                'extension'    => '2105',
                'grupos'       => ['GRP-Operaciones', 'Domain Users'],
                'disabled'     => false,
            ],
        ];
    }
    return $dir;
}

function adConnect()
{
    if (cfg('identity_backend') === 'local' || !function_exists('ldap_connect')) {
        return false;
    }
    $con = @ldap_connect(cfg('ldap_uri', 'ldap://127.0.0.1:389'));
    if (!$con) {
        return false;
    }
    ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($con, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($con, LDAP_OPT_NETWORK_TIMEOUT, 3);
    return $con;
}

/** @return bool|null true = credenciales validas, false = invalidas, null = servicio no disponible */
function adAuthenticate(string $usuario, string $clave): ?bool
{
    if ($clave === '') {
        return false;
    }

    if (cfg('identity_backend') === 'local' || !function_exists('ldap_connect')) {
        $dir = getLocalDirectory();
        $u = strtolower(trim($usuario));
        if (isset($dir[$u])) {
            $user = $dir[$u];
            if (!empty($user['disabled'])) {
                return false;
            }
            if ($clave === $user['password'] || password_verify($clave, $user['password'])) {
                return true;
            }
            return false;
        }
        return false;
    }

    $con = adConnect();
    if (!$con) {
        return null;
    }
    $dominio = cfg('ad_domain', 'glcentral.local');
    $netbios = cfg('ad_netbios', 'GLCENTRAL');
    $ok = @ldap_bind($con, $usuario . '@' . $dominio, $clave);
    if (!$ok) {
        $ok = @ldap_bind($con, $netbios . '\\' . $usuario, $clave);
    }
    if (!$ok && ldap_errno($con) === -1) {
        @ldap_close($con);
        return null;
    }
    @ldap_close($con);
    return (bool)$ok;
}

/** Devuelve el perfil del usuario desde AD, o null si no existe / esta deshabilitado. */
function adGetUser(string $sam): ?array
{
    if (cfg('identity_backend') === 'local' || !function_exists('ldap_connect')) {
        $dir = getLocalDirectory();
        $s = strtolower(trim($sam));
        if (isset($dir[$s])) {
            $user = $dir[$s];
            if (!empty($user['disabled'])) {
                return null;
            }
            return [
                'username'     => $user['username'],
                'nombre'       => $user['nombre'],
                'email'        => $user['email'],
                'cargo'        => $user['cargo'],
                'departamento' => $user['departamento'],
                'extension'    => $user['extension'] ?? null,
                'grupos'       => $user['grupos'],
            ];
        }
        return null;
    }

    $con = adConnect();
    if (!$con) {
        return null;
    }
    if (!@ldap_bind($con, cfg('ldap_svc_user'), cfg('ldap_svc_pass'))) {
        error_log('[ldap] no fue posible enlazar con la cuenta de servicio');
        @ldap_close($con);
        return null;
    }
    $base   = cfg('ldap_base', 'DC=glcentral,DC=local');
    $filtro = '(&(objectCategory=person)(objectClass=user)(sAMAccountName=' . ldap_escape($sam, '', LDAP_ESCAPE_FILTER) . '))';
    $busq   = @ldap_search($con, $base, $filtro, ['samaccountname', 'displayname', 'cn', 'mail', 'title', 'department', 'useraccountcontrol', 'distinguishedname', 'telephonenumber']);
    $ent    = $busq ? ldap_get_entries($con, $busq) : ['count' => 0];
    if ($ent['count'] < 1) {
        @ldap_close($con);
        return null;
    }
    $r   = $ent[0];
    $uac = (int)($r['useraccountcontrol'][0] ?? 0);
    if ($uac & 2) { // cuenta deshabilitada
        @ldap_close($con);
        return null;
    }
    $dn = $r['distinguishedname'][0];

    $grupos = [];
    $f2 = '(&(objectClass=group)(member:1.2.840.113556.1.4.1941:=' . ldap_escape($dn, '', LDAP_ESCAPE_FILTER) . '))';
    $b2 = @ldap_search($con, $base, $f2, ['cn']);
    if ($b2) {
        $g = ldap_get_entries($con, $b2);
        for ($i = 0; $i < $g['count']; $i++) {
            $grupos[] = $g[$i]['cn'][0];
        }
    }
    @ldap_close($con);
    sort($grupos);

    return [
        'username'     => strtolower($r['samaccountname'][0]),
        'nombre'       => $r['displayname'][0] ?? $r['cn'][0],
        'email'        => $r['mail'][0] ?? (strtolower($r['samaccountname'][0]) . '@' . cfg('ad_domain', 'glcentral.local')),
        'cargo'        => $r['title'][0] ?? 'Colaborador',
        'departamento' => $r['department'][0] ?? 'General',
        'extension'    => $r['telephonenumber'][0] ?? null,
        'grupos'       => $grupos,
    ];
}

/** Miembros de un grupo (para listas de asignacion). @return array<string,string> sam => nombre */
function adGroupMembers(string $grupo): array
{
    if (cfg('identity_backend') === 'local' || !function_exists('ldap_connect')) {
        $dir = getLocalDirectory();
        $res = [];
        foreach ($dir as $u => $datos) {
            if (in_array($grupo, $datos['grupos'], true) && empty($datos['disabled'])) {
                $res[$u] = $datos['nombre'];
            }
        }
        asort($res);
        return $res;
    }

    $con = adConnect();
    if (!$con || !@ldap_bind($con, cfg('ldap_svc_user'), cfg('ldap_svc_pass'))) {
        return [];
    }
    $base = cfg('ldap_base', 'DC=glcentral,DC=local');
    $b = @ldap_search($con, $base, '(&(objectClass=group)(cn=' . ldap_escape($grupo, '', LDAP_ESCAPE_FILTER) . '))', ['distinguishedname']);
    $g = $b ? ldap_get_entries($con, $b) : ['count' => 0];
    if ($g['count'] < 1) {
        @ldap_close($con);
        return [];
    }
    $dn = $g[0]['distinguishedname'][0];
    $u  = @ldap_search($con, $base, '(&(objectCategory=person)(objectClass=user)(memberOf=' . ldap_escape($dn, '', LDAP_ESCAPE_FILTER) . '))', ['samaccountname', 'displayname', 'cn']);
    $res = [];
    if ($u) {
        $e = ldap_get_entries($con, $u);
        for ($i = 0; $i < $e['count']; $i++) {
            $res[strtolower($e[$i]['samaccountname'][0])] = $e[$i]['displayname'][0] ?? $e[$i]['cn'][0];
        }
    }
    @ldap_close($con);
    asort($res);
    return $res;
}
