<?php
require_once __DIR__ . '/auth.php';

$PERMISSIONS = [
    'Admin' => [
        'create_user', 'edit_user', 'delete_user', 'view_users', 'reset_password', 'manage_roles',
        'create_student', 'edit_student', 'delete_student', 'view_students', 'bulk_import_students',
        'create_course', 'edit_course', 'delete_course', 'view_courses',
        'create_subject', 'edit_subject', 'delete_subject', 'view_subjects',
        'assign_lecturer',
        'create_enrollment', 'edit_enrollment', 'delete_enrollment', 'view_enrollments',
        'view_all_attendance', 'edit_all_attendance', 'override_attendance',
        'view_all_results', 'edit_all_results', 'delete_results', 'lock_results',
        'generate_all_reports', 'view_audit_logs',
        'system_settings', 'database_backup'
    ],
    
    'Staff' => [
        'create_student', 'edit_student', 'view_students',
        'create_enrollment', 'edit_enrollment', 'view_enrollments',
        'view_courses', 'view_subjects',
        'view_all_attendance',
        'view_all_results',
        'generate_student_reports', 'generate_enrollment_reports', 'generate_attendance_reports',
        'print_transcript'
    ],
    
    'Lecturer' => [
        'view_assigned_subjects', 'view_enrolled_students',
        'mark_attendance', 'edit_own_attendance', 'view_own_attendance',
        'create_exam', 'edit_exam', 'view_exams',
        'enter_marks', 'edit_own_marks', 'view_own_results',
        'generate_subject_reports', 'generate_class_reports'
    ],
    
    'Student' => [
        'view_own_profile', 'edit_own_contact',
        'view_own_enrollment',
        'view_own_attendance',
        'view_own_results', 'download_transcript',
        'view_own_subjects'
    ]
];

function hasPermission($permission) {
    global $PERMISSIONS;
    $role = getUserRole();
    
    if (!$role) {
        return false;
    }
    
    return in_array($permission, $PERMISSIONS[$role] ?? []);
}

function requirePermission($permission) {
    if (!hasPermission($permission)) {
        http_response_code(403);
        die('<h1>403 Forbidden</h1><p>You do not have permission to access this resource.</p>');
    }
}

function requireRole($allowedRoles) {
    requireLogin();
    $role = getUserRole();
    
    if (!in_array($role, (array)$allowedRoles)) {
        http_response_code(403);
        die('<h1>403 Forbidden</h1><p>Access denied for your role.</p>');
    }
}

function ownsResource($resourceUserId) {
    return getUserId() == $resourceUserId;
}

function isAssignedToSubject($lecturerId, $subjectId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM lecturer_subjects WHERE lecturer_id = ? AND subject_id = ?");
    $stmt->bind_param("ii", $lecturerId, $subjectId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $result['count'] > 0;
}

function canEditAttendance($attendanceDate) {
    $today = date('Y-m-d');
    return $attendanceDate === $today;
}

function areResultsLocked($examId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT locked FROM exams WHERE id = ?");
    $stmt->bind_param("i", $examId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $result['locked'] ?? false;
}
?>
