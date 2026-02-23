<?php

namespace App\Imports;

use App\Models\InventoryItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;

class InventoryImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Skip header row and grouping rows (where Col A is an asset tag, not a dept name)
                if (!isset($row[3]) || trim($row[3]) === '' || strtolower(trim($row[3])) === 'nomenclature' || strtolower(trim($row[3])) === 'description2')
                    continue;

                $department = trim($row[0] ?? '');
                $location = trim($row[1] ?? '');

                // Skip rows where column A looks like an asset tag code (these are Excel grouping rows)
                if (str_starts_with($department, 'HQI') || empty($department))
                    continue;

                $nomenclature = trim($row[3]);
                $brand = trim($row[4] ?? 'N/A');
                $modelName = trim($row[5] ?? 'N/A');
                $color = trim($row[6] ?? 'N/A');
                $sn = trim($row[7] ?? 'N/A');
                $assetTag = trim($row[8] ?? '');

                // Robust check: if mapping is shifted in some rows, find the HQI code anywhere
                if (empty($assetTag) || !str_contains($assetTag, 'HQI')) {
                    foreach ($row as $val) {
                        $valStr = (string) $val;
                        if (str_contains($valStr, 'HQI-')) {
                            $assetTag = trim($valStr);
                            break;
                        }
                    }
                }

                if (empty($nomenclature))
                    continue;

                // Determine Type based on Nomenclature keywords
                $type = 'Others';
                $nomLower = strtolower($nomenclature);
                if (str_contains($nomLower, 'laptop'))
                    $type = 'Laptop';
                elseif (str_contains($nomLower, 'desktop'))
                    $type = 'Desktop';
                elseif (str_contains($nomLower, 'monitor'))
                    $type = 'Monitor';
                elseif (str_contains($nomLower, 'mouse'))
                    $type = 'Mouse';
                elseif (str_contains($nomLower, 'keyboard'))
                    $type = 'Keyboard';
                elseif (str_contains($nomLower, 'printer') || str_contains($nomLower, 'scanner'))
                    $type = 'Printer';
                elseif (str_contains($nomLower, 'webcam') || str_contains($nomLower, 'camera') || str_contains($nomLower, 'cctv'))
                    $type = 'Webcam';
                elseif (str_contains($nomLower, 'router') || str_contains($nomLower, 'switch') || str_contains($nomLower, 'access point'))
                    $type = 'Router';
                elseif (str_contains($nomLower, 'chair') || str_contains($nomLower, 'table') || str_contains($nomLower, 'desk') || str_contains($nomLower, 'cabinet'))
                    $type = 'Furniture';

                InventoryItem::create([
                    'department' => $department,
                    'location' => $location,
                    'name' => $nomenclature,
                    'type' => $type,
                    'quantity' => 1,
                    'serial_number' => $sn !== 'N/A' ? $sn : null,
                    'asset_tag' => $assetTag !== 'N/A' && $assetTag !== '' ? $assetTag : null,
                    'status' => 'Good',
                    'brand' => $brand !== 'N/A' ? $brand : null,
                    'model_name' => $modelName !== 'N/A' ? $modelName : null,
                    'color' => $color !== 'N/A' ? $color : null,
                ]);

            } catch (\Exception $e) {
                Log::error('Inventory row error: ' . $e->getMessage());
                continue;
            }
        }
    }
}
