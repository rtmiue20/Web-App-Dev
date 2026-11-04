<?php
require_once __DIR__ . '/../core/Model.php';

class Trip extends Model
{
    public function getAll(): array
    {
        return Database::query(
            "SELECT t.*, u.username,
                    (SELECT COUNT(*) FROM trip_participants tp WHERE tp.trip_id = t.id) AS participant_count
             FROM trips t
             JOIN users u ON u.id = t.user_id
             WHERE t.starts_at >= NOW()
             ORDER BY t.starts_at"
        );
    }

    public function getById(int $id): array|false
    {
        $trips = Database::query(
            "SELECT t.*, u.username,
                    (SELECT COUNT(*) FROM trip_participants tp WHERE tp.trip_id = t.id) AS participant_count
             FROM trips t
             JOIN users u ON u.id = t.user_id
             WHERE t.id = :id
             LIMIT 1",
            ['id' => $id]
        );
        return $trips[0] ?? false;
    }

    public function hasJoined(int $tripId, int $userId): bool
    {
        return Database::query(
            'SELECT 1 FROM trip_participants WHERE trip_id = :trip_id AND user_id = :user_id LIMIT 1',
            ['trip_id' => $tripId, 'user_id' => $userId]
        ) !== [];
    }

    public function create(
        int $userId,
        string $title,
        string $description,
        string $location,
        ?float $lat,
        ?float $lng,
        string $startsAt,
        int $maxParticipants
    ): int {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO trips (user_id, title, description, location, lat, lng, starts_at, max_participants)
                 VALUES (:user_id, :title, :description, :location, :lat, :lng, :starts_at, :max_participants)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'title' => $title,
                'description' => $description,
                'location' => $location,
                'lat' => $lat,
                'lng' => $lng,
                'starts_at' => $startsAt,
                'max_participants' => $maxParticipants,
            ]);
            $tripId = (int) $this->db->lastInsertId();
            $stmt = $this->db->prepare(
                'INSERT INTO trip_participants (trip_id, user_id) VALUES (:trip_id, :user_id)'
            );
            $stmt->execute(['trip_id' => $tripId, 'user_id' => $userId]);
            $this->db->commit();
            return $tripId;
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    public function join(int $tripId, int $userId): string
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT max_participants, (starts_at <= NOW()) AS has_started
                 FROM trips WHERE id = :id FOR UPDATE'
            );
            $stmt->execute(['id' => $tripId]);
            $trip = $stmt->fetch();
            if (!$trip) {
                $this->db->rollBack();
                return 'missing';
            }
            if ((int) $trip['has_started'] === 1) {
                $this->db->commit();
                return 'closed';
            }

            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM trip_participants WHERE trip_id = :trip_id'
            );
            $stmt->execute(['trip_id' => $tripId]);
            $count = (int) $stmt->fetchColumn();

            $stmt = $this->db->prepare(
                'SELECT 1 FROM trip_participants WHERE trip_id = :trip_id AND user_id = :user_id'
            );
            $stmt->execute(['trip_id' => $tripId, 'user_id' => $userId]);
            if ($stmt->fetchColumn()) {
                $this->db->commit();
                return 'already';
            }
            if ($count >= (int) $trip['max_participants']) {
                $this->db->commit();
                return 'full';
            }

            $stmt = $this->db->prepare(
                'INSERT INTO trip_participants (trip_id, user_id) VALUES (:trip_id, :user_id)'
            );
            $stmt->execute(['trip_id' => $tripId, 'user_id' => $userId]);
            $this->db->commit();
            return 'joined';
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }
}
