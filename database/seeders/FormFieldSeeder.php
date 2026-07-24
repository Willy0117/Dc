<?php

namespace Database\Seeders;

use App\Models\FormField;
use App\Models\FormOption;
use Illuminate\Database\Seeder;

class FormFieldSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // 手
            ['treatment_area' => '手', 'field_name' => '穿刺血管',    'field_type' => 'checkbox', 'sort_order' => 0],
            ['treatment_area' => '手', 'field_name' => '病名',         'field_type' => 'checkbox', 'sort_order' => 1],
            ['treatment_area' => '手', 'field_name' => '右手疼痛部位', 'field_type' => 'checkbox', 'sort_order' => 2],
            ['treatment_area' => '手', 'field_name' => '左手疼痛部位', 'field_type' => 'checkbox', 'sort_order' => 3],
            // 足
            ['treatment_area' => '足', 'field_name' => '穿刺血管',    'field_type' => 'checkbox', 'sort_order' => 0],
            ['treatment_area' => '足', 'field_name' => '病名',         'field_type' => 'checkbox', 'sort_order' => 1],
            ['treatment_area' => '足', 'field_name' => '駆血部位',     'field_type' => 'radio',    'sort_order' => 2],
            // 肘
            ['treatment_area' => '肘', 'field_name' => '穿刺血管',    'field_type' => 'checkbox', 'sort_order' => 0],
            ['treatment_area' => '肘', 'field_name' => '駆血部位',     'field_type' => 'radio',    'sort_order' => 1],
            ['treatment_area' => '肘', 'field_name' => '病名',         'field_type' => 'checkbox', 'sort_order' => 2],
            // 肩
            ['treatment_area' => '肩', 'field_name' => '穿刺血管',    'field_type' => 'checkbox', 'sort_order' => 0],
            ['treatment_area' => '肩', 'field_name' => '駆血部位',     'field_type' => 'radio',    'sort_order' => 1],
            ['treatment_area' => '肩', 'field_name' => '病名',         'field_type' => 'checkbox', 'sort_order' => 2],
            // 膝
            ['treatment_area' => '膝', 'field_name' => '穿刺血管',    'field_type' => 'checkbox', 'sort_order' => 0],
            ['treatment_area' => '膝', 'field_name' => '駆血部位',     'field_type' => 'radio',    'sort_order' => 1],
            ['treatment_area' => '膝', 'field_name' => '病名',         'field_type' => 'checkbox', 'sort_order' => 2],
            // 共通
            ['treatment_area' => '共通', 'field_name' => 'トラブル・合併症', 'field_type' => 'checkbox', 'sort_order' => 0],
        ];

        foreach ($fields as $fieldData) {
            $field = FormField::create($fieldData);

            // 既存のform_optionsにform_field_idを紐づける
            FormOption::where('treatment_area', $fieldData['treatment_area'])
                ->where('field_name', $fieldData['field_name'])
                ->update(['form_field_id' => $field->id]);
        }

        $this->command->info('form_fields: ' . FormField::count() . '件 作成完了');
        $this->command->info('form_options 紐づけ完了');
    }
}
