<?php
/**
 * TrackingService
 * Generates unique tracking numbers for LS, AT, and PS requests
 */

require_once __DIR__ . '/../config/database.php';

class TrackingService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate next Locator Slip control number
     * Format: LS + 3 random chars + - + YYYYMM + - + 3-digit series
     * Example: LSP9K-202504-001
     */
    public function generateLSNumber()
    {
        return $this->generateNumber('LS');
    }

    /**
     * Generate next Authority to Travel tracking number
     * Format: AT + 3 random chars + - + YYYYMM + - + 3-digit series
     * Example: ATX7K-202504-001
     */
    public function generateATNumber($category = null, $scope = null)
    {
        return $this->generateNumber('AT');
    }

    /**
     * Generate next Pass Slip control number
     * Format: PS + 3 random chars + - + YYYYMM + - + 3-digit series
     * Example: PSL8P-202504-001
     */
    public function generatePSNumber()
    {
        return $this->generateNumber('PS');
    }

    /**
     * Legacy method - redirects to unified AT number
     */
    public function generateATLocalNumber()
    {
        return $this->generateATNumber();
    }

    /**
     * Legacy method - redirects to unified AT number
     */
    public function generateATNationalNumber()
    {
        return $this->generateATNumber();
    }

    /**
     * Legacy method - redirects to unified AT number
     */
    public function generateATPersonalNumber()
    {
        return $this->generateATNumber();
    }

    /**
     * Core number generation with atomic increment
     */
    private function generateNumber($prefix)
    {
        // Use YYYYMM in the existing `year` column so series resets each month.
        $period = date('Ym');
        $conn = $this->db->getConnection();

        try {
            $conn->beginTransaction();

            // Ensure sequence row exists for this prefix+period.
            $ensureSql = "INSERT INTO tracking_sequences (prefix, year, last_number)
                          VALUES (?, ?, 0)
                          ON DUPLICATE KEY UPDATE last_number = last_number";
            $conn->prepare($ensureSql)->execute([$prefix, $period]);

            // Lock the row for update
            $sql = "SELECT last_number FROM tracking_sequences 
                    WHERE prefix = ? AND year = ? FOR UPDATE";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$prefix, $period]);
            $row = $stmt->fetch();

            if (!$row) {
                throw new RuntimeException('Failed to initialize tracking sequence row.');
            }

            // Increment existing sequence
            $newNumber = intval($row['last_number']) + 1;
            $updateSql = "UPDATE tracking_sequences SET last_number = ? 
                          WHERE prefix = ? AND year = ?";
            $conn->prepare($updateSql)->execute([$newNumber, $prefix, $period]);

            if ($newNumber > 999) {
                throw new RuntimeException('Monthly series exceeded 999 for prefix: ' . $prefix);
            }

            $conn->commit();

            // Format: PREFIX + 3 random chars + - + YYYYMM + - + 3-digit series
            $randomSegment = $this->generateRandomSegment(3);
            return sprintf("%s%s-%s-%03d", $prefix, $randomSegment, $period, $newNumber);

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Generate random uppercase alphanumeric segment.
     */
    private function generateRandomSegment($length = 3)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxIndex = strlen($characters) - 1;
        $segment = '';

        for ($i = 0; $i < $length; $i++) {
            $segment .= $characters[random_int(0, $maxIndex)];
        }

        return $segment;
    }

    /**
     * Parse tracking number to get components
     */
    public static function parseTrackingNumber($trackingNo)
    {
        // New unified format: PREFIX + 3 random chars + - + YYYYMM + - + 3 digits
        if (preg_match('/^(LS|PS|AT)([A-Z0-9]{3})-(\d{6})-(\d{3})$/', $trackingNo, $matches)) {
            $period = $matches[3];
            $typeByPrefix = [
                'LS' => 'locator_slip',
                'PS' => 'pass_slip',
                'AT' => 'authority_to_travel'
            ];

            return [
                'type' => $typeByPrefix[$matches[1]],
                'prefix' => $matches[1],
                'random_segment' => $matches[2],
                'year' => substr($period, 0, 4),
                'month' => substr($period, 4, 2),
                'period' => $period,
                'number' => intval($matches[4])
            ];
        }

        // Legacy formats below remain supported for backward compatibility.
        // Handle different formats
        if (preg_match('/^(LS)-(\d{4})-(\d+)$/', $trackingNo, $matches)) {
            return [
                'type' => 'locator_slip',
                'prefix' => $matches[1],
                'year' => $matches[2],
                'number' => intval($matches[3])
            ];
        }

        if (preg_match('/^(PS)-(\d{4})-(\d+)$/', $trackingNo, $matches)) {
            return [
                'type' => 'pass_slip',
                'prefix' => $matches[1],
                'year' => $matches[2],
                'number' => intval($matches[3])
            ];
        }

        if (preg_match('/^(AT-LOCAL|AT-NATL|AT-OR|AT-PERS|AT)-(\d{4})-(\d+)$/', $trackingNo, $matches)) {
            $scope = 'local';
            $category = 'official';
            $travelType = 'within_region';

            if ($matches[1] === 'AT-PERS') {
                $category = 'personal';
                $scope = null;
                $travelType = null;
            } elseif ($matches[1] === 'AT-NATL' || $matches[1] === 'AT-OR') {
                $travelType = 'outside_region';
            }

            return [
                'type' => 'authority_to_travel',
                'prefix' => $matches[1],
                'year' => $matches[2],
                'number' => intval($matches[3]),
                'category' => $category,
                'scope' => $scope,
                'travel_type' => $travelType
            ];
        }

        return null;
    }

    /**
     * Validate tracking number format
     */
    public static function isValidTrackingNumber($trackingNo)
    {
        return self::parseTrackingNumber($trackingNo) !== null;
    }
}
