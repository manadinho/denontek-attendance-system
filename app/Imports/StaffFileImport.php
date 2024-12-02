<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;
use App\Models\FileUpload;
use App\Models\User;
use App\Models\FileUploadFail;

class StaffFileImport implements OnEachRow, SkipsOnFailure, WithChunkReading, WithEvents, WithStartRow, WithValidation
{
    use Importable, RegistersEventListeners, SkipsFailures;
    
    private $validCount = 0;
    private $failedCount = 0;
    private $fileUpload;

    public function __construct(FileUpload $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    public function onRow(Row $row)
    {
        $row = $row->toArray();

        $importData = $this->mapImportData($row);
        if (! empty($importData)) {
            $this->validCount++;
            $this->createStaff($importData);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function startRow(): int
    {
        return 2; // Starting from row 2 to skip headers
    }

    public function getValidCount(): int
    {
        return $this->validCount;
    }

    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    public function getColumns()
    {
        return [
            'name' => ['index' => 0, 'title' => 'name', 'rules' => 'required'],
            'email' => ['index' => 1, 'title' => 'email', 'rules' => 'required|email|unique:users,email'],
            'password' => ['index' => 2, 'title' => 'password', 'rules' => 'required|min:5'],
            'type' => ['index' => 3, 'title' => 'type', 'rules' => 'required|in:teacher,employee'],
        ];
    }

    public function rules(): array
    {
        return $this->getRules();
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $failed = [];

                foreach ($this->failures() as $failure) {
                    if (! isset($failed[$failure->row()])) {
                        $importData = $this->mapImportData($failure->values());
                        if (empty($importData)) {
                            continue; // Skip empty data
                        }
                        $rowNumber = $failure->row();
                        $importData['row_number'] = $rowNumber;
                        $failed[$failure->row()] = [
                            'file_upload_id' => $this->fileUpload->id,
                            'data' => $importData,
                        ];

                        $this->failedCount++;
                    }
                    foreach ($failure->errors() as $error) {
                        $failed[$failure->row()]['validation_errors'][] = $error;
                    }
                }

                foreach ($failed as $failedRecord) {
                    FileUploadFail::create($failedRecord);
                }

                // update total records, good records and cannot upload records
                $this->fileUpload->update([
                    'total_records' => $this->getValidCount() + $this->getFailedCount(),
                    'good' => $this->getValidCount(),
                    'cannot_upload' => $this->getFailedCount(),
                ]);
            },
        ];
    }

    private function getRules() {
        $rules = [];
        $columns = collect($this->getColumns())->pluck('rules', 'index');
        $columns->each(function($item, $index) use (&$rules) {
            $rules['*.'.$index] = $item;
        });
        return $rules;
    }

    public function customValidationAttributes(): array
    {
        $attributes = [];
        $columns = $this->getColumns();

        foreach ($columns as $key => $column) {
            $attributes[$key] = $column['title'];
        }

        return collect($this->getColumns())
            ->pluck('title', 'index')
            ->mapWithKeys(function ($title, $index) {
                return ['*.' . $index => $title];
            })
            ->toArray();
    }


    private function mapImportData(array $row): array
    {
        $data = [
            'name' => $row[0] ?? null,
            'email' => $row[1] ?? null,
            'password' => $row[2] ?? null,
            'type' => $row[3] ?? "",
        ];

        $filteredData = array_filter($data, function ($value) {
            return ! is_null($value) && $value !== '';
        });

        // Only return filtered data if it's not empty
        return ! empty($filteredData) ? $filteredData : [];
    }

    private function createStaff($importData)
    {
        try {
            User::firstOrCreate(
                [
                'name' => $importData['name'], 
                'school_id' => $this->fileUpload->school_id,
                'email' => $importData['email'],
                'password' => \Hash::make($importData['password']),
                'type' => $importData['type'],
                'rfid' => ' '
            ]);
        } catch (\Throwable $th) {
            
        }
    }
}
