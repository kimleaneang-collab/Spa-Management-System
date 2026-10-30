<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../../config/database.php';

final class AppointmentController
{
    // Show appointments and handle allowed appointment changes.
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $user = $_SESSION['user'];
        $role = strtolower((string) ($user['role'] ?? ''));
        $isTherapist = in_array($role, ['staff', 'therapist'], true);
        $appointmentModel = new Appointment(Database::connection());
        $editingAppointment = null;
        $editId = $isTherapist ? 0 : (int) ($_GET['edit'] ?? 0);
        if ($editId > 0) {
            $editingAppointment = $appointmentModel->find($editId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($isTherapist) {
                http_response_code(403);
                echo 'Therapists cannot create or modify appointments.';
                return;
            }

            $action = (string) ($_POST['action'] ?? 'create');
            $id = (int) ($_POST['id'] ?? 0);
            if ($action === 'delete' && $id > 0) {
                $appointmentModel->delete($id);
                write_audit_log(Database::connection(), 'appointment.deleted', 'appointments', $id);
                redirect('appointments');
            }

            $customerId = (int) ($_POST['customer_id'] ?? 0);
            if ($customerId <= 0 && isset($_POST['customer_search'])) {
                $customerSearch = trim((string) $_POST['customer_search']);
                $customerLookup = Database::connection()->prepare(
                    'SELECT id FROM customers WHERE full_name = :name AND status = \'active\' LIMIT 1'
                );
                $customerLookup->execute([':name' => $customerSearch]);
                $customerId = (int) $customerLookup->fetchColumn();
            }
            $serviceId = (int) ($_POST['service_id'] ?? 0);
            $therapistId = (int) ($_POST['therapist_id'] ?? 0) ?: null;
            $roomId = (int) ($_POST['room_id'] ?? 0) ?: null;
            $date = trim((string) ($_POST['appointment_date'] ?? ''));
            $start = trim((string) ($_POST['start_time'] ?? ''));
            $end = trim((string) ($_POST['end_time'] ?? ''));
            $status = trim((string) ($_POST['status'] ?? 'pending'));
            $notes = trim((string) ($_POST['notes'] ?? ''));

            if ($end === '' && $serviceId > 0 && valid_date_value($date, true) && preg_match('/^\d{2}:\d{2}$/', $start) === 1) {
                $durationQuery = Database::connection()->prepare('SELECT duration_minutes FROM services WHERE id = :id AND status = "active"');
                $durationQuery->execute([':id' => $serviceId]);
                $duration = (int) $durationQuery->fetchColumn();
                if ($duration > 0) {
                    $end = (new DateTimeImmutable($date . ' ' . $start))->modify('+' . $duration . ' minutes')->format('H:i');
                }
            }

            $startDateTime = preg_match('/^\d{2}:\d{2}$/', $start) === 1 && valid_date_value($date, true)
                ? new DateTimeImmutable($date . ' ' . $start)
                : null;
            $endDateTime = preg_match('/^\d{2}:\d{2}$/', $end) === 1 && valid_date_value($date, true)
                ? new DateTimeImmutable($date . ' ' . $end)
                : null;
            if ($startDateTime !== null && $endDateTime !== null && $endDateTime <= $startDateTime) {
                $endDateTime = $endDateTime->modify('+1 day');
            }

            if ($customerId > 0 && $serviceId > 0 && valid_date_value($date, true) && $startDateTime !== null && $endDateTime !== null && $endDateTime > $startDateTime && in_array($status, ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'], true) && valid_text($notes, 0, 2000)) {
                if ($action === 'update' && $id > 0) {
                    $appointmentModel->update($id, $customerId, $serviceId, $therapistId, $roomId, $date, $start, $end, $status, $notes);
                    write_audit_log(Database::connection(), 'appointment.updated', 'appointments', $id);
                } elseif ($action === 'create') {
                    $appointmentModel->create($customerId, $serviceId, $therapistId, $roomId, $date, $start, $end, $status, $notes, (int) ($_SESSION['user']['id'] ?? 0) ?: null);
                    write_audit_log(Database::connection(), 'appointment.created', 'appointments', (int) Database::connection()->lastInsertId());
                }
            } else {
                $missing = [];
                if ($customerId <= 0) {
                    $missing[] = 'customer';
                }
                if ($serviceId <= 0) {
                    $missing[] = 'service';
                }
                if (!valid_date_value($date, true)) {
                    $missing[] = 'date';
                }
                if ($startDateTime === null) {
                    $missing[] = 'start time';
                }
                if ($endDateTime === null) {
                    $missing[] = 'end time';
                }
                if ($startDateTime !== null && $endDateTime !== null && $endDateTime <= $startDateTime) {
                    $missing[] = 'an end time after the start time';
                }
                $_SESSION['form_error'] = 'Please provide: ' . implode(', ', $missing) . '.';
            }
            redirect('appointments');
        }

        $appointments = [];
        $options = ['customers' => [], 'therapists' => [], 'services' => [], 'rooms' => []];
        $formError = (string) ($_SESSION['form_error'] ?? '');
        unset($_SESSION['form_error']);
        $therapistId = null;
        if ($isTherapist) {
            $therapistAppointments = $appointmentModel->forTherapistUser(
                (int) ($user['id'] ?? 0),
                (string) ($user['full_name'] ?? $user['username'] ?? '')
            );
            $therapistId = $therapistAppointments['therapist_id'];
            $appointments = $therapistAppointments['appointments'];
        } else {
            $appointments = $appointmentModel->all();
            $options = $appointmentModel->options();
        }

        $isNewAppointmentRoute = (string) ($_GET['route'] ?? '') === 'new-appointment';
        if ($role === 'receptionist' && $isNewAppointmentRoute) {
            require __DIR__ . '/../Views/appointments/create.php';
            return;
        }

        $showCreateModal = $isNewAppointmentRoute && !$isTherapist && $editingAppointment === null;

        require __DIR__ . '/../Views/appointments/index.php';
    }
}
