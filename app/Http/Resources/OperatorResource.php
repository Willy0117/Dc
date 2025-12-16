<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OperatorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'code' => $this->code,
            'name' => $this->name,
            /*'disabled' => $this->disabled,
            'display_order' => $this->display_order,
            /* 2025.11.14 不要データ削除
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            */
        ];
    }
}

