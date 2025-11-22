<?php

namespace Modules\TerminalData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TdFolderResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'bidang_id' => $this->bidang_id,
            'name' => $this->name,
            'description' => $this->description,
            'path' => $this->path,
            'level' => $this->level,
            'color' => $this->color,
            'icon' => $this->icon,

            // Status flags
            'is_public' => $this->is_public,
            'is_starred' => $this->is_starred,
            'is_locked' => $this->is_locked,
            'is_system' => $this->is_system,

            // Statistics
            'total_files' => $this->total_files,
            'total_subfolders' => $this->total_subfolders,
            'total_size' => $this->total_size,
            'total_size_human' => $this->getHumanSize(),

            // Additional computed fields
            'subfolders_count' => $this->when(
                isset($this->subfolders_count),
                $this->subfolders_count
            ),

            // Settings
            'settings' => $this->settings,

            // Relationships
            'bidang' => $this->whenLoaded('bidang', function () {
                return [
                    'id' => $this->bidang->id,
                    'nama' => $this->bidang->nama,
                    'warna' => $this->bidang->warna ?? null,
                ];
            }),

            'parent' => $this->whenLoaded('parent', function () {
                return [
                    'id' => $this->parent->id,
                    'name' => $this->parent->name,
                    'path' => $this->parent->path,
                    'level' => $this->parent->level,
                ];
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
                'rename' => $request->user()?->can('rename', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
                'upload' => $request->user()?->can('upload', $this->resource) ?? false,
            ],

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
