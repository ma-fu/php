<?php
//css as file tips
$linktag = <<<eod
<link rel="stylesheet" href="styles.css?date('YmdHis');">
eod;
class sqler{
    private $db;
    private $v;
    private $q;
    private $b;
    function __construct($path="blog"){
        $this->db=new SQLite3($path);
        $this->make_tbl();
    }
    private function tbl_usr(){
        $tbl=<<<SQL
        CREATE TABLE user(
        id INTEGER PRIMARY KEY autoincrement,
        t1 TEXT,
        t2 TEXT,
        d1 TEXT DEFAULT (DATETIME('now','localtime')),
        d2 TEXT DEFAULT (DATETIME('now','localtime'))
        )
        SQL;
        $trg=<<<SQL
        CREATE TRIGGER usr_up
        AFTER UPDATE ON user
        BEGIN
        UPDATE user SET d2 = DATE('now','localtime') WHERE rowid = new.rowid;
        END;
        SQL;
        if(!isTbl("user")){
            $this.db->exec(tbl);
        }
        if(!isTrg("user_up")){
            $this.db->exec(trg);
        }
    }
    private function tbl_log(){
        $tbl=<<<SQL
        CREATE TABLE log(
        id INTEGER PRIMARY KEY autoincrement,
        t1 TEXT,
        t2 TEXT,
        t3 TEXT,
        t4 TEXT,
        i1 INTEGER,
        i2 INTEGER,
        d1 TEXT DEFAULT (DATE('now','localtime')),
        d2 TEXT DEFAULT (DATE('now','localtime')),
        FOREIGN KEY (i1) REFERENCES user (id) ON DELETE CASCADE
        )
        SQL;
        $trg=<<<SQL
        CREATE TRIGGER log_up
        AFTER UPDATE ON log
        BEGIN
        UPDATE log SET d2 = DATE('now','localtime') WHERE rowid = new.rowid;
        END;
        SQL;
        if(!isTbl("log")){
            $this.db->exec(tbl);
        }
        if(!isTrg("log_up")){
            $this.db->exec(trg);
        }

    }
    private function isTbl($t){
        $isT=<<<SQL
        SELECT count(*)
        FROM sqlite_master
        WHERE TYPE='table'
        AND NAME='$t'
        SQL;
        return $this->db->querySingle($isT);
    }
    private function isTrg($tr){
        $isTr=<<<SQL
        SELECT count(*)
        FROM sqlite_master
        WHERE TYPE='trigger'
        AND NAME='$tr'
        SQL;
        return $this->db->querySingle($isTr);
    }
    function make_tbl(){
        $this->tbl_usr();
        $this->tbl_log();
    }
    function Que($q){
        $this->q=$q;
    }
    function Val($v){
        $this->v=$v;
    }
    function Bind($b){
        $this->b=$b;
    }
    function Sel(){
        $r=$this->db->query($this->q);
        return $r->fetchArray();
    }
    function All(){
        return $r=$this->db->query($this->q);
    }
    function Ins(){
        $pre=$this->db->prepare($this->q);
        for($i=0;$i<count($this->b);$i++){
            $pre->bindValue($this->b[$i],$this->v[$i]);
        }
        $pre->execute();
    }
}
function test(){
    $test = new sqler();
    $test->make_tbl();
}
//Handler
function Handler(){
    $q=htmlspecialchars($_GET['q']);
    if($q=='admin'){
        Admin();
    }else if($q=="check"){
        Check();
    }else if($q=="test"){
        test();
    }else if($q=="register"){
        Register();
    }else if($q=="dash"){
        Dash();
    }else if($q=="logout"){
        Logout();
    }else if($q=="calendar"){
        Calendar_selector();
    }else{
        ls_all();
    }
}
function isUsr(){
    // $name=isset($_GET["name"])?htmlspecialchars($_GET["name"],ENT_QUOTES,"utf-8") : "";
    // $passcode=isset($_GET["passcode"])?htmlspecialchars($_GET["passcode"],ENT_QUOTES,"utf-8") : "";
    $name=$_GET["name"];
    $passcode=$_GET["passcode"];
    if($name&&$passcode){
        $q="select t1,t2 from user where t1='$name' and t2='$passcode'";
        $db=new sqler();
        $db->Que($q);
        $res = $db->Sel();
        if($name==$res[0]&&$passcode==$res[1]){
            $usr=true;
        }else{
            return false;
        }
    }else{
        return false;
    }
    return array($usr,$res[0],$res[1]);
}
//Check admin
function Register(){
    // session_start();
    // if(!$_SESSION['user']){
    //     header("Location:./index.php");
    //     exit;
    // }
    [$usr,$name,$passcode] = isUsr();
    
    if(!$usr){
        myheader("./index.php");
        exit;
    }
    if($_POST['submit']){
        // $name=isset($_POST["name"])?htmlspecialchars($_POST["name"],ENT_QUOTES,"utf-8") : "";
        // $password=isset($_POST["password"])?htmlspecialchars($_POST["password"],ENT_QUOTES,"utf-8") : "";
        $name=$_POST["name"];
        $passcode=$_POST["passcode"];
        if($name==""){
            myheader("./index.php");
            exit;
        }
        if($passcode==""){
            myheader("./index.php");
            exit;
        }
        $q = "select t1,t2 from user where t1='$name' and t2='$passcode'";
        $db=new sqler();
        $db->Que($q);
        $res = $db->Sel();
        if($name==$res[0]){
            myheader("./index.php");
            exit;
        }else{
            $q="insert into user (t1,t2) values(:t1,:t2)";
            $b=array(":t1",":t2");
            $v=array($name,$passcode);
            $db->Que($q);
            $db->Bind($b);
            $db->Val($v);
            $db->Ins();
            myheader("./index.php?q=dash");
        }
    }
    echo<<<eof
    <h3>Register</h3>
    <form action="./index.php?q=register" method="POST">
    <p>user</p>
    <input type="text" name="name" required>
    <p>password</p>
    <input type="password" name="passcode" required>
    <p><input name="submit" type="submit" value="Register"></p>
    </form>
    eof;
}
function Admin(){
    // session_start();
    // if($_SESSION['user']){
    //     header("Location:./index.php?q=dash");
    //     exit;
    // }
    [$usr,$name,$passcode] = isUsr();
    if($usr){
        myheader("./index.php?q=dash&name=$name&passcode=$passcode");
        exit;
    }
    echo<<<eof
    <h3>Login</h3>
    <form action="./index.php?q=check" method="POST">
    <p>user</p>
    <input type="text" name="name" required>
    <p>password</p>
    <input type="password" name="passcode" required>
    <button type="submit">Login</button>
    </form>
    eof;
}
function Check(){
    // $name=isset($_POST["name"])?htmlspecialchars($_POST["name"],ENT_QUOTES,"utf-8") : "";
    // $passcode=isset($_POST["password"])?htmlspecialchars($_POST["password"],ENT_QUOTES,"utf-8") : "";
    $name=$_POST["name"];
    $passcode=$_POST["passcode"];
    if($name==""){
        myheader("./index.php");
        exit;
    }
    if($passcode==""){
        myheader("./index.php");
        exit;
    }
    $db=new sqler();
    $q="select t1,t2,id from user where t1='$name' and t2='$passcode'";
    $db->Que($q);
    $res=$db->Sel();
    if($name==$res[0]&&$passcode==$res[1]){
        // allow Login
        // session_start();
        // session_regenerate_id(true);
        // $_SESSION['user'] = $name;
        // $_SESSION['userId']=$res[2];
        $u="./index.php?q=dash&name=$name&passcode=$passcode";
        myheader($u);
        // header("Location:./index.php?q=dash&name=$name&passcode=$passcode");
    }else{
        myheader("./index.php");
        exit;
    }
}
function myheader($u){
    echo<<<eof
    <script>
    window.location.href="$u"
    </script>
    eof;
}
function Dash(){
    // session_start();
    // echo session_id();
    // if(!$_SESSION['user']){
    //     header("Location:./index.php");
    //     exit;
    // }
    // $name=$_SESSION["user"];
    [$usr,$name,$passcode] = isUsr();
    if(!$usr){
        myheader("./index.php");
        exit;
    }
    if(@$_POST['submit']){
        $category=empty($_POST['t1'])?null:$_POST['t1'];
        $title=$_POST['t2'];
        $text=empty($_POST['t3'])?null:$_POST['t3'];
        $db=new sqler();
        $q="select id from user where t1='$name'";
        $db->Que($q);
        $res=$db->Sel();
        $i1=$res[0];
        $q="insert into log (t1,t2,t3,i1) values(:t1,:t2,:t3,:i1)";
        $db->Que($q);
        $b=array(":t1",":t2",":t3",":i1");
        $v=array($category,$title,$text,$i1);
        $db->Bind($b);
        $db->Val($v);
        $db->Ins();
    }
    echo<<<eof
    <h1>Hi $name!</h1>
    <nav>
    </nav>
    <form method="post" action="">
    <p>Category</p>
    <p><input type="text" name="t1" size="40"></p>
    <p>Title</p>
    <p><input type="text" name="t2" size="40" required></p>
    <p>Text</p>
    <p><textarea name="t3" rows="8" cols="40" maxlength="255"></textarea></p>
    <p><input name="submit" type="submit" value="Send"></p>
    </form>
    eof;
    ls_usr($name);
}
//Logout
// function Logout(){
    // session_start();
    // $_SESSION["user"]=false;
    // $_SESSION["userId"]=false;
    // header("location:./index.php");
// }
//Diary
function ls_all(){
    $db = new sqler();
    $q = <<<SQL
    SELECT log.*,user.t1 AS usr
    FROM log
    INNER JOIN user
    ON user.id = log.i1
    ORDER BY id DESC
    SQL;
    $db->Que($q);
    Diary($db->All());
}
function ls_usr($name){
    $db = new sqler();
    $q=<<<SQL
    SELECT log.*,user.t1 AS usr
    FROM log
    INNER JOIN user
    ON user.id = log.i1
    WHERE usr = '$name' 
    ORDER BY log.id DESC
    SQL;
    $db->Que($q);
    Diary($db->All());
}
function Diary($res){
    while ($row = $res->fetchArray()) {
        echo<<<eof
        <article>
            <small>
                {$row["usr"]}
            </small>
            <small>
                {$row["t1"]}
            </small>
            <small>
                {$row["d1"]}
            </small>
            <p>
                {$row["t2"]}
            </p>
        eof;
        if($row["t4"]){
            echo<<<eof
            <details>
                <summary>more</summary>
                <a href="./index.php?q={$row["t4"]}">
                    {$row["t4"]}
                </a>
            </details>
            eof;
        }else if($row["t3"]){
            echo<<<eof
            <details>
                <summary>more</summary>
                <p>
                    {$row["t3"]}
                </p>
            </details>
            eof;
        }
        echo<<<eof
        </article>
        eof;
    }
}
//list directory
function lsDir(){
    $posts = scandir("posts");

    foreach ($posts as $post) {
        if ($post !== "." && $post !== "..") {
            echo "<li><a href='posts/$post'>$post</a></li>";
        }
    }
}
function Calendar_selector(){
    echo<<<eof
    <form action='' method='post'><select name='year'>
    eof;
    $y=2023;
    while($y<2040){
        if($y==date('Y')){
            echo "<option value='$y' selected>$y</option>";
        }else{
            echo "<option value='$y'>$y</option>";
        }
        $y++;
    }
    echo<<<eof
    </select><select name="month">
    eof;
    $cnt=1;
    while($cnt<13){
        if($cnt==date("n")){
            echo "<option value='$cnt' selected>$cnt</option>";
        }else{
            echo "<option value='$cnt'>$cnt</option>";
        }
        $cnt++;
    }
    echo<<<eof
    </select><input type="submit" value="make"></form>
    eof;
    $year=$_POST["year"];
    $month=$_POST["month"];
    if($year==null){
        $year=date("Y");
    }
    if($month==null){
        $month=date("n");
    }
    Calendar($m=$month,$y=$year);
}
function Calendar($m=null,$y=null){
    if(!(bool)$m){
        $m=date("m");
    }
    if(!(bool)$y){
        $y=date("Y");
    }
    echo<<<eof
    <br><font size=4><b>$y.$m</b></font><br>
    <table border='1'<tr>
    <td bgcolor='#ffaaaa' align='center' width='35'><font size='4'><b>S</b></font></td>
    <td bgcolor='#ffaadd' align='center' width='35'><font size='4'><b>M</b></font></td>
    <td bgcolor='#ffaadd' align='center' width='35'><font size='4'><b>T</b></font></td>
    <td bgcolor='#ffaadd' align='center' width='35'><font size='4'><b>W</b></font></td>
    <td bgcolor='#ffaadd' align='center' width='35'><font size='4'><b>T</b></font></td>
    <td bgcolor='#ffaadd' align='center' width='35'><font size='4'><b>F</b></font></td>
    <td bgcolor='#ddddff' align='center' width='35'><font size='4'><b>S</b></font></td>
    eof;
    $wtop=date('w',mktime(0,0,0,$m,1,$y));
    echo "<tr>";
    for($dot1=0;$dot1<$wtop;$dot1++){
        echo "<td align='center'>x</td>";
    }
    for($d=1;checkdate($m,$d,$y);$d++){
        if(($d+$wtop)%7==1){
            echo "<tr><td align='center'><font size='4' color='red' id=$d><b>$d</b></font></td>";
        }else if(($d+$wtop)%7==0){
            echo "<td align='center'><font size='4' color='blue' id=$d><b>$d</b></font></td></tr>";
        }else{
            echo "<td align='center'><font size='4' id=$d><b>$d</b></font></td>";
        }
        if($y.$m.$d==date("Ymd")){
            echo "<script>document.getElementById('$d').color='yellow';</script>";
        }
    }
    $wend=date('w',mktime(0,0,0,$m,$d,$y));
    if($wend!=0){
        for($dot2=0;$dot2<7-$wend;$dot2++){
            echo "<td align='center'>x</td>";
        }
    }
    echo "</tr></table>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #9999ff; }
    header { background-color: #6633cc; color: #000; padding: 1em; text-align: center; }
    main { width:800px; margin:auto; }
    footer { background-color: #6633cc; color: #000; text-align: center; padding: 1em; position: fixed; width: 100%; bottom: 0; }
    ul { list-style-type: none; padding: 0; }
    ul li { margin-bottom: 1em; }
    a { text-decoration: none; color: #6633cc; }
    a:hover { text-decoration: underline; }
    article { border:solid 1px blue; margin:5px auto;}
    article p {margin-left:5px;}
    details {margin-left:5px;}
    </style>
</head>
<body>
    <header>
        <h1>maf</h1>
    </header>
    <main>
        <h2>Contents</h2>
        <?php
        error_reporting(E_ERROR | E_PARSE);
        Handler();
        ?>
    </main>
</body>
</html>