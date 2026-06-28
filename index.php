<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'لوحة التحكم';

// الحصول على إحصائيات سريعة
$database = new Database();
$db = $database->getConnection();

// عدد الموظفين
$stmt = $db->prepare("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
$stmt->execute();
$totalEmployees = $stmt->fetch()['total'];

// عدد الأقسام
$stmt = $db->prepare("SELECT COUNT(*) as total FROM departments");
$stmt->execute();
$totalDepartments = $stmt->fetch()['total'];

// عدد الموظفين الحاضرين اليوم
$stmt = $db->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = CURDATE() AND status = 'present'");
$stmt->execute();
$presentToday = $stmt->fetch()['total'];

// عدد طلبات الإجازة المعلقة
$stmt = $db->prepare("SELECT COUNT(*) as total FROM leaves WHERE status = 'pending'");
$stmt->execute();
$pendingLeaves = $stmt->fetch()['total'];

// أحدث الموظفين المضافين
$stmt = $db->prepare("SELECT * FROM employees ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recentEmployees = $stmt->fetchAll();

// طلبات الإجازة المعلقة
$stmt = $db->prepare("
    SELECT l.*, e.first_name, e.last_name, e.employee_id 
    FROM leaves l 
    JOIN employees e ON l.employee_id = e.id 
    WHERE l.status = 'pending' 
    ORDER BY l.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$pendingLeaveRequests = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="main-content container py-4">

    <!-- عنوان الصفحة -->
    <div class="mb-4 border-bottom pb-2">
        <h1 class="h3 mb-1">
            <i class="fas fa-tachometer-alt me-2 text-primary"></i> لوحة التحكم
        </h1>
        <p class="text-muted mb-0">مرحباً بك في نظام إدارة الموظفين</p>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h3 class="fw-bold mb-0"><?php echo $totalEmployees; ?></h3>
                    <small class="text-muted">إجمالي الموظفين</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-2x text-success mb-2"></i>
                    <h3 class="fw-bold mb-0"><?php echo $totalDepartments; ?></h3>
                    <small class="text-muted">عدد الأقسام</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-user-check fa-2x text-info mb-2"></i>
                    <h3 class="fw-bold mb-0"><?php echo $presentToday; ?></h3>
                    <small class="text-muted">الحاضرون اليوم</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-times fa-2x text-warning mb-2"></i>
                    <h3 class="fw-bold mb-0"><?php echo $pendingLeaves; ?></h3>
                    <small class="text-muted">طلبات إجازة معلقة</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- أحدث الموظفين -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user-plus me-2"></i> أحدث الموظفين</h6>
                </div>
                <div class="card-body">
                    <?php if (count($recentEmployees) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentEmployees as $employee): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></h6>
                                        <small class="text-muted"><?php echo $employee['employee_id']; ?> - <?php echo $employee['position']; ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo formatArabicDate($employee['created_at']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="pages/employee/list.php" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">لا توجد بيانات موظفين</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- طلبات الإجازة -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i> طلبات الإجازة المعلقة</h6>
                </div>
                <div class="card-body">
                    <?php if (count($pendingLeaveRequests) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($pendingLeaveRequests as $leave): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo $leave['first_name'] . ' ' . $leave['last_name']; ?></h6>
                                        <small class="text-muted d-block"><?php echo $leave['leave_type']; ?></small>
                                        <small class="text-muted">
                                            من <?php echo formatArabicDate($leave['start_date']); ?> 
                                            إلى <?php echo formatArabicDate($leave['end_date']); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-warning text-dark"><?php echo $leave['days_count']; ?> يوم</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="pages/leave/list.php" class="btn btn-sm btn-outline-warning">عرض الكل</a>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">لا توجد طلبات معلقة</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- روابط سريعة -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-link me-2"></i> روابط سريعة</h6>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <a href="pages/employee/add.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-user-plus me-2"></i> إضافة موظف
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="pages/department/add.php" class="btn btn-outline-success w-100">
                        <i class="fas fa-building me-2"></i> إضافة قسم
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="pages/attendance/add.php" class="btn btn-outline-info w-100">
                        <i class="fas fa-clock me-2"></i> تسجيل حضور
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="pages/leave/add.php" class="btn btn-outline-warning w-100">
                        <i class="fas fa-calendar-plus me-2"></i> طلب إجازة
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
