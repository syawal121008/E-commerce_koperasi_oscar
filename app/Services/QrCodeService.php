<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
    public function generateForUser(User $user): string
    {
        try {
            Log::info('Generating QR code for user: ' . $user->user_id);
            /*
            // Try with minimal data first
            $qrData = $user->student_id; // Only student ID
            */
            // Ambil data user dalam bentuk array dari model User
$dataArray = $user->getQrCodeData();

// Ubah array tersebut menjadi sebuah string JSON
$qrData = json_encode($dataArray);
            Log::info('QR Data: ' . $qrData . ' (length: ' . strlen($qrData) . ')');
            
            // Start with smaller version and scale up if needed
            $options = new QROptions([
                'version'    => 3, // Start small
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_L, // Lowest error correction
                'scale'      => 4,
                'imageBase64' => false,
            ]);

            try {
                $qrCode = (new QRCode($options))->render($qrData);
            } catch (\Exception $e) {
                Log::warning('Failed with version 3, trying version 5');
                
                // If failed, try with higher version
                $options->version = 5;
                $qrCode = (new QRCode($options))->render($qrData);
            }
            
            // Ensure the qrcodes directory exists
            if (!Storage::disk('public')->exists('qrcodes')) {
                Storage::disk('public')->makeDirectory('qrcodes');
            }
            
            // Create unique filename
            $filename = 'qr_' . $user->student_id . '_' . time() . '.png';
            $path = 'qrcodes/' . $filename;
            
            // Save file
            $saved = Storage::disk('public')->put($path, $qrCode);
            
            if (!$saved) {
                throw new \Exception('Failed to save QR code file');
            }
            
            // Verify file was created
            if (!Storage::disk('public')->exists($path)) {
                throw new \Exception('QR code file was not created');
            }
            
            $fileSize = Storage::disk('public')->size($path);
            Log::info('QR code saved paidfully. File: ' . $path . ', Size: ' . $fileSize . ' bytes');
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
    
    public function generateSimpleQr(string $data): string
    {
        try {
            // Limit data length
            if (strlen($data) > 50) {
                $data = substr($data, 0, 50);
            }
            
            $options = new QROptions([
                'version'    => 3,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_L,
                'scale'      => 4,
                'imageBase64' => true, // Return base64 for simple QR
            ]);

            $qrCode = (new QRCode($options))->render($data);
            
            // Remove data:image/png;base64, prefix if present
            if (str_starts_with($qrCode, 'data:image/png;base64,')) {
                $qrCode = substr($qrCode, 22);
            }
            
            return $qrCode;
            
        } catch (\Exception $e) {
            Log::error('Simple QR generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Decode QR data back to user info
     */
    public function decodeQrData(string $qrData): ?array
    {
        try {
            // If it's just student_id (simple format)
            if (!str_contains($qrData, '|') && !str_contains($qrData, '{')) {
                return [
                    'student_id' => $qrData,
                    'type' => 'payment'
                ];
            }
            
            // If it's pipe-separated format: student_id|user_id_part|type
            if (str_contains($qrData, '|')) {
                $parts = explode('|', $qrData);
                return [
                    'student_id' => $parts[0] ?? null,
                    'user_id_part' => $parts[1] ?? null,
                    'type' => $parts[2] ?? 'payment'
                ];
            }
            
            // If it's JSON format (legacy)
            $decoded = json_decode($qrData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to decode QR data: ' . $e->getMessage());
            return null;
        }
    }
}