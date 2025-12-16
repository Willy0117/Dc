<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProcessResource;

class DeviceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            /*'code' => $this->code,*/
            'name' => $this->name,
            'process' => ProcessResource::make($this->process),
            'measurement' => $this->measurement,
        ];
    }
}
