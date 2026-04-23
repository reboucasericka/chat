<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'file' => ['nullable', 'file', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('body') && ! $this->hasFile('attachment') && ! $this->hasFile('file')) {
                $validator->errors()->add('body', 'Informe uma mensagem ou anexo.');
            }
        });
    }
}
