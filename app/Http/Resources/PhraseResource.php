<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Concerns\TransformsContent;

class PhraseResource extends JsonResource
{
    use TransformsContent;

    public function toArray(Request $request): array
    {
        $translations = $this->translations ?? [];

        return [
            'id'           => $this->id,
            'category_id'  => $this->phrase_category_id,
            'category'     => LookupResource::make($this->whenLoaded('category')),
            'khmer_text'   => $this->khmer_text,
            'romanization' => $this->romanization,
            'meaning'      => $translations[app()->getLocale()] ?? $translations['en'] ?? null,
            'translations' => $translations ?: new \stdClass,
            'audio_url'    => $this->mediaUrl('audio'),
            'is_emergency' => (bool) $this->is_emergency,
            'updated_at'   => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}