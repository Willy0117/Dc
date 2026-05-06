<?php

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            // …他の項目…

            'history_certificate_path' =>
                $this->history_certificate_path
                    ? Storage::url($this->history_certificate_path)
                    : null,

            'history_certificate_thumbnail_path' =>
                $this->history_certificate_thumbnail_path
                    ? Storage::url($this->history_certificate_thumbnail_path)
                    : null,
        ];
    }
}
