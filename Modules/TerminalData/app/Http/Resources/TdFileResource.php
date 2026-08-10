<?php

namespace Modules\TerminalData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TdFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folder_id' => $this->folder_id,
            'name' => $this->name,
            'original_name' => $this->original_name,
            'description' => $this->description,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'size_human' => $this->getHumanSize(),
            'version' => $this->version,

            // Status flags
            'is_public' => $this->is_public,
            'is_starred' => $this->is_starred,
            'is_locked' => $this->is_locked,

            // Statistics
            'views' => $this->views,
            'downloads' => $this->downloads,
            'last_viewed_at' => $this->last_viewed_at?->format('Y-m-d H:i:s'),
            'last_downloaded_at' => $this->last_downloaded_at?->format('Y-m-d H:i:s'),

            // Relationships
            'bidang' => $this->whenLoaded('bidang', function () {
                return $this->bidang ? [
                    'id' => $this->bidang->id,
                    'nama' => $this->bidang->nama,
                    'warna' => $this->bidang->warna ?? null,
                ] : null;
            }),

            'sub_bidang' => $this->whenLoaded('subBidang', function () {
                return $this->subBidang ? [
                    'id' => $this->subBidang->id,
                    'nama' => $this->subBidang->nama,
                ] : null;
            }),

            'folder' => $this->whenLoaded('folder', function () {
                return $this->folder ? [
                    'id' => $this->folder->id,
                    'name' => $this->folder->name,
                    'path' => $this->folder->path,
                ] : null;
            }),

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'nama' => $this->creator->nama,
                    'nip' => $this->creator->nip ?? null,
                ];
            }),

            'updater' => $this->whenLoaded('updater', function () {
                return $this->updater ? [
                    'id' => $this->updater->id,
                    'nama' => $this->updater->nama,
                ] : null;
            }),

            // Permissions
            'permissions' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'download' => $request->user()?->can('download', $this->resource) ?? false,
            ],

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
