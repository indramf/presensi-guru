<?php

namespace App\Enums;

enum AuditEventType: string
{
    case LoginSuccess = 'auth.login';
    case LoginFailed = 'auth.login_failed';
    case Logout = 'auth.logout';
    case DataCreated = 'data.created';
    case DataUpdated = 'data.updated';
    case DataDeactivated = 'data.deactivated';
    case AttendanceRecorded = 'attendance.recorded';
    case AttendanceRejected = 'attendance.rejected';
    case AttendanceStatusChanged = 'attendance.status_changed';
    case ClassReportCreated = 'class_report.created';
    case AccessDenied = 'security.access_denied';
    case AlertReviewed = 'alert.reviewed';
    case IntegrityChecked = 'audit.integrity_checked';
}
