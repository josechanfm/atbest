<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\Member;
use App\Models\Config;
use App\Models\Organization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
// use Maatwebsite\Excel\Concerns\WithDrawings;

class MemberImport implements ToCollection, WithStartRow, SkipsOnFailure, WithHeadingRow
{
    /**
     * @return \Illuminate\Support\Collection
     */
    use SkipsFailures, Importable;

    /**
     * @var Contest $contest
     */
    private Organization $organization;

    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function headingRow(): int
    {
        return 2;
    }

    /**
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $validator = Validator::make($row->toArray(), [
                'given_name' => 'required',
                'family_name' => 'required',
                'middle_name' => 'nullable',
                'gender' => 'required',
                'dob' => 'nullable',
                'address'=> 'nullable',
                'email'=>'required|email|unique:members',
                'mobile'=>'nullable',
            ]);
            
            $data = [...$row];

            $data['organization_id'] = $this->organization->id;

            if (is_numeric($data['dob'])) {
                $data['dob'] = Date::excelToDateTimeObject($data['dob'])->format('Y-m-d');
            }
            // 創建代表隊資料
            $this->createOrganizationMember($data);
        }
    }
    private function createOrganizationMember($row): Member
    {
        // 創建代表隊資料
        return Member::firstOrCreate(['email' => $row['email']],$row);
    }

}
    