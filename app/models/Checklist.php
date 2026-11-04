<?php
require_once __DIR__ . '/../core/Model.php';

class Checklist extends Model
{
    public function getVisibleForUser(?int $userId): array
    {
        $sql = 'SELECT c.*, u.username
                FROM trip_checklists c
                JOIN users u ON u.id = c.user_id
                WHERE c.is_public = 1';
        $params = [];

        if ($userId !== null) {
            $sql .= ' OR c.user_id = :user_id';
            $params['user_id'] = $userId;
        }

        $sql .= ' ORDER BY c.created_at DESC';
        $checklists = Database::query($sql, $params);

        foreach ($checklists as &$checklist) {
            $checklist['items'] = Database::query(
                'SELECT item FROM checklist_items WHERE checklist_id = :id ORDER BY sort_order, id',
                ['id' => $checklist['id']]
            );
        }
        unset($checklist);

        return $checklists;
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
}
