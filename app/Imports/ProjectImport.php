<?php

namespace App\Imports;

use App\Factory\ProjectFactory;
use App\Models\FailedRow;
use App\Models\Project;
use App\Models\Task;
use App\Models\Type;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ProjectImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    private Task $task;

    /**
     * @param $task
     */
    public function __construct($task)
    {
        $this->task = $task;
    }



    /**
     * @param Collection $collection
     */
//    public function collection(Collection $collection)
//    {
//        $types = Type::all();
//        $typesMap = $this->getTypesMap($types);
//
//        foreach ($collection as $row) {
//            if (!isset($row['naimenovanie'])) continue;
//
//            Project::updateOrCreate([
//                'type_id' => $this->getTypeId($typesMap, $row['tip']),
//                'title' => $row['naimenovanie'],
//                'created_at_time' => isset($row['data_sozdaniia']) ? Date::excelToDateTimeObject($row['data_sozdaniia']) : null,
//                'contracted_at' => isset($row['podpisanie_dogovora']) ? Date::excelToDateTimeObject($row['podpisanie_dogovora']) : null,
//                'deadline' => isset($row['dedlain']) ? Date::excelToDateTimeObject($row['dedlain']) : null,
//                'is_chain' => isset($row['setevik']) ? $this->getBool($row['setevik']) : null,
//                'is_on_time' => isset($row['sdaca_v_srok']) ? $this->getBool($row['sdaca_v_srok']) : null,
//                'has_outsource' => isset($row['nalicie_autsorsinga']) ? $this->getBool($row['nalicie_autsorsinga']) : null,
//                'has_investors' => isset($row['nalicie_investorov']) ? $this->getBool($row['nalicie_investorov']) : null,
//                'worker_count' => $row['kolicestvo_ucastnikov'] ?? null,
//                'service_count' => $row['kolicestvo_uslug'] ?? null,
//                'payment_first_step' => $row['vlozenie_v_pervyi_etap'] ?? null,
//                'payment_second_step' => $row['vlozenie_vo_vtoroi_etap'] ?? null,
//                'payment_third_step' => $row['vlozenie_v_tretii_etap'] ?? null,
//                'payment_fourth_step' => $row['vlozenie_v_cetvertyi_etap'] ?? null,
//                'comment' => $row['kommentarii'] ?? null,
//                'effective_value' => $row['znacenie_effektivnosti'] ?? null,
//            ]);
//        }
//    }


    public function collection(Collection $collection)
    {
//        $types = Type::all();
        $typesMap = $this->getTypesMap(Type::all());

        foreach ($collection as $row) {
            if (!isset($row['naimenovanie'])) {
                continue;
            }

            $projectFactory = ProjectFactory::make($typesMap, $row);
            Project::updateOrCreate([
                'type_id' => $projectFactory->getValues()['type_id'],
                'title' => $projectFactory->getValues()['title'],
                'created_at_time' => $projectFactory->getValues()['created_at_time'],
                'contracted_at' => $projectFactory->getValues()['contracted_at'],
            ], $projectFactory->getValues());
        }
    }


    private function getTypesMap($types): array
    {
        $map = [];
        foreach ($types as $type) {
            $map[$type->title] = $type->id;
        }

        return $map;
    }

    private function getTypeId($map, $title)
    {
        return isset($map[$title]) ? $map[$title] : Type::create(['title' => $title])->id;
    }

    private function getBool($item): bool
    {
        return $item == 'Да';
    }

    public function rules(): array
    {
        return [
            'tip' => 'required|string',
            'naimenovanie' => 'required|string',
            'data_sozdaniia' => 'required|integer',
            'podpisanie_dogovora' => 'required|integer',
            'dedlain' => 'nullable|integer',
            'setevik' => 'nullable|string',
            'nalicie_autsorsinga' => 'nullable|string',
            'nalicie_investorov' => 'nullable|string',
            'sdaca_v_srok' => 'nullable|string',
            'vlozenie_v_pervyi_etap' => 'nullable|integer',
            'vlozenie_vo_vtoroi_etap' => 'nullable|integer',
            'vlozenie_v_tretii_etap' => 'nullable|integer',
            'vlozenie_v_cetvertyi_etap' => 'nullable|integer',
            'kolicestvo_ucastnikov' => 'nullable|integer',
            'kolicestvo_uslug' => 'nullable|integer',
            'kommentarii' => 'nullable|string',
            'znacenie_effektivnosti' => 'nullable|numeric',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $map = [];
        foreach ($failures as $failure) {

            foreach ($failure->errors() as $error) {
                $map[] = [
                    'key' => $this->attributesMap()[$failure->attribute()],
                    'row' => $failure->row(),
                    'message' => $error,
                    'task_id' => $this->task->id,

                ];
            }
        }
           if (count($map) > 0) FailedRow::insertFailedRows($map, $this->task);
    }

    private function attributesMap(): array
    {
        return [
            'tip' => 'Тип',
            'naimenovanie' => 'Наименование',
            'data_sozdaniia' => 'Дата создания',
            'podpisanie_dogovora' => 'Подписание договора',
            'dedlain' => 'Дедлайн',
            'setevik' => 'Сетевик',
            'nalicie_autsorsinga' => 'Наличие аутсорсинга',
            'nalicie_investorov' => 'Наличие инвесторов',
            'sdaca_v_srok' => 'Сдача в срок',
            'vlozenie_v_pervyi_etap' => 'Вложение в первый этап',
            'vlozenie_vo_vtoroi_etap' => 'Вложение во второй этап',
            'vlozenie_v_tretii_etap' => 'Вложение в третий этап',
            'vlozenie_v_cetvertyi_etap' => 'Вложение в четвертый этап',
            'kolicestvo_ucastnikov' => 'Количество участников',
            'kolicestvo_uslug' => 'Количество услуг',
            'kommentarii' => 'Комментарий',
            'znacenie_effektivnosti' => 'Значение эффективности',
        ];
    }






































//    public function customValidationMessages(): array
//    {
//        return [
//            'data_sozdaniia.integer' => 'Это поле должно быть числом',
//        ];
//    }
}
