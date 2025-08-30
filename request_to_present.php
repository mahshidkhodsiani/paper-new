<?php
/**
 * request_to_present.php
 * - Auth required (login/register/logout)
 * - Requester + Presenter are in separate "card" sections
 * - Competition toggle (Yes/No) -> shows Competition Details + optional Competition PDF
 * - Requester extra info (affiliation, phone) is stored & emailed
 * - Optional Paper PDF upload
 * - Email includes ONLY the raw custom message (no "template" header)
 * - Accept/Decline links, optional ICS, optional webhook, cron reminders
 *
 * ===== Fresh DB =====
 * CREATE TABLE IF NOT EXISTS users (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   name VARCHAR(120) NOT NULL,
 *   email VARCHAR(255) NOT NULL UNIQUE,
 *   password_hash VARCHAR(255) NOT NULL,
 *   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 *
 * CREATE TABLE IF NOT EXISTS request_to_present (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   user_id INT NOT NULL,
 *   requester_name VARCHAR(120) NOT NULL,
 *   requester_email VARCHAR(255) NOT NULL,
 *   requester_affiliation VARCHAR(255) DEFAULT NULL,
 *   requester_phone VARCHAR(64) DEFAULT NULL,
 *   presenter_name VARCHAR(120) NOT NULL,
 *   presenter_email VARCHAR(255) NOT NULL,
 *   presenter_affiliation VARCHAR(255) DEFAULT NULL,
 *   paper_title VARCHAR(255) NOT NULL,
 *   paper_link VARCHAR(1024) DEFAULT NULL,
 *   pdf_path VARCHAR(1024) DEFAULT NULL,
 *   message TEXT,
 *   preferred_date DATE DEFAULT NULL,
 *   preferred_time VARCHAR(10) DEFAULT NULL,
 *   alternate_date DATE DEFAULT NULL,
 *   alternate_time VARCHAR(10) DEFAULT NULL,
 *   timezone VARCHAR(64) DEFAULT NULL,
 *   duration_minutes INT DEFAULT NULL,
 *   cc_emails VARCHAR(1024) DEFAULT NULL,
 *   status ENUM('pending','accepted','declined') DEFAULT 'pending',
 *   manage_token CHAR(64) NOT NULL,
 *   reminder_date DATE DEFAULT NULL,
 *   reminder_sent TINYINT(1) DEFAULT 0,
 *   comp_details TEXT DEFAULT NULL,
 *   comp_pdf_path VARCHAR(1024) DEFAULT NULL,
 *   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *   FOREIGN KEY (user_id) REFERENCES users(id)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */

session_start();

$config = array(
  // ==== DB ====
  'db_dsn'  => 'mysql:host=127.0.0.1;port=3306;dbname=your_db;charset=utf8mb4',
  'db_user' => 'your_user',
  'db_pass' => 'your_password',

  // ==== UPLOADS ====
  'upload_dir'     => __DIR__ . '/uploads/requests',
  'max_file_bytes' => 15 * 1024 * 1024,
  'allowed_mimes'  => array('application/pdf'),

  // ==== EMAIL ====
  'send_email'     => true,
  'from_email'     => 'no-reply@yourdomain.com',
  'org_name'       => 'Your Lab / Department',
  'base_url'       => null, // e.g., 'http://localhost/myproject/request_to_present.php'

  // ==== WEBHOOK ====
  'webhook_url'    => null,
  'webhook_secret' => null,

  // ==== CRON ====
  'cron_secret'         => 'change-this-secret',
  'reminder_lead_days'  => 3,

  // ==== SECURITY ====
  'require_https'  => false,
);

if ($config['require_https'] && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
  header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit;
}

// -------- Helpers --------
function csrf_token() { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function validate_csrf($t) { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t === null ? '' : $t); }
function h($s) { return htmlspecialchars($s === null ? '' : $s, ENT_QUOTES, 'UTF-8'); }
function is_valid_email($e) { return filter_var($e, FILTER_VALIDATE_EMAIL); }
function trim_or_null($s) { $s = trim((string)$s); return $s === '' ? null : $s; }
function parse_cc_emails($s) {
  $s = trim((string)$s);
  if ($s === '') return null;
  $parts = array_map('trim', explode(',', $s));
  $valid = array();
  foreach ($parts as $e) {
    if (filter_var($e, FILTER_VALIDATE_EMAIL)) $valid[] = $e;
  }
  return $valid ? implode(',', $valid) : null;
}
function ensure_upload_dir($dir) {
  if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
  if (!is_dir($dir) || !is_writable($dir)) throw new RuntimeException("Upload directory not writable: $dir");
}
function base_url() {
  global $config;
  if (!empty($config['base_url'])) return $config['base_url'];
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on') ? 'https' : 'http';
  return $scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
}
function script_dir_url() {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on') ? 'https' : 'http';
  $dir = rtrim(str_replace('\\','/', dirname($_SERVER['PHP_SELF'])), '/');
  return $scheme.'://'.$_SERVER['HTTP_HOST'].$dir;
}
function file_public_url($abs_path) {
  $abs = str_replace('\\','/',$abs_path);
  $root = str_replace('\\','/',__DIR__);
  if (strpos($abs, $root) === 0) {
    $rel = substr($abs, strlen($root)); // like /uploads/requests/...
    $rel = str_replace(' ', '%20', $rel);
    return rtrim(script_dir_url(),'/') . $rel;
  }
  return null;
}
function build_manage_link($token,$action){
  $u=base_url(); $sep=(strpos($u,'?')!==false)?'&':'?'; return $u.$sep.'token='.urlencode($token).'&action='.urlencode($action);
}
function dt_from_parts($date,$time,$tz){
  if(!$date||!$time) return null;
  try{ return new DateTime($date.' '.$time.':00',$tz?new DateTimeZone($tz):new DateTimeZone('UTC')); }catch(Exception $e){ return null; }
}
function make_ics($uid,$summary,$desc,$location,$startDt,$durationMinutes,$organizerEmail){
  $dtStart=clone $startDt; $dtEnd=clone $startDt; $dtEnd->modify('+'.(int)$durationMinutes.' minutes');
  $fmt='Ymd\THis\Z'; $now=new DateTime('now',new DateTimeZone('UTC'));
  $ics=array('BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//YourOrg//PresentRequest//EN','CALSCALE:GREGORIAN','METHOD:REQUEST','BEGIN:VEVENT',
        'UID:'.$uid,'DTSTAMP:'.$now->format($fmt),'DTSTART:'.$dtStart->setTimezone(new DateTimeZone('UTC'))->format($fmt),
        'DTEND:'.$dtEnd->setTimezone(new DateTimeZone('UTC'))->format($fmt),
        $organizerEmail?'ORGANIZER:mailto:'.$organizerEmail:null,
        'SUMMARY:'.str_replace(array("\r","\n"),' ',$summary),
        'LOCATION:'.str_replace(array("\r","\n"),' ',$location),
        'DESCRIPTION:'.preg_replace('/\r?\n/','\\n',$desc),
        'END:VEVENT','END:VCALENDAR');
  $out = array(); foreach ($ics as $x) { if ($x !== null) $out[] = $x; } return implode("\r\n", $out);
}
function send_webhook($event,$payload){
  global $config;
  if (empty($config['webhook_url'])) return;
  $ch=curl_init($config['webhook_url']); $headers=array('Content-Type: application/json');
  if (!empty($config['webhook_secret'])) $headers[]='Authorization: Bearer '.$config['webhook_secret'];
  $data=array('event'=>$event,'payload'=>$payload);
  curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>$headers,CURLOPT_POSTFIELDS=>json_encode($data),CURLOPT_TIMEOUT=>6));
  curl_exec($ch); curl_close($ch);
}
function send_mail_with_optional_ics($to,$cc,$subject,$body,$icsContent=null,$icsFilename='invite.ics'){
  global $config;
  $headers=array(); $boundary='==Multipart_Boundary_x'.bin2hex(random_bytes(8)).'x';
  if($icsContent){
    $headers[]="From: {$config['org_name']} <{$config['from_email']}>"; if($cc) $headers[]="Cc: {$cc}"; $headers[]="MIME-Version: 1.0"; $headers[]="Content-Type: multipart/mixed; boundary=\"$boundary\"";
    $msg="--$boundary\r\nContent-Type: text/plain; charset=\"UTF-8\"\r\n\r\n$body\r\n\r\n--$boundary\r\nContent-Type: text/calendar; method=REQUEST; charset=\"UTF-8\"\r\nContent-Transfer-Encoding: 8bit\r\nContent-Disposition: attachment; filename=\"$icsFilename\"\r\n\r\n$icsContent\r\n\r\n--$boundary--";
    return @mail($to,$subject,$msg,implode("\r\n",$headers));
  } else {
    $headers[]="From: {$config['org_name']} <{$config['from_email']}>"; if($cc) $headers[]="Cc: {$cc}"; $headers[]="MIME-Version: 1.0"; $headers[]="Content-Type: text/plain; charset=UTF-8";
    return @mail($to,$subject,$body,implode("\r\n",$headers));
  }
}

// -------- DB --------
try {
  $pdo = new PDO($config['db_dsn'], $config['db_user'], $config['db_pass'], array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ));
} catch (Exception $e) {
  $db_error = $e->getMessage();
}

// -------- Auth helpers --------
function current_user() { return isset($_SESSION['user']) ? $_SESSION['user'] : null; }

// -------- CRON endpoint --------
if (isset($_GET['cron'])) {
  if (($_GET['secret'] ?? '') !== $config['cron_secret']) { http_response_code(403); echo 'Forbidden'; exit; }
  if (!empty($db_error)) { echo 'DB error: '.h($db_error); exit; }
  $today = new DateTime('today', new DateTimeZone('UTC'));
  $stmt = $pdo->prepare("SELECT * FROM request_to_present WHERE reminder_sent = 0 AND preferred_date IS NOT NULL AND status IN ('pending','accepted')");
  $stmt->execute();
  $count = 0;
  while ($row = $stmt->fetch()) {
    $lead = (int)$config['reminder_lead_days'];
    $remDate = new DateTime($row['preferred_date'], new DateTimeZone('UTC'));
    $remDate->modify("-{$lead} days");
    if ($today >= $remDate) {
      $subject = "[{$config['org_name']}] Reminder: Upcoming presentation request – " . $row['paper_title'];
      $lines = array(
        "Hello {$row['presenter_name']},",
        "",
        "This is a friendly reminder about your requested presentation.",
        "Paper: {$row['paper_title']}",
      );
      if (!empty($row['preferred_time']) && !empty($row['timezone'])) {
        $lines[] = "When: {$row['preferred_date']} {$row['preferred_time']} ({$row['timezone']})";
      } elseif (!empty($row['preferred_date'])) {
        $lines[] = "Date: {$row['preferred_date']}";
      }
      $lines[] = "";
      $lines[] = "Manage: Accept " . build_manage_link($row['manage_token'], 'accept');
      $lines[] = "        Decline " . build_manage_link($row['manage_token'], 'decline');
      $body = implode("\r\n", $lines);
      send_mail_with_optional_ics($row['presenter_email'], $row['cc_emails'], $subject, $body, null);
      $upd = $pdo->prepare("UPDATE request_to_present SET reminder_sent = 1 WHERE id = :id");
      $upd->execute(array(':id' => $row['id']));
      $count++;
    }
  }
  echo "Reminders sent: $count";
  exit;
}

// -------- Accept/Decline endpoint --------
$status_message = null;
if (isset($_GET['token'], $_GET['action']) && empty($_POST)) {
  if (!empty($db_error)) {
    $status_message = 'Database connection failed: ' . h($db_error);
  } else {
    $token = $_GET['token'];
    $action = $_GET['action'];
    if (!in_array($action, array('accept','decline'), true)) {
      $status_message = 'Invalid action.';
    } else {
      $stmt = $pdo->prepare("SELECT * FROM request_to_present WHERE manage_token = :t LIMIT 1");
      $stmt->execute(array(':t' => $token));
      $row = $stmt->fetch();
      if (!$row) {
        $status_message = 'Invalid or expired link.';
      } else {
        $newStatus = ($action === 'accept') ? 'accepted' : 'declined';
        $upd = $pdo->prepare("UPDATE request_to_present SET status = :s WHERE id = :id");
        $upd->execute(array(':s' => $newStatus, ':id' => $row['id']));
        $status_message = "Thank you! You have {$newStatus} the request for “" . h($row['paper_title']) . "”.";
        send_webhook('status_changed', array(
          'id' => $row['id'],
          'status' => $newStatus,
          'paper_title' => $row['paper_title'],
          'presenter_email' => $row['presenter_email'],
        ));
      }
    }
  }
}

// -------- Auth flows --------
$auth_error = null;
if (isset($_GET['auth']) && empty($_POST)) {
  if ($_GET['auth'] === 'logout') {
    unset($_SESSION['user']);
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
  }
}

if (isset($_GET['auth']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
    $auth_error = 'Invalid CSRF token.';
  } else {
    $auth = $_GET['auth'];
    if ($auth === 'register') {
      $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
      $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
      $pass = (string)(isset($_POST['password']) ? $_POST['password'] : '');
      if ($name === '' || !is_valid_email($email) || strlen($pass) < 6) {
        $auth_error = 'Please enter a name, valid email, and a password (min 6 chars).';
      } else if (!empty($db_error)) {
        $auth_error = 'Database error: ' . h($db_error);
      } else {
        try {
          $hash = password_hash($pass, PASSWORD_DEFAULT);
          $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (:n,:e,:p)");
          $stmt->execute(array(':n'=>$name, ':e'=>$email, ':p'=>$hash));
          $_SESSION['user'] = array('id'=>$pdo->lastInsertId(), 'name'=>$name, 'email'=>$email);
          header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
          exit;
        } catch (Exception $e) {
          $auth_error = 'Could not create account. If the email already exists, try logging in.';
        }
      }
    } elseif ($auth === 'login') {
      $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
      $pass = (string)(isset($_POST['password']) ? $_POST['password'] : '');
      if (!is_valid_email($email) || $pass === '') {
        $auth_error = 'Enter a valid email and password.';
      } else if (!empty($db_error)) {
        $auth_error = 'Database error: ' . h($db_error);
      } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :e LIMIT 1");
        $stmt->execute(array(':e'=>$email));
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password_hash'])) {
          $_SESSION['user'] = array('id'=>$user['id'], 'name'=>$user['name'], 'email'=>$user['email']);
          header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
          exit;
        } else {
          $auth_error = 'Invalid email or password.';
        }
      }
    }
  }
}

// -------- Handle POST (create request) --------
$success = false;
$errors = array();

if (!isset($_GET['auth']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = current_user();
  if (!$user) {
    $errors[] = 'You must be signed in to submit a request.';
  }
  if (!validate_csrf(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
    $errors[] = 'Invalid CSRF token. Please refresh and try again.';
  }

  // Requester from account + extra per-request fields
  $requester_name   = $user ? $user['name'] : '';
  $requester_email  = $user ? $user['email'] : '';
  $requester_aff    = trim_or_null(isset($_POST['requester_affiliation']) ? $_POST['requester_affiliation'] : '');
  $requester_phone  = trim_or_null(isset($_POST['requester_phone']) ? $_POST['requester_phone'] : '');

  // Form fields
  $presenter_name   = trim(isset($_POST['presenter_name']) ? $_POST['presenter_name'] : '');
  $presenter_email  = trim(isset($_POST['presenter_email']) ? $_POST['presenter_email'] : '');
  $presenter_aff    = trim_or_null(isset($_POST['affiliation']) ? $_POST['affiliation'] : '');
  $paper_title      = trim(isset($_POST['paper_title']) ? $_POST['paper_title'] : '');
  $paper_link       = trim_or_null(isset($_POST['paper_link']) ? $_POST['paper_link'] : '');
  $message          = trim_or_null(isset($_POST['message']) ? $_POST['message'] : '');

  $preferred_date   = trim_or_null(isset($_POST['preferred_date']) ? $_POST['preferred_date'] : '');
  $preferred_time   = trim_or_null(isset($_POST['preferred_time']) ? $_POST['preferred_time'] : '');
  $alternate_date   = trim_or_null(isset($_POST['alternate_date']) ? $_POST['alternate_date'] : '');
  $alternate_time   = trim_or_null(isset($_POST['alternate_time']) ? $_POST['alternate_time'] : '');
  $timezone_str     = trim_or_null(isset($_POST['timezone']) ? $_POST['timezone'] : '');

  $duration_minutes = trim_or_null(isset($_POST['duration_minutes']) ? $_POST['duration_minutes'] : '');
  $cc_emails        = parse_cc_emails(isset($_POST['cc_emails']) ? $_POST['cc_emails'] : '');
  $consent          = isset($_POST['consent']) ? true : false;

  // Competition toggle
  $include_comp     = isset($_POST['include_comp']) && $_POST['include_comp'] === '1';

  // Competition
  $comp_details     = $include_comp ? trim_or_null(isset($_POST['comp_details']) ? $_POST['comp_details'] : '') : null;

  // Validation
  if ($presenter_name==='') $errors[]='Presenter name is required.';
  if (!is_valid_email($presenter_email)) $errors[]='A valid presenter email is required.';
  if ($paper_title==='') $errors[]='Paper title is required.';
  if ($paper_link && !filter_var($paper_link, FILTER_VALIDATE_URL)) $errors[]='Paper link must be a valid URL.';
  if ($preferred_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$preferred_date)) $errors[]='Preferred date must be YYYY-MM-DD.';
  if ($preferred_time && !preg_match('/^\d{2}:\d{2}$/',$preferred_time)) $errors[]='Preferred time must be HH:MM.';
  if ($alternate_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$alternate_date)) $errors[]='Alternate date must be YYYY-MM-DD.';
  if ($alternate_time && !preg_match('/^\d{2}:\d{2}$/',$alternate_time)) $errors[]='Alternate time must be HH:MM.';
  if ($duration_minutes && !ctype_digit($duration_minutes)) $errors[]='Duration must be a whole number of minutes.';
  if (!$consent) $errors[]='You must consent to store this information.';

  // File upload (paper PDF - optional)
  $pdf_path = null;
  if (!empty($_FILES['paper_pdf']['name'])) {
    try {
      ensure_upload_dir($config['upload_dir']);
      $f=$_FILES['paper_pdf'];
      if($f['error']!==UPLOAD_ERR_OK) throw new RuntimeException('Upload error code: '.$f['error']);
      if($f['size']>$config['max_file_bytes']) throw new RuntimeException('File too large. Max 15 MB.');
      $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($f['tmp_name']);
      if(!in_array($mime,$config['allowed_mimes'],true)) throw new RuntimeException('Only PDF files are allowed.');
      $ext='.pdf'; $safeBase=bin2hex(random_bytes(8));
      $dest=$config['upload_dir'].'/'.date('Ymd_His').'_paper_'.$safeBase.$ext;
      if(!move_uploaded_file($f['tmp_name'],$dest)) throw new RuntimeException('Failed to move uploaded file.');
      $pdf_path=$dest;
    } catch (Exception $e) { $errors[]='PDF upload failed (paper): '.$e->getMessage(); }
  }

  // File upload (competition PDF - optional, only if include_comp)
  $comp_pdf_path = null;
  if ($include_comp && !empty($_FILES['comp_pdf']['name'])) {
    try {
      ensure_upload_dir($config['upload_dir']);
      $f=$_FILES['comp_pdf'];
      if($f['error']!==UPLOAD_ERR_OK) throw new RuntimeException('Upload error code: '.$f['error']);
      if($f['size']>$config['max_file_bytes']) throw new RuntimeException('File too large. Max 15 MB.');
      $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($f['tmp_name']);
      if(!in_array($mime,$config['allowed_mimes'],true)) throw new RuntimeException('Only PDF files are allowed.');
      $ext='.pdf'; $safeBase=bin2hex(random_bytes(8));
      $dest=$config['upload_dir'].'/'.date('Ymd_His').'_comp_'.$safeBase.$ext;
      if(!move_uploaded_file($f['tmp_name'],$dest)) throw new RuntimeException('Failed to move uploaded file.');
      $comp_pdf_path=$dest;
    } catch (Exception $e) { $errors[]='PDF upload failed (competition): '.$e->getMessage(); }
  }

  // Save
  if (empty($errors) && empty($db_error)) {
    try {
      $token = bin2hex(random_bytes(32));
      $reminder_date = null;
      if ($preferred_date) { $dt = new DateTime($preferred_date, new DateTimeZone('UTC')); $dt->modify('-'.(int)$config['reminder_lead_days'].' days'); $reminder_date = $dt->format('Y-m-d'); }
      $stmt = $pdo->prepare("
        INSERT INTO request_to_present
          (user_id, requester_name, requester_email, requester_affiliation, requester_phone,
           presenter_name, presenter_email, presenter_affiliation,
           paper_title, paper_link, pdf_path, message,
           preferred_date, preferred_time, alternate_date, alternate_time, timezone,
           duration_minutes, cc_emails, status, manage_token, reminder_date,
           comp_details, comp_pdf_path)
        VALUES
          (:user_id, :requester_name, :requester_email, :requester_affiliation, :requester_phone,
           :presenter_name, :presenter_email, :presenter_affiliation,
           :paper_title, :paper_link, :pdf_path, :message,
           :preferred_date, :preferred_time, :alternate_date, :alternate_time, :timezone,
           :duration_minutes, :cc_emails, 'pending', :manage_token, :reminder_date,
           :comp_details, :comp_pdf_path)
      ");
      $stmt->execute(array(
        ':user_id'=>$user['id'],
        ':requester_name'=>$requester_name, ':requester_email'=>$requester_email,
        ':requester_affiliation'=>$requester_aff, ':requester_phone'=>$requester_phone,
        ':presenter_name'=>$presenter_name, ':presenter_email'=>$presenter_email,
        ':presenter_affiliation'=>$presenter_aff,
        ':paper_title'=>$paper_title, ':paper_link'=>$paper_link, ':pdf_path'=>$pdf_path,
        ':message'=>$message,
        ':preferred_date'=>$preferred_date, ':preferred_time'=>$preferred_time,
        ':alternate_date'=>$alternate_date, ':alternate_time'=>$alternate_time,
        ':timezone'=>$timezone_str, ':duration_minutes'=>$duration_minutes,
        ':cc_emails'=>$cc_emails, ':manage_token'=>$token, ':reminder_date'=>$reminder_date,
        ':comp_details'=>$comp_details, ':comp_pdf_path'=>$comp_pdf_path
      ));
      $insert_id = $pdo->lastInsertId();
      $success = true;

      // Email
      if ($config['send_email']) {
        $subject = "[{$config['org_name']}] Request to present: " . $paper_title;
        $acceptLink  = build_manage_link($token,'accept');
        $declineLink = build_manage_link($token,'decline');

        $lines = array();
        $lines[] = "Hello {$presenter_name},";
        $lines[] = "";
        $lines[] = "{$requester_name} ({$requester_email}) is inviting you to present a paper.";
        if ($requester_aff) $lines[] = "Requester affiliation: {$requester_aff}";
        if ($requester_phone) $lines[] = "Requester phone: {$requester_phone}";
        $lines[] = "";
        $lines[] = "Paper: {$paper_title}";
        if ($paper_link)     $lines[] = "Link: {$paper_link}";
        if ($presenter_aff)  $lines[] = "Presenter affiliation: {$presenter_aff}";
        $lines[] = "";
        $lines[] = "Preferred: " . ($preferred_date ? $preferred_date : '—') . ' ' . ($preferred_time ? $preferred_time : '') . ($timezone_str ? " ({$timezone_str})" : '');
        $lines[] = "Alternate: " . ($alternate_date ? $alternate_date : '—') . ' ' . ($alternate_time ? $alternate_time : '');
        if ($duration_minutes) $lines[] = "Duration (min): {$duration_minutes}";
        // Only the raw custom message — no template header
        if ($message !== null && $message !== '') {
          $lines[] = "";
          $lines[] = $message;
          $lines[] = "";
        }
        // Competition block only if selected and content present
        if ($include_comp && ($comp_details || $comp_pdf_path)) {
          $lines[] = "— Competition —";
          if ($comp_details) $lines[] = $comp_details;
          if ($comp_pdf_path) {
            $fileUrl = file_public_url($comp_pdf_path);
            $lines[] = "Competition PDF: " . ($fileUrl ? $fileUrl : "(stored on server)");
          }
          $lines[] = "";
        }
        $lines[] = "Respond quickly:";
        $lines[] = "Accept:  {$acceptLink}";
        $lines[] = "Decline: {$declineLink}";

        $body = implode("\r\n", $lines);

        // ICS
        $icsContent = null;
        if ($preferred_date && $preferred_time && $duration_minutes) {
          $tz = $timezone_str ? new DateTimeZone($timezone_str) : new DateTimeZone('UTC');
          $start = dt_from_parts($preferred_date,$preferred_time,$tz);
          if ($start) {
            $uid  = 'rtp-'.$insert_id.'-'.bin2hex(random_bytes(6)).'@'.parse_url(base_url(), PHP_URL_HOST);
            $descParts = array();
            if ($message) $descParts[] = $message;
            $descParts[] = "Paper: {$paper_title}";
            $descParts[] = "Requester: {$requester_name} ({$requester_email})";
            if ($requester_aff) $descParts[] = "Requester affiliation: {$requester_aff}";
            if ($requester_phone) $descParts[] = "Requester phone: {$requester_phone}";
            if ($include_comp && $comp_details) $descParts[] = "Competition: ".$comp_details;
            $desc = implode("\\n", $descParts);
            $icsContent = make_ics($uid, "Presentation: {$paper_title}", $desc, $config['org_name'], $start, (int)$duration_minutes, $requester_email);
          }
        }
        send_mail_with_optional_ics($presenter_email, $cc_emails, $subject, $body, $icsContent);
      }

      // Webhook
      send_webhook('created',array(
        'id'=>(int)$insert_id,'paper_title'=>$paper_title,'presenter_email'=>$presenter_email,
        'preferred_date'=>$preferred_date,'preferred_time'=>$preferred_time,'timezone'=>$timezone_str,
        'status'=>'pending','user_id'=>$user['id'],'include_comp'=>$include_comp
      ));

    } catch (Exception $e) {
      $errors[] = 'Database error: ' . $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Request to Present (Sign-in Required)</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f6f7fb; margin:0; }
    .container { max-width: 1000px; margin: 32px auto; background:white; padding:24px; border-radius:16px; box-shadow:0 6px 24px rgba(0,0,0,0.08); }
    h1 { margin-top:0; font-size:24px; }
    .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px,1fr)); gap:16px; }
    .cards-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(340px,1fr)); gap:20px; align-items:start; }
    .card { background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.04); }
    .card h2 { margin:0 0 8px 0; font-size:18px; }
    .card .sub { color:#6b7280; font-size:12px; margin-bottom:8px; }
    label { display:block; font-weight:600; margin:6px 0; }
    input[type="text"], input[type="email"], input[type="url"], input[type="date"], input[type="time"], input[type="number"], textarea, select, input[type="password"], input[type="file"] {
      width:100%; padding:10px 12px; border:1px solid #e1e5ee; border-radius:10px; box-sizing:border-box; background:#fbfcff;
    }
    textarea { min-height:140px; }
    .help { font-size:12px; color:#6b7280; }
    .errors { background:#fff1f2; color:#b91c1c; border:1px solid #fecaca; padding:12px; border-radius:12px; margin-bottom:16px; }
    .success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:12px; border-radius:12px; margin-bottom:16px; }
    .btn { appearance:none; border:0; background:#111827; color:white; padding:12px 16px; border-radius:12px; cursor:pointer; font-weight:600; }
    .req:after { content:" *"; color:#ef4444; }
    .section-title { font-size:18px; margin-top:12px; }
    .status { background:#eef2ff; border:1px solid #c7d2fe; padding:12px; border-radius:12px; margin-bottom:16px; }
    .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .muted { color:#6b7280; font-size:14px; }
    a { color:#0f62fe; text-decoration:none; }
    a:hover { text-decoration:underline; }
    .hidden { display:none; }
  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div><?php $org = isset($config['org_name']) ? trim($config['org_name']) : ''; if ($org !== '' && $org !== 'Your Lab / Department') { echo '<strong>'.h($org).'</strong>'; } ?></div>
      <div>
        <?php if (current_user()): ?>
          <span class="muted">Signed in as <?php echo h($_SESSION['user']['name']); ?> (<?php echo h($_SESSION['user']['email']); ?>)</span>
          &nbsp;·&nbsp;<a href="?auth=logout">Sign out</a>
        <?php else: ?>
          <a href="?auth=login">Sign in</a> · <a href="?auth=register">Create account</a>
        <?php endif; ?>
      </div>
    </div>

    <h1>Request to Present</h1>
    <p class="help">You must be signed in to submit a request. Fields marked with <span style="color:#ef4444">*</span> are required.</p>

    <?php if (isset($status_message)): ?>
      <div class="status"><?php echo $status_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($db_error)): ?>
      <div class="errors"><strong>Database connection failed.</strong><br><?php echo h($db_error); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['auth']) && in_array($_GET['auth'], array('login','register'))): ?>
      <?php if (!empty($auth_error)): ?>
        <div class="errors"><?php echo h($auth_error); ?></div>
      <?php endif; ?>

      <?php if ($_GET['auth'] === 'login'): ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
          <div class="section-title">Sign in</div>
          <div class="grid">
            <div>
              <label class="req" for="email">Email</label>
              <input type="email" id="email" name="email" required>
            </div>
            <div>
              <label class="req" for="password">Password</label>
              <input type="password" id="password" name="password" required>
            </div>
          </div>
          <div style="margin-top:16px;">
            <button class="btn" type="submit">Sign in</button>
            <span class="help">No account? <a href="?auth=register">Create one</a>.</span>
          </div>
        </form>
      <?php else: ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">
          <div class="section-title">Create account</div>
          <div class="grid">
            <div>
              <label class="req" for="name">Full name</label>
              <input type="text" id="name" name="name" required>
            </div>
            <div>
              <label class="req" for="email">Email</label>
              <input type="email" id="email" name="email" required>
            </div>
            <div>
              <label class="req" for="password">Password</label>
              <input type="password" id="password" name="password" required>
              <div class="help">At least 6 characters.</div>
            </div>
          </div>
          <div style="margin-top:16px;">
            <button class="btn" type="submit">Create account</button>
            <span class="help">Already have one? <a href="?auth=login">Sign in</a>.</span>
          </div>
        </form>
      <?php endif; ?>

    <?php else: ?>

      <?php if (!empty($errors)): ?>
        <div class="errors">
          <strong>Please fix the following:</strong>
          <ul><?php foreach ($errors as $e): ?><li><?php echo h($e); ?></li><?php endforeach; ?></ul>
        </div>
      <?php elseif (!empty($success)): ?>
        <div class="success">
          <strong>Success!</strong> Your request has been recorded<?php if (!empty($config['send_email'])) echo ' and an email was sent to the presenter'; ?>.
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf" value="<?php echo h(csrf_token()); ?>">

        <div class="cards-grid">
          <div class="card">
            <h2>Requester</h2>
            <div class="sub">Details of the person making this request</div>
            <div class="grid">
              <div>
                <label>Your Name</label>
                <input type="text" value="<?php echo current_user() ? h($_SESSION['user']['name']) : ''; ?>" disabled placeholder="Will fill after sign-in">
              </div>
              <div>
                <label>Your Email</label>
                <input type="email" value="<?php echo current_user() ? h($_SESSION['user']['email']) : ''; ?>" disabled placeholder="Will fill after sign-in">
              </div>
              <div>
                <label for="requester_affiliation">Your Affiliation/Department (optional)</label>
                <input type="text" id="requester_affiliation" name="requester_affiliation" placeholder="e.g., CS Dept, University X" value="<?php echo h(isset($_POST['requester_affiliation']) ? $_POST['requester_affiliation'] : ''); ?>">
              </div>
              <div>
                <label for="requester_phone">Your Phone/Contact (optional)</label>
                <input type="text" id="requester_phone" name="requester_phone" placeholder="e.g., +1 555 123 4567" value="<?php echo h(isset($_POST['requester_phone']) ? $_POST['requester_phone'] : ''); ?>">
              </div>
            </div>
            <?php if (!current_user()): ?>
              <div class="help" style="margin-top:8px;">Please <a href="?auth=login">sign in</a> to submit.</div>
            <?php endif; ?>
          </div>

          <div class="card">
            <h2>Presenter</h2>
            <div class="sub">Who you are inviting to present</div>
            <div class="grid">
              <div>
                <label class="req" for="presenter_name">Presenter Name</label>
                <input type="text" id="presenter_name" name="presenter_name" required value="<?php echo h(isset($_POST['presenter_name']) ? $_POST['presenter_name'] : ''); ?>">
              </div>
              <div>
                <label class="req" for="presenter_email">Presenter Email</label>
                <input type="email" id="presenter_email" name="presenter_email" required value="<?php echo h(isset($_POST['presenter_email']) ? $_POST['presenter_email'] : ''); ?>">
              </div>
              <div>
                <label for="affiliation">Presenter Affiliation</label>
                <input type="text" id="affiliation" name="affiliation" placeholder="e.g., University / Company" value="<?php echo h(isset($_POST['affiliation']) ? $_POST['affiliation'] : ''); ?>">
              </div>
            </div>
          </div>
        </div>

        <div class="section-title">Paper Details</div>
        <div class="grid">
          <div style="grid-column: 1 / -1;">
            <label class="req" for="paper_title">Paper Title</label>
            <input type="text" id="paper_title" name="paper_title" required value="<?php echo h(isset($_POST['paper_title']) ? $_POST['paper_title'] : ''); ?>">
          </div>
          <div>
            <label for="paper_link">Paper Link (URL)</label>
            <input type="url" id="paper_link" name="paper_link" placeholder="https://..." value="<?php echo h(isset($_POST['paper_link']) ? $_POST['paper_link'] : ''); ?>">
          </div>
          <div>
            <label for="paper_pdf">Upload Paper PDF (optional)</label>
            <input type="file" id="paper_pdf" name="paper_pdf" accept="application/pdf">
            <div class="help">Max 15 MB. PDF only.</div>
          </div>
        </div>

        <div class="section-title">Scheduling & Availability</div>
        <div class="grid">
          <div>
            <label for="preferred_date">Preferred Date</label>
            <input type="date" id="preferred_date" name="preferred_date" value="<?php echo h(isset($_POST['preferred_date']) ? $_POST['preferred_date'] : ''); ?>">
          </div>
          <div>
            <label for="preferred_time">Preferred Time</label>
            <input type="time" id="preferred_time" name="preferred_time" value="<?php echo h(isset($_POST['preferred_time']) ? $_POST['preferred_time'] : ''); ?>">
          </div>
          <div>
            <label for="alternate_date">Alternate Date</label>
            <input type="date" id="alternate_date" name="alternate_date" value="<?php echo h(isset($_POST['alternate_date']) ? $_POST['alternate_date'] : ''); ?>">
          </div>
          <div>
            <label for="alternate_time">Alternate Time</label>
            <input type="time" id="alternate_time" name="alternate_time" value="<?php echo h(isset($_POST['alternate_time']) ? $_POST['alternate_time'] : ''); ?>">
          </div>
          <div>
            <label for="timezone">Time Zone</label>
            <select id="timezone" name="timezone">
              <?php
                $tzs = array('UTC','America/Chicago','America/New_York','America/Los_Angeles','Europe/London','Europe/Berlin','Europe/Paris','Asia/Tehran','Asia/Kolkata','Asia/Tokyo','Australia/Sydney');
                $sel = isset($_POST['timezone']) ? $_POST['timezone'] : '';
                foreach ($tzs as $tz) { $s = ($sel === $tz) ? 'selected' : ''; echo '<option value="'.h($tz).'" '.$s.'>'.h($tz).'</option>'; }
              ?>
            </select>
            <div class="help">Time zone used for the invite & emails.</div>
          </div>
          <div>
            <label for="duration_minutes">Duration (minutes)</label>
            <input type="number" id="duration_minutes" name="duration_minutes" min="10" step="5" placeholder="e.g., 30" value="<?php echo h(isset($_POST['duration_minutes']) ? $_POST['duration_minutes'] : ''); ?>">
          </div>
        </div>

        <div class="section-title">Include competition?</div>
        <div class="grid">
          <div>
            <?php $inc = isset($_POST['include_comp']) ? $_POST['include_comp'] : '0'; ?>
            <label><input type="radio" name="include_comp" value="0" <?php echo ($inc !== '1') ? 'checked' : ''; ?>> No</label>
            &nbsp;&nbsp;
            <label><input type="radio" name="include_comp" value="1" <?php echo ($inc === '1') ? 'checked' : ''; ?>> Yes</label>
          </div>
        </div>

        <div id="comp-fields" class="<?php echo ($inc === '1') ? '' : 'hidden'; ?>">
          <div class="section-title">Competition (shown because you selected "Yes")</div>
          <div class="grid">
            <div style="grid-column: 1 / -1;">
              <label for="comp_details">Competition Details</label>
              <textarea id="comp_details" name="comp_details" placeholder="Any info about the competition (e.g., theme, criteria, timeline)."><?php echo h(isset($_POST['comp_details']) ? $_POST['comp_details'] : ''); ?></textarea>
            </div>
            <div>
              <label for="comp_pdf">Competition PDF (optional)</label>
              <input type="file" id="comp_pdf" name="comp_pdf" accept="application/pdf">
              <div class="help">Upload a PDF with additional competition information (optional).</div>
            </div>
          </div>
        </div>

        <div class="section-title">Message</div>
        <div class="grid">
          <div style="grid-column: 1 / -1;">
            <label for="message">Custom Message to Presenter</label>
            <textarea id="message" name="message" placeholder="Write your message here."><?php echo h(isset($_POST['message']) ? $_POST['message'] : ''); ?></textarea>
          </div>
        </div>

        <div class="section-title">Notifications</div>
        <div class="grid">
          <div>
            <label for="cc_emails">CC (comma-separated emails)</label>
            <input type="text" id="cc_emails" name="cc_emails" placeholder="advisor@uni.edu, organizer@lab.org" value="<?php echo h(isset($_POST['cc_emails']) ? $_POST['cc_emails'] : ''); ?>">
          </div>
        </div>

        <div class="section-title">Consent</div>
        <div class="grid">
          <div style="grid-column: 1 / -1;">
            <label>
              <input type="checkbox" name="consent" value="1" <?php echo isset($_POST['consent']) ? 'checked' : ''; ?>>
              I consent to storing this information for the purposes of organizing the presentation.
            </label>
          </div>
        </div>

        <div style="margin-top:20px;">
          <button class="btn" type="submit" <?php echo current_user() ? '' : 'disabled'; ?>>Send Request</button>
          <?php if (!current_user()): ?><div class="help">Please <a href="?auth=login">sign in</a> to enable the button.</div><?php endif; ?>
        </div>
      </form>
    <?php endif; ?>
  </div>

  <script>
    // Toggle competition fields on Yes/No
    (function(){
      var radios = document.querySelectorAll('input[name="include_comp"]');
      var comp = document.getElementById('comp-fields');
      function update() {
        var val = document.querySelector('input[name="include_comp"]:checked');
        if (val && val.value === '1') comp.classList.remove('hidden');
        else comp.classList.add('hidden');
      }
      for (var i=0;i<radios.length;i++){ radios[i].addEventListener('change', update); }
      update();
    })();
  </script>
</body>
</html>
