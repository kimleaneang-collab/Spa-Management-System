<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class EmployeeController
{
    private const EMPLOYEE_ROLES = ['manager', 'receptionist', 'therapist', 'cashier', 'cleaner'];
    private const EMPLOYEE_STATUSES = ['active', 'inactive', 'on_leave'];
    private const ATTENDANCE_STATUSES = ['present', 'late', 'absent', 'leave', 'half_day'];

    public function index(): void
    {
        require_roles(['admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: GET, POST');
            exit('Method not allowed.');
        }

        $db = Database::connection();
        $formError = '';
        $formValues = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_valid_csrf_token();
            $formValues = $_POST;
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'create_employee') {
                $formError = $this->createEmployee($db);
            } elseif ($action === 'record_attendance') {
                $formError = $this->recordAttendance($db);
            } elseif ($action === 'create_schedule') {
                $formError = $this->createSchedule($db);
            } else {
                http_response_code(400);
                $formError = 'Unsupported employee management action.';
            }

            if ($formError === '') {
                $_SESSION['employee_management_message'] = 'The record was saved successfully.';
                redirect('employee-management');
            }
        }

        $employees = $db->query(
            'SELECT id, employee_code, full_name, role_name, phone, email, salary, hire_date, status
             FROM employees
             ORDER BY full_name'
        )->fetchAll(PDO::FETCH_ASSOC);
        $attendanceRecords = $db->query(
            'SELECT a.attendance_date, a.check_in, a.check_out, a.status, a.notes,
                    e.employee_code, e.full_name
             FROM attendance a
             INNER JOIN employees e ON e.id = a.employee_id
             ORDER BY a.attendance_date DESC, e.full_name
             LIMIT 100'
        )->fetchAll(PDO::FETCH_ASSOC);
        $schedules = $db->query(
            'SELECT es.day_of_week, es.start_time, es.end_time, es.is_day_off,
                    e.employee_code, e.full_name
             FROM employee_schedules es
             INNER JOIN employees e ON e.id = es.employee_id
             ORDER BY e.full_name, es.day_of_week
             LIMIT 100'
        )->fetchAll(PDO::FETCH_ASSOC);

        $successMessage = (string) ($_SESSION['employee_management_message'] ?? '');
        unset($_SESSION['employee_management_message']);

        require __DIR__ . '/../Views/employees/index.php';
    }

    private function createEmployee(PDO $db): string
    {
        $code = trim((string) ($_POST['employee_code'] ?? ''));
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $role = strtolower(trim((string) ($_POST['role_name'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $salary = trim((string) ($_POST['salary'] ?? '0'));
        $hireDate = trim((string) ($_POST['hire_date'] ?? ''));
        $status = strtolower(trim((string) ($_POST['status'] ?? 'active')));
        $errors = [];

        if (!valid_text($code, 1, 40)) {
            $errors[] = 'Enter an employee code between 1 and 40 characters.';
        }
        if (!valid_text($name, 2, 150)) {
            $errors[] = 'Enter an employee name between 2 and 150 characters.';
        }
        if (!in_array($role, self::EMPLOYEE_ROLES, true)) {
            $errors[] = 'Select a valid employee role.';
        }
        if (!valid_phone($phone)) {
            $errors[] = 'Enter a valid phone number or leave it blank.';
        }
        if (!valid_email($email)) {
            $errors[] = 'Enter a valid email address or leave it blank.';
        }
        if (!valid_non_negative_number($salary) || (float) $salary > 9999999999.99) {
            $errors[] = 'Enter a valid non-negative salary.';
        }
        if (!valid_date_value($hireDate)) {
            $errors[] = 'Enter a valid hire date.';
        }
        if (!in_array($status, self::EMPLOYEE_STATUSES, true)) {
            $errors[] = 'Select a valid employee status.';
        }
        if ($errors !== []) {
            return implode(' ', $errors);
        }

        $statement = $db->prepare(
            'INSERT INTO employees
                (employee_code, full_name, role_name, phone, email, salary, hire_date, status)
             VALUES
                (:employee_code, :full_name, :role_name, :phone, :email, :salary, :hire_date, :status)'
        );

        try {
            $db->beginTransaction();
            $statement->execute([
                ':employee_code' => $code,
                ':full_name' => $name,
                ':role_name' => $role,
                ':phone' => $phone !== '' ? $phone : null,
                ':email' => $email !== '' ? $email : null,
                ':salary' => $salary,
                ':hire_date' => $hireDate !== '' ? $hireDate : null,
                ':status' => $status,
            ]);
            $employeeId = (int) $db->lastInsertId();
            write_audit_log($db, 'employee.created', 'employees', $employeeId, null, [
                'employee_code' => $code,
                'full_name' => $name,
                'role_name' => $role,
            ]);
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                return 'That employee code is already in use.';
            }
            throw $exception;
        }

        return '';
    }

    private function recordAttendance(PDO $db): string
    {
        $employeeId = filter_var($_POST['employee_id'] ?? null, FILTER_VALIDATE_INT);
        $date = trim((string) ($_POST['attendance_date'] ?? ''));
        $checkIn = trim((string) ($_POST['check_in'] ?? ''));
        $checkOut = trim((string) ($_POST['check_out'] ?? ''));
        $status = strtolower(trim((string) ($_POST['attendance_status'] ?? '')));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $errors = [];

        if ($employeeId === false || $employeeId === null || $employeeId < 1
            || !$this->employeeExists($db, (int) $employeeId)) {
            $errors[] = 'Select an existing employee.';
        }
        if (!valid_date_value($date, true)) {
            $errors[] = 'Enter a valid attendance date.';
        }
        if (!in_array($status, self::ATTENDANCE_STATUSES, true)) {
            $errors[] = 'Select a valid attendance status.';
        }
        if (!valid_text($notes, 0, 255)) {
            $errors[] = 'Attendance notes must be 255 characters or fewer.';
        }

        $checkInDate = $this->parseDateTimeLocal($checkIn);
        $checkOutDate = $this->parseDateTimeLocal($checkOut);
        if ($checkIn !== '' && $checkInDate === null) {
            $errors[] = 'Enter a valid check-in time.';
        }
        if ($checkOut !== '' && $checkOutDate === null) {
            $errors[] = 'Enter a valid check-out time.';
        }
        if ($checkInDate !== null && $checkInDate->format('Y-m-d') !== $date) {
            $errors[] = 'Check-in must be on the attendance date.';
        }
        if ($checkOutDate !== null && $checkOutDate->format('Y-m-d') !== $date) {
            $errors[] = 'Check-out must be on the attendance date.';
        }
        if ($checkInDate !== null && $checkOutDate !== null && $checkOutDate < $checkInDate) {
            $errors[] = 'Check-out cannot be earlier than check-in.';
        }
        if ($errors !== []) {
            return implode(' ', $errors);
        }

        $statement = $db->prepare(
            'INSERT INTO attendance
                (employee_id, attendance_date, check_in, check_out, status, notes)
             VALUES
                (:employee_id, :attendance_date, :check_in, :check_out, :status, :notes)'
        );
        try {
            $db->beginTransaction();
            $statement->execute([
                ':employee_id' => (int) $employeeId,
                ':attendance_date' => $date,
                ':check_in' => $checkInDate?->format('Y-m-d H:i:s'),
                ':check_out' => $checkOutDate?->format('Y-m-d H:i:s'),
                ':status' => $status,
                ':notes' => $notes !== '' ? $notes : null,
            ]);
            $attendanceId = (int) $db->lastInsertId();
            write_audit_log($db, 'attendance.recorded', 'attendance', $attendanceId, null, [
                'employee_id' => (int) $employeeId,
                'attendance_date' => $date,
                'status' => $status,
            ]);
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                return 'Attendance has already been recorded for this employee and date.';
            }
            throw $exception;
        }

        return '';
    }

    private function createSchedule(PDO $db): string
    {
        $employeeId = filter_var($_POST['employee_id'] ?? null, FILTER_VALIDATE_INT);
        $day = filter_var($_POST['day_of_week'] ?? null, FILTER_VALIDATE_INT);
        $startTime = trim((string) ($_POST['start_time'] ?? ''));
        $endTime = trim((string) ($_POST['end_time'] ?? ''));
        $isDayOff = (string) ($_POST['is_day_off'] ?? '') === '1';
        $errors = [];

        if ($employeeId === false || $employeeId === null || $employeeId < 1
            || !$this->employeeExists($db, (int) $employeeId)) {
            $errors[] = 'Select an existing employee.';
        }
        if ($day === false || $day === null || $day < 1 || $day > 7) {
            $errors[] = 'Select a valid day of the week.';
        }
        if (!$isDayOff) {
            if (!$this->validTime($startTime) || !$this->validTime($endTime)) {
                $errors[] = 'Enter valid start and end times, or mark this day as a day off.';
            } elseif ($endTime <= $startTime) {
                $errors[] = 'The schedule end time must be after its start time.';
            }
        }
        if ($errors !== []) {
            return implode(' ', $errors);
        }

        $statement = $db->prepare(
            'INSERT INTO employee_schedules
                (employee_id, day_of_week, start_time, end_time, is_day_off)
             VALUES
                (:employee_id, :day_of_week, :start_time, :end_time, :is_day_off)'
        );
        try {
            $db->beginTransaction();
            $statement->execute([
                ':employee_id' => (int) $employeeId,
                ':day_of_week' => (int) $day,
                ':start_time' => $isDayOff ? null : $startTime,
                ':end_time' => $isDayOff ? null : $endTime,
                ':is_day_off' => $isDayOff ? 1 : 0,
            ]);
            $scheduleId = (int) $db->lastInsertId();
            write_audit_log($db, 'employee_schedule.created', 'employee_schedules', $scheduleId, null, [
                'employee_id' => (int) $employeeId,
                'day_of_week' => (int) $day,
                'is_day_off' => $isDayOff,
            ]);
            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                return 'A schedule already exists for this employee and day.';
            }
            throw $exception;
        }

        return '';
    }

    private function employeeExists(PDO $db, int $employeeId): bool
    {
        $statement = $db->prepare('SELECT id FROM employees WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $employeeId]);

        return $statement->fetchColumn() !== false;
    }

    private function parseDateTimeLocal(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $dateTime !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $dateTime->format('Y-m-d\TH:i') === $value
                ? $dateTime
                : null;
    }

    private function validTime(string $value): bool
    {
        $time = DateTimeImmutable::createFromFormat('!H:i', $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $time !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $time->format('H:i') === $value;
    }
}