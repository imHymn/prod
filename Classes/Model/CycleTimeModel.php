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
    public function getAssemblyProcessTimes(): array
    {
        $sql = "SELECT material_no, assembly_processtime FROM material_inventory";
        $rows = $this->db->Select($sql);
        $data = [];
        foreach ($rows as $row) {
            $data[$row['material_no']] = $row['assembly_processtime']; // ✅ FIXED
        }

        return $data;
    }
    // Inside CycleTimeModel.php
    public function getStampingCycleTimes(): array
    {
        $sql = "
        SELECT 
            stamping_hyd,
            stamping_mech,
            stamping_small,
            stamping_muffler,
            stamping_spotwelding,
            stamping_finishing
        FROM material_inventory
        LIMIT 1
    ";

        return $this->db->SelectOne($sql) ?? [];
    }
    public function getComponentStages(): array
    {
        $sql = "SELECT components_name, stage_name FROM components_inventory";
        return $this->db->Select($sql);
    }
}
