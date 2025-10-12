<?php

namespace App\Services;

use Modules\Dokumen\Models\Template;
use Modules\Dokumen\Models\TemplateGenerated;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class TemplateService
{
    /**
     * Get available variables untuk template
     */
    public function getSystemVariables()
    {
        return [
            'user' => [
                'nama' => 'Nama User',
                'nip' => 'NIP',
                'email' => 'Email',
                'phone' => 'No. Telepon',
                'jabatan' => 'Jabatan',
                'bidang' => 'Bidang',
                'alamat' => 'Alamat',
            ],
            'dokumen' => [
                'nomor' => 'Nomor Dokumen',
                'tanggal' => 'Tanggal Dokumen',
                'judul' => 'Judul Dokumen',
            ],
            'system' => [
                'tanggal_hari_ini' => 'Tanggal Hari Ini',
                'tahun' => 'Tahun',
                'bulan' => 'Bulan',
                'hari' => 'Hari',
            ],
            'custom' => [
                'custom_field_1' => 'Custom Field 1',
                'custom_field_2' => 'Custom Field 2',
            ]
        ];
    }

    /**
     * Prepare data untuk replace variables
     */
    public function prepareVariableData($user, $additionalData = [])
    {
        $data = [
            // User data
            'user.nama' => $user->nama ?? $user->name ?? '',
            'user.nip' => $user->nip ?? '',
            'user.email' => $user->email ?? '',
            'user.phone' => $user->phone ?? '',
            'user.jabatan' => $user->jabatan->nama ?? '',
            'user.bidang' => $user->bidang->nama ?? '',
            'user.alamat' => $user->alamat ?? '',

            // System data
            'system.tanggal_hari_ini' => now()->format('d F Y'),
            'system.tahun' => now()->year,
            'system.bulan' => now()->format('F'),
            'system.hari' => now()->format('l'),
        ];

        // Merge additional data
        foreach ($additionalData as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Generate dokumen dari template
     */
    public function generateFromTemplate(Template $template, $user, $additionalData = [])
    {
        try {
            Log::info('Generating document from template', [
                'template_id' => $template->id,
                'user_id' => $user->id
            ]);

            // Prepare data
            $data = $this->prepareVariableData($user, $additionalData);

            // Replace variables in content
            $content = $this->replaceVariables($template->content, $data);

            // Replace variables in header & footer
            $header = $template->header ? $this->replaceVariables($template->header, $data) : '';
            $footer = $template->footer ? $this->replaceVariables($template->footer, $data) : '';

            // Generate based on format
            $result = match ($template->format_output) {
                'pdf' => $this->generatePDF($content, $header, $footer, $template->settings),
                'docx' => $this->generateDOCX($content, $header, $footer, $template->settings),
                default => $content,
            };

            // Save generated record
            $generated = TemplateGenerated::create([
                'template_id' => $template->id,
                'user_id' => $user->id,
                'data_variables' => $data,
                'file_path' => $result['path'] ?? null,
                'generated_at' => now(),
            ]);

            Log::info('Document generated successfully', [
                'generated_id' => $generated->id
            ]);

            return [
                'success' => true,
                'content' => $result['content'] ?? $content,
                'file_path' => $result['path'] ?? null,
                'generated_id' => $generated->id,
            ];
        } catch (\Exception $e) {
            Log::error('Error generating document from template', [
                'error' => $e->getMessage(),
                'template_id' => $template->id
            ]);

            throw $e;
        }
    }

    /**
     * Replace variables dengan data
     */
    private function replaceVariables($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Generate PDF dari HTML content
     */
    private function generatePDF($content, $header, $footer, $settings)
    {
        $html = $this->buildFullHTML($content, $header, $footer, $settings);

        $pdf = Pdf::loadHTML($html);

        // Apply settings
        if (isset($settings['orientation'])) {
            $pdf->setPaper('a4', $settings['orientation']);
        }

        $filename = 'generated_' . time() . '.pdf';
        $path = 'templates/generated/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        return [
            'path' => $path,
            'content' => $html
        ];
    }

    /**
     * Generate DOCX (Placeholder - need PHPWord library)
     */
    private function generateDOCX($content, $header, $footer, $settings)
    {
        // TODO: Implement DOCX generation using PHPWord
        // For now, return HTML
        return [
            'path' => null,
            'content' => $this->buildFullHTML($content, $header, $footer, $settings)
        ];
    }

    /**
     * Build full HTML document
     */
    private function buildFullHTML($content, $header, $footer, $settings)
    {
        $margins = $settings['margins'] ?? [
            'top' => 20,
            'right' => 20,
            'bottom' => 20,
            'left' => 20
        ];

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                @page {
                    margin: {$margins['top']}mm {$margins['right']}mm {$margins['bottom']}mm {$margins['left']}mm;
                }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 12pt;
                    line-height: 1.6;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 10px;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    border-top: 1px solid #000;
                    padding-top: 10px;
                    font-size: 10pt;
                }
                .content {
                    text-align: justify;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                table, th, td {
                    border: 1px solid black;
                    padding: 8px;
                }
            </style>
        </head>
        <body>
            {$header}
            <div class='content'>
                {$content}
            </div>
            {$footer}
        </body>
        </html>
        ";

        return $html;
    }

    /**
     * Preview template dengan sample data
     */
    public function previewTemplate(Template $template, $sampleData = [])
    {
        $user = auth()->user();
        $data = $this->prepareVariableData($user, $sampleData);

        $content = $this->replaceVariables($template->content, $data);
        $header = $template->header ? $this->replaceVariables($template->header, $data) : '';
        $footer = $template->footer ? $this->replaceVariables($template->footer, $data) : '';

        return $this->buildFullHTML($content, $header, $footer, $template->settings ?? []);
    }
}
