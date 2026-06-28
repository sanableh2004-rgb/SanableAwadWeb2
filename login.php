<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// إذا كان المستخدم مسجل دخول، إعادة توجيه للصفحة الرئيسية
if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'تسجيل الدخول';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            redirect('index.php');
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">
                <h2 class="text-center text-primary fw-bold mb-4"><?php echo SITE_NAME; ?></h2>
                <p class="text-center text-muted mb-4">تسجيل الدخول إلى النظام</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- اسم المستخدم -->
                    <div class="mb-3">
                        <label for="username" class="form-label">اسم المستخدم أو البريد الإلكتروني</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   placeholder="أدخل اسم المستخدم أو البريد الإلكتروني" required>
                        </div>
                    </div>

                    <!-- كلمة المرور -->
                    <div class="mb-4">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="أدخل كلمة المرور" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                        </button>
                    </div>

                    <div class="text-center text-muted">
                        <small>للاختبار: admin / password</small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.min-vh-100 {
    min-height: 100vh;
}
.card {
    border-radius: 20px;
}
.input-group-text {
    background-color: #f8f9fa;
    border-color: #e9ecef;
}
</style>
