<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SensorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'serial_number' => $this->serial_number,
            'name' => $this->name,
            'model' => $this->model,
        ];
    }
}
