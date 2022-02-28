<?php
$sql = new mysqli("localhost", "root", "", "riba"); // connecting to db

$session = []; // array for session user data

if($token = $_COOKIE["token"]){ // checking out if there is logined user
	$result = $sql->query("SELECT * FROM users WHERE token = '$token'");
	
	if($result->num_rows) // if token there's no such token in db, $session will be unseted
		$session = $result->fetch_assoc();
	else
		unset($session, $token);
}

if(!$token){
	unset($session); // it's also unsetting, if theres no any token in cookies or if it is wrong
	
	// if u r guest, u r able to login or signup
	if($_POST["login"] and $_POST["password"] and $_POST["name"]){ // creating new user, if there is $_POST["name"]
		extract($_POST);
		$result = $sql->query("SELECT * FROM users WHERE login = \"$login\" and password = \"$password\"");
		if($result->num_rows)
			echo "<script>alert(\"this login 's in use\");</script>";
		else
			$sql->query("INSERT INTO users VALUES (null, \"$name\", \"$login\", \"$password\", 0, null)");
	}
	if($_POST["login"] and $_POST["password"]){ // and logining user if there's no
		extract($_POST); // extract array values to variables: extrack(["login" => "nya", "password" => "anyanya"])  -->  $login = "nya"; $password = "anyanya";
		$result = $sql->query("SELECT * FROM users WHERE login = \"$login\" and password = \"$password\"");
		if($result->num_rows){
			$token = md5(rand(0, 10000000)); // that's what loining is: random string(token) created and saved in db and in users cookies, 
			$sql->query("UPDATE users SET token = \"$token\" WHERE login = \"$login\" and password = \"$password\""); // so every request will be checked on wich user did it
			SetCookie("token", $token, time()+32000000);
			
			$GLOBALS['session'] = $sql->query("SELECT * FROM users WHERE token = '$token'")->fetch_assoc(); // creating "session"
		}else echo "<script>alert(\"wrong!!\");</script>"; // if query got no result, alerting about wrong login/pswd
	}
}?>

<?=!$session ? '<a href="#login">login</a>' : '<a href="?" onclick="document.cookie=\'token=\'">logout</a>'?>
<br>
<?=!$session ? '<a href="#signup">signup</a>' : '<br>'?>
<br>
<br>
<br>
<br>

<style>
body{
	background-image: url(https://sun9-42.userapi.com/impg/PIRpqaiSk1w91sgc4yw1cgCW9WWUswjd1NAjKg/KLUjiqLDEqQ.jpg?size=1136x936&quality=96&sign=7a1aa7c2e520225bddf21f5a1d2fe4e3&type=album);
	background-size: cover;
}
.hidden{
	display: none;
	position: absolute;
	left:0;top:0;right:0;bottom:0;
	background-color: #000000C0;
	text-align: center;
	padding: 200px;
}
.hidden:target{ /*forms will apear on targeting(href="#id"), so no need to create more pages*/
	display: block;
}
</style>

<?php

if(!$session){
	echo "<h1>not logined</h1>";
}else

switch($session["level"]){
	case 0: // regular user can only see part of information about himself ?><h2>name: <?=$session["name"]?></h2><h2>status: regular user</h2><?php
		$result = $sql->query("SELECT id, name, login FROM users WHERE id = ".$session["id"]);
		$arr = $result->fetch_assoc();
		
		foreach($arr as $key => $value)
			echo "$key: $value<br>";
	break;
	case 1: // admin can see all the information about all users ?><h2>name: <?=$session["name"]?></h2><h2>status: admin</h2><?php
		$result = $sql->query("SELECT * FROM users");
		echo "<table border=1>";
		$row = $result->fetch_assoc();
		echo "<tr>";
		foreach($row as $key => $value)
				echo "<td>$key</td>";
		echo "</tr>";
		$result->data_seek(0);
		while($row = $result->fetch_assoc()){
			echo "<tr>";
			foreach($row as $value)
				echo "<td>$value</td>";
			echo "<tr>";
		}
		echo "</table>";
	break;
	case 2: // and cooler admin :) he can alse edit any information, about all the users ?><h2>name: <?=$session["name"]?></h2><h2>status: cooler admin</h2><?php
		if($_POST["name"]!==null and $_POST["login"]!==null and $_POST["password"]!==null and $_POST["id"]!==null and $_POST["level"]!==null and $_POST["token"]!==null){
			extract($_POST);
			$sql->query("UPDATE users SET id = '$id', name = '$name', login = '$login', password = '$password', level = '$level', token = '$token' WHERE id = $id");
		}
		
		$result = $sql->query("SELECT * FROM users");
		
		while($row = $result->fetch_assoc()){
			echo "<form method=\"post\">";
			echo "id: " . $row["id"] . " | <input type=\"hidden\" value=\"" . $row["id"] . "\"";
			
			foreach($row as $key => $value)
				echo "$key: <input name=\"$key\" value=\"$value\"> ";
			
			echo "<input type=\"submit\"></form><hr>";
		}
	break;
	default:
		echo "<h1>U HAVE NO ASS</h1>";
}
?>

<form method="post" id="signup" class="hidden" action="#">
	<input name="name" placeholder="name"><br><br>
	<input name="login" placeholder="login"><br><br>
	<input name="password" placeholder="password" type="password"><br><br>
	<input type="submit"><br><br>
	<a href="#">back</a>
</form>

<form method="post" id="login" class="hidden" action="#">
	<input name="login" placeholder="login"><br><br>
	<input name="password" placeholder="password" type="password"><br><br>
	<input type="submit"><br><br>
	<a href="#">back</a>
</form>

<?php

/* DUMP OF DATABASE. DEFAULT DB NAME IS "riba". IF WANNA USE ANOTHERE ONE, YOU SHOULD CHANGE 2'ND LINE OF THIS FILE(of php file, not of sql code).
HERE'S SQL CODE:

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(128) NOT NULL,
  `login` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `level` int NOT NULL,
  `token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `name`, `login`, `password`, `level`, `token`) VALUES
(1, 'putin', '123', 'qwerty', 2, '0ce33888ab8fdfe1d23f6ce3842dcf0e'),
(3, 'kopatich', 'a', 'b', 1, '7cdadb7e7f71d0973ed129b969c90313'),
(4, 'vasya', '1', '1', 0, '5f5b4ea14bd3154e0bb4afbe4aeeafd3');

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;
*/