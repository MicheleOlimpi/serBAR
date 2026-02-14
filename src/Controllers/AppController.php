<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\BarRepository;
use App\Services\BoardService;

class AppController
{
    private const CALENDAR_KIND_TO_CODE = [
        'feriale' => 'feriale',
        'festivo' => 'festivo',
        'speciale' => 'speciale',
    ];

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_day'], $_POST['message'])) {
            $message = trim((string) $_POST['message']);
            $dayId = (int) $_POST['report_day'];
            if ($dayId > 0 && $message !== '') {
                $this->repo->createNotification((int) Auth::user()['id'], $dayId, $message);
            }
            View::redirect('./');
        }

        View::render('consultation/dashboard', [
            'boards' => $this->repo->boardsForConsultation(),
            'shifts' => $this->repo->consultationShifts(),
            'notifications' => $this->repo->consultationNotifications(),
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
                $this->repo->saveBoardDay([
                    'id' => (int) $dayId,
                    'day_type_id' => (int) ($dayData['day_type_id'] ?? 0),
                    'morning_close' => $dayData['morning_close'] ?? null,
                    'evening_close' => $dayData['evening_close'] ?? null,
                    'notes' => $dayData['notes'] ?? null,
                ]);
                $this->repo->setBoardDayUsers((int) $dayId, $dayData['users'] ?? []);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Auth::isAdmin() && isset($_POST['report_day'])) {
            $this->repo->createNotification((int) Auth::user()['id'], (int) $_POST['report_day'], trim($_POST['message']));
        }

        View::render('admin/board_edit', [
            'board' => $this->repo->board($boardId),
            'days' => $this->repo->boardDays($boardId),
            'dayTypes' => $this->repo->dayTypes(),
            'users' => $this->repo->activeUsers(),
            'dayUsers' => $this->repo->boardDayUsersMap($boardId),
        ]);
    }

    public function users(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->repo->saveUser($_POST);
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
        }
        if (isset($_GET['delete'])) {
            $this->repo->deleteDayType((int) $_GET['delete']);
            View::redirect('?action=day_types');
        }
        View::render('admin/day_types', ['types' => $this->repo->dayTypes()]);
    }

    public function shiftConfig(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['slots'] ?? [] as $dayTypeId => $slots) {
                $this->repo->saveShiftConfig((int) $dayTypeId, (int) $slots);
            }
        }
        View::render('admin/shift_config', ['cfg' => $this->repo->shiftConfigs()]);
    }

    public function calendar(): void
    {
        $this->guardAdmin();

        if (isset($_GET['delete'])) {
            $this->repo->deleteCalendarDay((int) $_GET['delete']);
            View::redirect('?action=calendar');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dayTypeId = $this->resolveDayTypeId($_POST['calendar_kind'] ?? 'feriale', $_POST['day_type_id'] ?? null);
            $this->repo->saveCalendarDay([
                'id' => $_POST['id'] ?? null,
                'day_date' => $_POST['day_date'],
                'recurrence_name' => $_POST['recurrence_name'],
                'is_holiday' => ($_POST['calendar_kind'] ?? '') === 'festivo' ? 1 : 0,
                'is_special' => ($_POST['calendar_kind'] ?? '') === 'speciale' ? 1 : 0,
                'day_type_id' => $dayTypeId,
            ]);
            View::redirect('?action=calendar');
        }

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editing = $editingId > 0 ? $this->repo->calendarDayById($editingId) : null;

        View::render('admin/calendar', [
            'days' => $this->repo->calendarDays($_GET['month'] ?? null),
            'types' => $this->repo->dayTypes(),
            'editing' => $editing,
            'calendarKinds' => array_keys(self::CALENDAR_KIND_TO_CODE),
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

    private function resolveDayTypeId(string $calendarKind, mixed $selectedDayTypeId): ?int
    {
        $dayTypeId = (int) $selectedDayTypeId;
        if ($dayTypeId > 0) {
            return $dayTypeId;
        }

        $code = self::CALENDAR_KIND_TO_CODE[$calendarKind] ?? 'feriale';
        $dayType = $this->repo->dayTypeByCode($code);
        return $dayType ? (int) $dayType['id'] : null;
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
