<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../../config/database.php';

final class CalendarController
{
    public function index(): void
    {
        require_authentication();

        $year = filter_var($_GET['year'] ?? date('Y'), FILTER_VALIDATE_INT);
        $month = filter_var($_GET['month'] ?? date('n'), FILTER_VALIDATE_INT);
        if ($year === false || $year < 2000 || $year > 2100 || $month === false || $month < 1 || $month > 12) {
            http_response_code(400);
            exit('Invalid calendar month.');
        }

        $monthStart = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $gridStart = $monthStart->modify('-' . (int) $monthStart->format('w') . ' days');
        $gridEnd = $gridStart->modify('+42 days');
        $user = $_SESSION['user'] ?? [];
        $role = strtolower((string) ($user['role'] ?? ''));
        $isTherapist = in_array($role, ['staff', 'therapist'], true);
        $appointmentModel = new Appointment(Database::connection());
        if ($isTherapist) {
            $weekStart = (new DateTimeImmutable('today'))->modify('monday this week');
            $weekEnd = $weekStart->modify('+7 days');
            $therapistAppointments = $appointmentModel->forTherapistUser(
                (int) ($user['id'] ?? 0),
                (string) ($user['full_name'] ?? $user['username'] ?? '')
            );
            $therapistId = $therapistAppointments['therapist_id'];
            $appointments = $therapistId === null
                ? []
                : $appointmentModel->betweenDatesForTherapist(
                    $therapistId,
                    $weekStart->format('Y-m-d'),
                    $weekEnd->format('Y-m-d')
                );
            $scheduleStart = $weekStart;
            $scheduleDays = 7;
        } else {
            $therapistId = null;
            $appointments = $appointmentModel->betweenDates(
                $gridStart->format('Y-m-d'),
                $gridEnd->format('Y-m-d')
            );
            $scheduleStart = $gridStart;
            $scheduleDays = 42;
        }
        $appointmentsByDate = [];
        foreach ($appointments as $appointment) {
            $appointmentsByDate[$appointment['appointment_date']][] = $appointment;
        }

        $previousMonth = $monthStart->modify('-1 month');
        $nextMonth = $monthStart->modify('+1 month');
        $today = date('Y-m-d');

        require __DIR__ . '/../Views/appointments/calender.php';
    }
}
