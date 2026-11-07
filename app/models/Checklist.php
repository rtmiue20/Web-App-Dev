<?php
require_once __DIR__ . '/../core/Model.php';

class Checklist extends Model
{
    public function getVisibleForUser(?int $userId, ?string $tripType = null): array
    {
        $sql = 'SELECT c.*, u.username, u.avatar_path
                FROM trip_checklists c
                JOIN users u ON u.id = c.user_id
                WHERE ';
        $params = [];

        if ($userId !== null) {
            $sql .= '(c.is_public = 1 OR c.user_id = :user_id)';
            $params['user_id'] = $userId;
        } else {
            $sql .= 'c.is_public = 1';
        }

        if ($tripType !== null && trim($tripType) !== '' && $tripType !== 'all') {
            $sql .= ' AND c.trip_type = :trip_type';
            $params['trip_type'] = trim($tripType);
        }

        $sql .= ' ORDER BY c.created_at DESC';
        $checklists = Database::query($sql, $params);

        foreach ($checklists as &$checklist) {
            $checklist['items'] = Database::query(
                'SELECT id, item, sort_order FROM checklist_items WHERE checklist_id = :id ORDER BY sort_order, id',
                ['id' => $checklist['id']]
            );
        }
        unset($checklist);

        return $checklists;
    }

    public function getTripTypes(): array
    {
        $rows = Database::query('SELECT DISTINCT trip_type FROM trip_checklists WHERE is_public = 1 ORDER BY trip_type ASC');
        return array_column($rows, 'trip_type');
    }

    public function getById(int $id): array|false
    {
        $rows = Database::query(
            'SELECT c.*, u.username FROM trip_checklists c JOIN users u ON u.id = c.user_id WHERE c.id = :id LIMIT 1',
            ['id' => $id]
        );
        if (empty($rows)) {
            return false;
        }
        $checklist = $rows[0];
        $checklist['items'] = Database::query(
            'SELECT id, item, sort_order FROM checklist_items WHERE checklist_id = :id ORDER BY sort_order, id',
            ['id' => $id]
        );
        return $checklist;
    }

    public function create(int $userId, string $title, string $tripType, bool $isPublic, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO trip_checklists (user_id, title, trip_type, is_public)
                 VALUES (:user_id, :title, :trip_type, :is_public)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'title' => $title,
                'trip_type' => $tripType,
                'is_public' => $isPublic ? 1 : 0,
            ]);
            $checklistId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare(
                'INSERT INTO checklist_items (checklist_id, item, sort_order) VALUES (:checklist_id, :item, :sort_order)'
            );
            foreach ($items as $index => $item) {
                $stmt->execute([
                    'checklist_id' => $checklistId,
                    'item' => $item,
                    'sort_order' => $index,
                ]);
            }

            $this->db->commit();
            return $checklistId;
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    public function delete(int $id, int $userId, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            $deleted = Database::execute('DELETE FROM trip_checklists WHERE id = :id', ['id' => $id]) > 0;
        } else {
            $deleted = Database::execute(
                'DELETE FROM trip_checklists WHERE id = :id AND user_id = :user_id',
                ['id' => $id, 'user_id' => $userId]
            ) > 0;
        }

        if ($deleted) {
            try {
                Database::execute('DELETE FROM checklist_items WHERE checklist_id = :id', ['id' => $id]);
            } catch (Throwable $e) {
            }
            try {
                Database::execute("DELETE FROM notifications WHERE type = 'create_checklist' AND target_id = :id", ['id' => $id]);
            } catch (Throwable $e) {
            }
        }

        return $deleted;
    }
}
