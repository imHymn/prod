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
        $sql = "SELECT material_no, assembly_cycletime, stamping_spotwelding, stamping_finishing FROM material_inventory";
        $rows = $this->db->Select($sql);

        $cycleTimes = [
            'materials' => [],  // keyed by material_no
            'stamping_spotwelding' => null,
            'stamping_finishing' => null
        ];

        foreach ($rows as $row) {
            $cycleTimes['materials'][$row['material_no']] = $row['assembly_cycletime'];

            // Assumes the same values for all rows, so set once
            if ($cycleTimes['stamping_spotwelding'] === null) {
                $cycleTimes['stamping_spotwelding'] = $row['stamping_spotwelding'];
            }

            if ($cycleTimes['stamping_finishing'] === null) {
                $cycleTimes['stamping_finishing'] = $row['stamping_finishing'];
            }
        }

        return $cycleTimes;
    }

    public function getAssemblyProcessTimes(): array
    {
        $sql = "SELECT material_no, assembly_processtime, stamping_spotwelding, stamping_finishing FROM material_inventory";
        $rows = $this->db->Select($sql);

        $processTimes = [
            'materials' => [],  // keyed by material_no
            'stamping_spotwelding' => null,
            'stamping_finishing' => null
        ];

        foreach ($rows as $row) {
            $processTimes['materials'][$row['material_no']] = $row['assembly_processtime'];

            // Set stamping times once (assuming all rows contain the same)
            if ($processTimes['stamping_spotwelding'] === null) {
                $processTimes['stamping_spotwelding'] = $row['stamping_spotwelding'];
            }

            if ($processTimes['stamping_finishing'] === null) {
                $processTimes['stamping_finishing'] = $row['stamping_finishing'];
            }
        }

        return $processTimes;
    }


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
