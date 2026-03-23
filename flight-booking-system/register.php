<?php
$page_title = 'Register';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE . 'dashboard.php');
    exit;
}

$error = '';
$old = ['name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    $old = ['name'=>$name,'email'=>$email,'phone'=>$phone];

    if(empty($name) || empty($email) || empty($password)){
        $error = "Please fill all required fields";
    }
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email format";
    }
    elseif(strlen($password) < 3){
        $error = "Password must be at least 3 characters";
    }
    elseif($password !== $confirm){
        $error = "Passwords do not match";
    }
    else{
        $pdo = getDB();

        // 🔴 CHECK IF ADMIN ALREADY EXISTS
        if($role === 'admin'){
            $adminCheck = $pdo->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
            $adminCheck->execute();

            if($adminCheck->fetch()){
                $error = "Admin already exists. Only one admin allowed.";
            }
        }

        // if still no error, continue
        if(!$error){

            // check existing email
            $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
            $check->execute([$email]);

            if($check->fetch()){
                $error = "Email already exists";
            }
            else{
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users(name,email,phone,password,role)
                    VALUES(?,?,?,?,?)
                ");
                $stmt->execute([$name,$email,$phone,$hashed,$role]);

                // auto login
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;

                if($role == 'admin'){
                    header("Location: ".BASE."admin/index.php");
                }else{
                    header("Location: ".BASE."dashboard.php");
                }
                exit;
            }
        }
    }
}


require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
<div class="auth-card">
<div class="card">

<h2>Create Account</h2>

<?php if ($error): ?>
<div class="alert alert-danger">
<?php echo $error; ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="form-group">
<label>Name</label>
<input type="text" name="name" class="form-control" required
value="<?php echo htmlspecialchars($old['name']); ?>">
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" class="form-control" required
value="<?php echo htmlspecialchars($old['email']); ?>">
</div>

<div class="form-group">
<label>Phone</label>
<input type="text" name="phone" class="form-control"
value="<?php echo htmlspecialchars($old['phone']); ?>">
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="form-group">
<label>Confirm Password</label>
<input type="password" name="confirm_password" class="form-control" required>
</div>

<div class="form-group">
<label>Account Type</label>
<select name="role" class="form-control">
<option value="user">User</option>
<option value="admin">Admin</option>
</select>
</div>

<button type="submit" class="btn btn-primary btn-block">
Create Account
</button>

</form>

</div>
</div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
