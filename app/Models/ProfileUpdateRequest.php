<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        
        return [
            // Nama tidak wajib diisi saat edit (nullable)
            'full_name' => ['nullable', 'string', 'max:255'],
            
            // Email tidak wajib diisi saat edit (nullable), tapi jika diisi harus valid dan unique
            'email' => [
                'nullable', 
                'string', 
                'lowercase', 
                'email', 
                'max:255', 
                Rule::unique(User::class, 'email')->ignore($user->user_id, 'user_id')
            ],
            
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048', // 2MB
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.string' => 'Nama lengkap harus berupa teks.',
            'full_name.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',
            'email.string' => 'Email harus berupa teks.',
            'email.lowercase' => 'Email harus menggunakan huruf kecil.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'profile_photo.image' => 'File harus berupa gambar.',
            'profile_photo.mimes' => 'Format foto harus JPG, JPEG, PNG, atau GIF.',
            'profile_photo.max' => 'Ukuran foto tidak boleh lebih dari 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Jika field kosong, set ke null untuk konsistensi
        if ($this->input('full_name') === '') {
            $this->merge(['full_name' => null]);
        }
        
        if ($this->input('email') === '') {
            $this->merge(['email' => null]);
        }
    }
}