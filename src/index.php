<?php

function getRealIpAddr() {
 if ( !empty($_SERVER['HTTP_CLIENT_IP']) ) {
  // Check IP from internet.
  $ip = $_SERVER['HTTP_CLIENT_IP'];
 } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
  // Check IP is passed from proxy.
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
 } else {
  // Get IP address from remote address.
  $ip = $_SERVER['REMOTE_ADDR'];
 }
 return $ip;
}

function cidr_match($ip, $range) {
    list ($subnet, $bits) = explode('/', $range);
    if ($bits === null) {
        $bits = 32;
    }
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
    return ($ip & $mask) == $subnet;
}

if (file_exists("/app/config/config.ini")) {
  $valid_passwords = parse_ini_file("/app/config/config.ini");
  $valid_users = array_keys($valid_passwords);
}

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);
$inSubnet = cidr_match(getRealIpAddr(), "192.168.1.0/32");

if (!$validated && !$inSubnet) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  die ("Not authorized");
}

// If arrives here, is a valid user.
echo "<p>Welcome $user.</p>";
echo "<p>Congratulation, you are into the system.</p>";
printf("<p>IP: %s</p>", getRealIpAddr());
if($inSubnet) {
  echo "<p>Wee in subnet</p>";
}

print_r($valid_users);

?>
