<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Therapist.php';
require_once __DIR__ . '/../../config/database.php';

final class TherapistController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $therapistModel = new Therapist(Database::connection());

        $editingTherapist = null;
        $editId = (int) ($_GET['edit'] ?? 0);
        if ($editId > 0) {
            $editingTherapist = $therapistModel->find($editId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['therapist_name'])) {
            $action = (string) ($_POST['action'] ?? 'create');
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['therapist_name'] ?? ''));
            $phone = trim((string) ($_POST['therapist_phone'] ?? ''));

            if (valid_text($name, 2, 150) && valid_phone($phone, true)) {
                $specialization = trim((string) ($_POST['therapist_specialization'] ?? ''));
                $gender = trim((string) ($_POST['therapist_gender'] ?? ''));
                $status = trim((string) ($_POST['therapist_status'] ?? 'active'));
                if (valid_text($specialization, 0, 150) && in_array($gender, ['', 'male', 'female', 'other'], true) && in_array($status, ['active', 'inactive', 'on_leave'], true) && (($action === 'update' && $id > 0) || $action === 'create')) {
                    if ($action === 'update' && $id > 0) {
                    $therapistModel->update($id, $name, $phone, $specialization, $gender, $status);
                    } else {
                    $therapistModel->create($name, $phone, $specialization, $gender, $status);
                    }
                }
            }

            redirect('therapists');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $therapistModel->delete($id);
            }
            redirect('therapists');
        }

        $therapists = [];
        try {
            $therapists = $therapistModel->all();
        } catch (Throwable $e) {
            $therapists = [
                ['name' => 'Maya Lim', 'specialty' => 'Massage Therapy', 'status' => 'Available', 'status_class' => 'available', 'today' => 5, 'rating' => 4.9],
                ['name' => 'Sophie Tran', 'specialty' => 'Facial Care', 'status' => 'On Break', 'status_class' => 'break', 'today' => 3, 'rating' => 4.8],
                ['name' => 'Hana Park', 'specialty' => 'Body Treatment', 'status' => 'Available', 'status_class' => 'available', 'today' => 6, 'rating' => 4.7],
                ['name' => 'Daniel Cruz', 'specialty' => 'Thermal Therapy', 'status' => 'Off Duty', 'status_class' => 'off-duty', 'today' => 0, 'rating' => 4.6],
            ];
        }

        require __DIR__ . '/../Views/therapists/index.php';
    }
}
