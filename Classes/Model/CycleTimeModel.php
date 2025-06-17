<?php

namespace Model;

class CycleTimeModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)
    {
        $this->db = $db;
    }

    public function getQCCycleTimes(): array
    {
        $sql = "SELECT material_no, qc_cycletime FROM material_inventory";
        $rows = $this->db->Select($sql);
        $data = [];
        foreach ($rows as $row) {
            $data[$row['material_no']] = $row['qc_cycletime'];
        }

        return $data;
    }

    public function getAssemblyCycleTimes(): array
    {
        $sql = "SELECT material_no, assembly_cycletime FROM material_inventory";
        $rows = $this->db->Select($sql);
        $data = [];
        foreach ($rows as $row) {
            $data[$row['material_no']] = $row['assembly_cycletime']; // ✅ FIXED
        }

        return $data;
    }
}
