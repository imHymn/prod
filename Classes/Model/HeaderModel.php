<?php


namespace Model;

class HeaderModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)
    {
        $this->db = $db;
    }

    public function getAssemblyCounts(): array
    {
        $counts = [
            'assembly_todolist' => 0,
            'assembly_rework' => 0,
            'assembly_manpower_efficiency' => 0,
            'assembly_worklogs' => 0,
            'assembly_staping_stages' => 0, // ✅ New count
            'total' => 0
        ];

        // Base queries
        $queries = [
            'assembly_todolist' => "SELECT COUNT(*) as count FROM delivery_form WHERE section = 'DELIVERY' AND process IS NULL",
            'assembly_rework' => "SELECT COUNT(*) as count FROM rework_assembly WHERE assembly_timeout IS NULL",
            // ✅ Add QC-related section count
            'assembly_staping_stages' => "SELECT COUNT(*) as count 
                                 FROM stamping 
                                 WHERE LOWER(section) IN ('finishing', 'spot welding') 
                                   AND time_out IS NULL"
        ];

        foreach ($queries as $key => $sql) {
            $result = $this->db->SelectOne($sql);
            $counts[$key] = (int)($result['count'] ?? 0);
        }

        // Manpower efficiency
        $manpowerMain = $this->db->SelectOne("SELECT COUNT(*) as count FROM assembly_list");
        $manpowerRework = $this->db->SelectOne("SELECT COUNT(*) as count FROM rework_assembly");
        $counts['assembly_manpower_efficiency'] = (int)$manpowerMain['count'] + (int)$manpowerRework['count'];

        // Worklogs
        $worklogsMain = $this->db->SelectOne("SELECT COUNT(*) as count FROM assembly_list");
        $worklogsRework = $this->db->SelectOne("SELECT COUNT(*) as count FROM rework_assembly");
        $counts['assembly_worklogs'] = (int)$worklogsMain['count'] + (int)$worklogsRework['count'];

        $counts['total'] = $counts['assembly_todolist'] + $counts['assembly_rework'];

        return $counts;
    }

    public function getQcCounts(): array
    {
        $counts = [
            'qc_todolist' => 0,
            'qc_rework' => 0,
            'qc_manpower_efficiency' => 0,
            'qc_worklogs' => 0,
            'total' => 0
        ];

        // Base queries without time filter
        $queries = [
            'qc_todolist' => "SELECT COUNT(*) as count FROM qc_list WHERE time_out IS NULL",
            'qc_rework' => "SELECT COUNT(*) as count FROM rework_qc WHERE qc_timeout is null",
        ];

        // Fetch base counts
        foreach ($queries as $key => $sql) {
            $result = $this->db->SelectOne($sql);
            $counts[$key] = (int)($result['count'] ?? 0);
        }

        // Manpower efficiency = sum of assembly_list and rework_assembly
        $manpowerMain = $this->db->SelectOne("SELECT COUNT(*) as count FROM qc_list");
        $manpowerRework = $this->db->SelectOne("SELECT COUNT(*) as count FROM rework_qc");
        $counts['qc_manpower_efficiency'] = (int)$manpowerMain['count'] + (int)$manpowerRework['count'];

        // Same logic for worklogs
        $worklogsMain = $this->db->SelectOne("SELECT COUNT(*) as count FROM qc_list");
        $worklogsRework = $this->db->SelectOne("SELECT COUNT(*) as count FROM rework_qc");
        $counts['qc_worklogs'] = (int)$worklogsMain['count'] + (int)$worklogsRework['count'];

        $counts['total'] = $counts['qc_todolist'] + $counts['qc_rework'];


        return $counts;
    }
    public function getDeliveryCounts(): array
    {
        $counts = [
            'pulled_out' => 0,
            'total' => 0,
        ];

        $sql = "SELECT COUNT(*) as count FROM delivery_form WHERE date_loaded is null";
        $result = $this->db->SelectOne($sql);
        $counts['pulled_out'] = (int)($result['count'] ?? 0);

        $counts['total'] = $counts['pulled_out']; // If there are other delivery items, sum them here.

        return $counts;
    }
    public function getWarehouseCounts(): array
    {
        $counts = [
            'for_pulling' => 0,
            'total' => 0
        ];

        $sql = "SELECT COUNT(*) as count FROM fg_warehouse WHERE pulled_at is null";
        $result = $this->db->SelectOne($sql);
        $counts['for_pulling'] = (int)($result['count'] ?? 0);

        $counts['total'] = $counts['for_pulling']; // Add more if needed

        return $counts;
    }
    public function getRmwCounts(): array
    {
        $counts = [
            'for_issue' => 0,
            'total' => 0
        ];

        // Example: Replace with your actual table and condition
        $sql = "SELECT COUNT(*) as count FROM issued_rawmaterials WHERE delivered_at is null";
        $result = $this->db->SelectOne($sql);
        $counts['for_issue'] = (int)($result['count'] ?? 0);

        $counts['total'] = $counts['for_issue']; // Add more sub-counts if needed later

        return $counts;
    }
    public function getStampingCounts(): array
    {
        $counts = [
            'stamping_todolist' => 0,
            'total' => 0
        ];

        $sql = "SELECT COUNT(*) as count 
            FROM stamping 
            WHERE time_out IS NULL 
              AND LOWER(section) NOT IN ('finishing', 'spot welding')";

        $result = $this->db->SelectOne($sql);
        $counts['stamping_todolist'] = (int)($result['count'] ?? 0);

        // Total count can be adjusted later if needed
        $counts['total'] = $counts['stamping_todolist'];

        return $counts;
    }
}
