<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\BarRepository;
use App\Services\BoardService;

class AppController
{
    public function __construct(private BarRepository $repo, private BoardService $boardService)
    {
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->repo->findUserByUsername(trim($_POST['username'] ?? ''));
            if ($user && $user['status'] === 'attivo' && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
                Auth::login($user);
                View::redirect('./');
            }
            View::render('auth/login', ['error' => 'Credenziali non valide']);
            return;
        }
        View::render('auth/login');
    }

    public function logout(): void
    {
        Auth::logout();
        View::redirect('?action=login');
    }

    public function dashboard(): void
    {
        $this->guard();
        if (Auth::isAdmin()) {
            View::render('admin/dashboard', [
                'boards' => $this->repo->boards(),
                'notifications' => $this->repo->notifications(),
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
            $message = trim((string) $_POST['message']);
            if ($message !== '') {
                $this->repo->createNotification((int) Auth::user()['id'], null, $message);
            }
            View::redirect('./');
        }

        View::render('consultation/dashboard', [
            'boards' => $this->repo->boardsForConsultation(),
            'shifts' => $this->repo->consultationShifts(),
            'notifications' => $this->repo->consultationNotifications(),
            'directoryUsers' => $this->repo->consultationDirectory(),
        ]);
    }

    public function boards(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_board'])) {
            $month = (int) $_POST['month'];
            $year = (int) $_POST['year'];
            $id = $this->repo->createBoard($month, $year);
            $this->boardService->generate($id, $month, $year);
        }
        if (isset($_GET['delete'])) {
            $this->repo->deleteBoard((int) $_GET['delete']);
            View::redirect('?action=boards');
        }
        View::render('admin/boards', ['boards' => $this->repo->boards()]);
    }

    public function boardEdit(): void
    {
        $this->guard();
        $boardId = (int) ($_GET['id'] ?? 0);
        if ($boardId < 1) {
            View::redirect('./');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::isAdmin()) {
            foreach ($_POST['day'] ?? [] as $dayId => $dayData) {
                $dayId = (int) $dayId;
                $dayTypeId = (int) ($dayData['day_type_id'] ?? 0);

                $this->repo->saveBoardDay([
                    'id' => $dayId,
                    'day_type_id' => $dayTypeId,
                    'notes' => $dayData['notes'] ?? null,
                ]);
                $this->repo->syncBoardDayShifts($dayId, $dayTypeId);

                foreach ($dayData['shifts'] ?? [] as $shiftId => $shiftData) {
                    $this->repo->updateBoardDayShiftVolunteers(
                        (int) $shiftId,
                        trim((string) ($shiftData['volunteers'] ?? '')),
                        trim((string) ($shiftData['responsabile_chiusura'] ?? '')) !== '' ? trim((string) ($shiftData['responsabile_chiusura'] ?? '')) : null
                    );
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Auth::isAdmin() && isset($_POST['report_day'])) {
            $this->repo->createNotification((int) Auth::user()['id'], (int) $_POST['report_day'], trim($_POST['message']));
        }

        $days = $this->repo->boardDays($boardId);
        foreach ($days as $day) {
            $this->repo->syncBoardDayShifts((int) $day['id'], (int) $day['day_type_id']);
        }

        View::render('admin/board_edit', [
            'board' => $this->repo->board($boardId),
            'days' => $days,
            'dayTypes' => $this->repo->dayTypes(),
            'dayShifts' => $this->repo->boardDayShiftsMap($boardId),
            'activeUsers' => $this->repo->userDisplayNames(),
        ]);
    }

    public function users(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_password_user_id'])) {
                $this->repo->changeUserPassword((int) $_POST['change_password_user_id'], (string) ($_POST['new_password'] ?? ''));
            } elseif (isset($_POST['update_user_id'])) {
                $this->repo->updateUserProfile(
                    (int) $_POST['update_user_id'],
                    (string) ($_POST['last_name'] ?? ''),
                    (string) ($_POST['first_name'] ?? ''),
                    (string) ($_POST['status'] ?? 'attivo')
                );
            } else {
                $this->repo->saveUser($_POST);
            }
        }
        if (isset($_GET['delete'])) {
            $this->repo->deleteUser((int) $_GET['delete']);
            View::redirect('?action=users');
        }
        View::render('admin/users', ['users' => $this->repo->allUsers()]);
    }

    public function dayTypes(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->repo->saveDayType($_POST);
            View::redirect('?action=day_types');
        }
        if (isset($_GET['delete'])) {
            $this->repo->deleteDayType((int) $_GET['delete']);
            View::redirect('?action=day_types');
        }

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editing = $editingId > 0 ? $this->repo->dayTypeById($editingId) : null;

        View::render('admin/day_types', [
            'types' => $this->repo->dayTypes(),
            'editing' => $editing,
        ]);
    }

    public function shiftConfig(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->repo->saveDailyShift($_POST);
            if ($error !== null) {
                View::redirect('?action=shift_config&error=' . urlencode($error));
            }
            View::redirect('?action=shift_config&saved=1');
        }

        if (isset($_GET['delete'])) {
            $this->repo->deleteDailyShift((int) $_GET['delete']);
            View::redirect('?action=shift_config');
        }

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editing = $editingId > 0 ? $this->repo->dailyShiftById($editingId) : null;

        View::render('admin/shift_config', [
            'shifts' => $this->repo->shiftConfigs(),
            'dayTypes' => $this->repo->dayTypes(),
            'editing' => $editing,
            'error' => isset($_GET['error']) ? (string) $_GET['error'] : '',
            'saved' => isset($_GET['saved']),
        ]);
    }

    public function calendar(): void
    {
        $this->guardAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $calendarDayId = (int) ($_POST['save_id'] ?? 0);
            $rowData = $_POST['row'][$calendarDayId] ?? null;
            if ($calendarDayId > 0) {
                $this->repo->updateCalendarDayDetails(
                    $calendarDayId,
                    trim((string) ($rowData['recurrence_name'] ?? '')),
                    trim((string) ($rowData['santo'] ?? '')),
                    (int) ($rowData['day_type_id'] ?? 0),
                    isset($rowData['is_special'])
                );
            }
            View::redirect('?action=calendar');
        }

        View::render('admin/calendar', [
            'days' => $this->repo->calendarDays($_GET['month'] ?? null),
            'types' => $this->repo->dayTypes(),
        ]);
    }

    public function setup(): void
    {
        $this->guardAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->repo->saveSetupSettings($_POST);
            View::redirect('?action=setup&saved=1');
        }

        View::render('admin/setup', [
            'settings' => $this->repo->setupSettings(),
            'saved' => isset($_GET['saved']),
        ]);
    }

    public function notifications(): void
    {
        $this->guardAdmin();

        if (isset($_GET['delete'])) {
            $this->repo->deleteNotification((int) $_GET['delete']);
            View::redirect('?action=notifications');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['quick_status_id'], $_POST['quick_status'])) {
                $this->repo->updateNotificationStatus((int) $_POST['quick_status_id'], (string) $_POST['quick_status']);
            } else {
                $this->repo->saveNotification($_POST);
            }
            View::redirect('?action=notifications');
        }

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editing = $editingId > 0 ? $this->repo->notificationById($editingId) : null;

        View::render('admin/notifications', [
            'notifications' => $this->repo->notifications(),
            'users' => $this->repo->activeUsers(),
            'boardDays' => $this->repo->boardDaysForSelect(),
            'editing' => $editing,
            'statuses' => ['inviata', 'letto', 'in_corso', 'chiuso'],
        ]);
    }

    private function guard(): void
    {
        if (!Auth::check()) {
            View::redirect('?action=login');
        }
    }

    private function guardAdmin(): void
    {
        $this->guard();
        if (!Auth::isAdmin()) {
            View::redirect('./');
        }
    }
}
