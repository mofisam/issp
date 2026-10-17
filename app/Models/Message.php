<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * A single user-to-user inbox message (see database/issp_database_schema.sql,
 * Section 11b). Any logged-in user - staff (admin/editor/super_admin) or
 * stakeholder - can be a sender or recipient; the admin compose screen is
 * how staff send one, and every recipient sees what they've received in
 * their own inbox (admin layout for staff, portal layout for stakeholders).
 */
class Message extends Model
{
    protected static string $table = 'messages';
    protected static bool $softDeletes = true;

    public static function send(?int $senderId, int $recipientId, string $subject, string $body): int
    {
        return self::create([
            'sender_id'    => $senderId,
            'recipient_id' => $recipientId,
            'subject'      => $subject,
            'body'         => $body,
        ]);
    }

    /** Sends the same message to several recipients at once (e.g. a staff-wide broadcast). @return int[] the new message ids */
    public static function sendToMany(?int $senderId, array $recipientIds, string $subject, string $body): array
    {
        $ids = [];
        foreach ($recipientIds as $recipientId) {
            $ids[] = self::send($senderId, (int) $recipientId, $subject, $body);
        }
        return $ids;
    }

    /** @return array<int, array<string, mixed>> messages received by $userId, newest first, with the sender's name attached */
    public static function inboxFor(int $userId, int $page, int $perPage, ?string $search = null): array
    {
        $filters = ['recipient_id' => $userId];
        $rows = self::paginate($page, $perPage, $filters, $search, ['subject', 'body'], 'created_at DESC');
        return self::attachSenderNames($rows);
    }

    public static function inboxCountFor(int $userId, ?string $search = null): int
    {
        return self::count(['recipient_id' => $userId], $search, ['subject', 'body']);
    }

    /** @return array<int, array<string, mixed>> messages sent by $userId, newest first, with the recipient's name attached */
    public static function sentBy(int $userId, int $page, int $perPage, ?string $search = null): array
    {
        $filters = ['sender_id' => $userId];
        $rows = self::paginate($page, $perPage, $filters, $search, ['subject', 'body'], 'created_at DESC');
        return self::attachRecipientNames($rows);
    }

    public static function sentCountBy(int $userId, ?string $search = null): int
    {
        return self::count(['sender_id' => $userId], $search, ['subject', 'body']);
    }

    public static function unreadCountFor(int $userId): int
    {
        $db   = Database::connection();
        $stmt = $db->prepare(
            'SELECT COUNT(*) AS total FROM `messages` WHERE `recipient_id` = ? AND `read_at` IS NULL AND `deleted_at` IS NULL'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($row['total'] ?? 0);
    }

    /** A message a given recipient is allowed to view - returns null if it doesn't belong to them. */
    public static function findForRecipient(int $id, int $userId): ?array
    {
        $message = self::find($id);
        if (!$message || (int) $message['recipient_id'] !== $userId) {
            return null;
        }
        $sender = $message['sender_id'] ? User::find((int) $message['sender_id']) : null;
        $message['sender_name'] = $sender ? trim($sender['first_name'] . ' ' . $sender['last_name']) : 'System';
        return $message;
    }

    public static function markRead(int $id, int $userId): void
    {
        $db   = Database::connection();
        $stmt = $db->prepare(
            'UPDATE `messages` SET `read_at` = NOW() WHERE `id` = ? AND `recipient_id` = ? AND `read_at` IS NULL'
        );
        $stmt->bind_param('ii', $id, $userId);
        $stmt->execute();
        $stmt->close();
    }

    /** @param array<int, array<string, mixed>> $rows */
    private static function attachSenderNames(array $rows): array
    {
        $senderIds = array_filter(array_unique(array_column($rows, 'sender_id')));
        $names = self::namesFor($senderIds);
        foreach ($rows as &$row) {
            $row['sender_name'] = $row['sender_id'] ? ($names[(int) $row['sender_id']] ?? 'Unknown') : 'System';
        }
        unset($row);
        return $rows;
    }

    /** @param array<int, array<string, mixed>> $rows */
    private static function attachRecipientNames(array $rows): array
    {
        $recipientIds = array_filter(array_unique(array_column($rows, 'recipient_id')));
        $names = self::namesFor($recipientIds);
        foreach ($rows as &$row) {
            $row['recipient_name'] = $names[(int) $row['recipient_id']] ?? 'Unknown';
        }
        unset($row);
        return $rows;
    }

    /** @return array<int, string> user id => "First Last" */
    private static function namesFor(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }
        $db = Database::connection();
        $placeholders = implode(', ', array_fill(0, count($userIds), '?'));
        $stmt = $db->prepare("SELECT id, first_name, last_name FROM `users` WHERE id IN ({$placeholders})");
        $types = str_repeat('i', count($userIds));
        $stmt->bind_param($types, ...array_values($userIds));
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $names = [];
        foreach ($rows as $row) {
            $names[(int) $row['id']] = trim($row['first_name'] . ' ' . $row['last_name']);
        }
        return $names;
    }
}
