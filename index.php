<?php
/*
 *  Copyright (c) 2013 nwxxeh
 *  http://github.com/nwxxeh/lrkr
 *
 */
 
//     CONFIGURATION - change this after importing links.sql into your database
$host = "localhost";
$username = "";
$password = "";
$database = "";
$website_name = "Generic Shortener";
$links_table = "links";
$domain = "example.com"; //change it!
$complaints = "abuse@example.com"; //complaints/abuse email
$path = "/l"; //change it to "/index.php?u=" if you don't have mod_rewrite enabled






mysql_connect($host, $username, $password) or die("ERROR");
mysql_select_db($database) or die("TERROR");
function alphaID($in, $to_num = false, $pad_up = false, $passKey = null)
{
    $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if ($passKey !== null) {

        for ($n = 0; $n<strlen($index); $n++) {
            $i[] = substr( $index,$n ,1);
        }

        $passhash = hash('sha256',$passKey);
        $passhash = (strlen($passhash) < strlen($index))
            ? hash('sha512',$passKey)
            : $passhash;

        for ($n=0; $n < strlen($index); $n++) {
            $p[] =  substr($passhash, $n ,1);
        }

        array_multisort($p,  SORT_DESC, $i);
        $index = implode($i);
    }

    $base  = strlen($index);

    if ($to_num) {
        // Digital number  <<--  alphabet letter code
        $in  = strrev($in);
        $out = 0;
        $len = strlen($in) - 1;
        for ($t = 0; $t <= $len; $t++) {
            $bcpow = pow($base, $len - $t);
            $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
        }

        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) {
                $out -= pow($base, $pad_up);
            }
        }
        $out = sprintf('%F', $out);
        $out = substr($out, 0, strpos($out, '.'));
    } else {
        // Digital number  -->>  alphabet letter code
        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) {
                $in += pow($base, $pad_up);
            }
        }

        $out = "";
        for ($t = floor(log($in, $base)); $t >= 0; $t--) {
            $bcp = pow($base, $t);
            $a   = floor($in / $bcp) % $base;
            $out = $out . substr($index, $a, 1);
            $in  = $in - ($a * $bcp);
        }
        $out = strrev($out); // reverse
    }

    return $out;
}
if (isset($_GET['u']))
{
$result = mysql_query("SELECT * FROM ".$links_table." WHERE id=".mysql_real_escape_string(alphaID($_GET['u'],true)));
if (($result) && (mysql_num_rows($result) == 1))
{
$row = mysql_fetch_assoc($result);
header("Location: ".$row['url']);
}
}
?>
<html>
<head>
<title><?php echo $website_name; ?></title>
<style type="text/css">
body{
font-size: 24px;
font-family: "Verdana";
color: #FFFFFF;
background: #CCCCCC url('./bg.png') repeat;
}
#logo {
width: 500px;
margin: auto;
font-size: 50px;
font-family: "Verdana";
color: #FFFFFF;
text-align: center;
text-shadow: 0px 2px 3px #555;
}
#main {
text-align: center;
background-color: #AAAAAA;
width: 500px;
padding-top: 20px;
margin: auto;
border: 1px solid #A0A0A0;
box-shadow: 0px 4px 4px #555;
border-radius: 2px;
}
#footer {
text-align: center;
width: 500px;
margin: auto;
font-size: 9px;
color: #FFFFFF;
margin-top: 7px;
}
a, a:visited {
color: #F0F0F0;
text-decoration: none;
}
a:hover {
color: #F000F0;
text-decoration: none;
}
input {
border: none;
}
input[type='text']
{
width: 150px;
font-size: 15px;
font-family: "Verdana";
height: 30px;
}
input[name='url']
{
width: 350px !important;
font-size: 20px !important;
font-family: "Verdana" !important;
height: 40px !important;
}
input[type='submit']
{
height: 40px;
font-size: 20px;
font-family: "Verdana";
color: #FFFFFF;
text-shadow: 0px 2px 3px #555;
background: #777;
}
</style>
</head>
<body>
<div id="logo">
<?php echo $website_name; ?>
</div>
<div id="main">
<?php
if (isset($_GET['f']))
{
switch ($_GET['f'])
{
  case "add":
    if (isset($_POST['url']))
    {
      if (filter_var($_POST['url'], FILTER_VALIDATE_URL) == false)
      {
	echo "<b>Please enter valid URL!</b><br />";
      } else {
	$result = mysql_query("SELECT * FROM links WHERE url=".mysql_real_escape_string($_POST['url']));
	if ((!$result) || (mysql_num_rows($result) == 0))
	{
	  $result = mysql_query("INSERT INTO links (url) VALUES ('".mysql_real_escape_string($_POST['url'])."')") or die("ERROR");
	  $id = mysql_insert_id();
	} else {
	  $row = mysql_fetch_assoc();
	  $id = $row['id'];
	}
	echo "Shorter url: <input type='text' value='http://".$domain.$path.alphaID($id)."' /><br /><br />";
      }
    }
    break;
}
}
?>
<form action="./fadd" method="POST">
<input type="text" name="url" /><input type="submit" name="sub" value="Short me!" />
</form>
</div>
<div id="footer">
Made by <a href="http://github.com/nwxxeh">nwxxeh</a> &bull; <a href="mailto:<?php echo $complaints; ?>">Abuse/complaints</a>
</div>
</body>
</html>